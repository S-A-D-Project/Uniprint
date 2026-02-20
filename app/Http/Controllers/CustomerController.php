<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Models\DesignAsset;
use App\Models\Enterprise;
use App\Models\Service;
use App\Models\CustomerOrder;
use App\Models\OrderItem;
use App\Models\OrderItemCustomization;
use App\Models\OrderStatusHistory;

class CustomerController extends Controller
{
    public function dashboard()
    {
        $userId = session('user_id');
        $userName = session('user_name');

        // Calculate total design assets for this user (saved AI designs + uploaded order design files)
        $userDesignsCount = DB::table('user_designs')->where('user_id', $userId)->count();
        $uploadedDesignFilesCount = DB::table('order_design_files')->where('uploaded_by', $userId)->count();
        $totalAssets = $userDesignsCount + $uploadedDesignFilesCount;

        $stats = [
            'total_orders' => DB::table('customer_orders')->where('customer_id', $userId)->count(),
            'pending_orders' => DB::table('customer_orders')
                ->join('order_status_history', 'customer_orders.purchase_order_id', '=', 'order_status_history.purchase_order_id')
                ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
                ->where('customer_orders.customer_id', $userId)
                ->where('statuses.status_name', 'Pending')
                ->distinct()
                ->count('customer_orders.purchase_order_id'),
            'completed_orders' => DB::table('customer_orders')
                ->join('order_status_history', 'customer_orders.purchase_order_id', '=', 'order_status_history.purchase_order_id')
                ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
                ->where('customer_orders.customer_id', $userId)
                ->where('statuses.status_name', 'Delivered')
                ->distinct()
                ->count('customer_orders.purchase_order_id'),
            'total_assets' => $totalAssets,
        ];

        $recent_orders = DB::table('customer_orders')
            ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
            ->where('customer_orders.customer_id', $userId)
            ->select('customer_orders.*', 'enterprises.name as enterprise_name')
            ->orderBy('customer_orders.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('customer.dashboard', compact('stats', 'recent_orders', 'userName'));
    }

    public function respondOrderExtension(Request $request, $orderId, $requestId)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'decision' => 'required|in:accept,decline',
        ]);

        if (!schema_has_table('order_extension_requests')) {
            return redirect()->back()->with('error', 'Extension requests are not available. Please run migrations.');
        }

        $ext = DB::table('order_extension_requests')
            ->where('request_id', $requestId)
            ->where('purchase_order_id', $orderId)
            ->where('customer_id', $userId)
            ->first();

        if (!$ext) {
            abort(404);
        }

        if (($ext->status ?? null) !== 'pending') {
            return redirect()->back()->with('error', 'This extension request is no longer pending.');
        }

        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $orderId)
            ->where('customer_id', $userId)
            ->first();

        if (!$order) {
            abort(404);
        }

        $decision = (string) $request->decision;

        // Determine business recipient (owner preferred; fallback to staff)
        $businessRecipientId = null;
        if (schema_has_column('enterprises', 'owner_user_id')) {
            $businessRecipientId = DB::table('enterprises')->where('enterprise_id', $order->enterprise_id)->value('owner_user_id');
        }
        if (!$businessRecipientId && schema_has_table('staff')) {
            $businessRecipientId = DB::table('staff')
                ->where('enterprise_id', $order->enterprise_id)
                ->orderByRaw("CASE WHEN position = 'Owner' THEN 0 ELSE 1 END")
                ->value('user_id');
        }

        DB::transaction(function () use ($decision, $orderId, $requestId, $userId, $order, $ext) {
            DB::table('order_extension_requests')
                ->where('request_id', $requestId)
                ->update([
                    'status' => $decision === 'accept' ? 'accepted' : 'declined',
                    'responded_by' => $userId,
                    'responded_at' => now(),
                    'updated_at' => now(),
                ]);

            if ($decision === 'accept') {
                $dueExpr = 'date_requested';
                if (schema_has_column('customer_orders', 'pickup_date')) {
                    $dueExpr = 'pickup_date';
                } elseif (schema_has_column('customer_orders', 'requested_fulfillment_date')) {
                    $dueExpr = 'requested_fulfillment_date';
                } elseif (schema_has_column('customer_orders', 'delivery_date')) {
                    $dueExpr = 'delivery_date';
                }

                $newDue = null;
                if (!empty($ext->proposed_due_date)) {
                    $newDue = $ext->proposed_due_date;
                }

                if ($newDue) {
                    DB::table('customer_orders')
                        ->where('purchase_order_id', $orderId)
                        ->where('customer_id', $userId)
                        ->update([
                            $dueExpr => $newDue,
                            'updated_at' => now(),
                        ]);
                }
            }

            // Notification insertion is handled after transaction to allow correct recipient resolution.
        });

        if ($businessRecipientId && schema_has_table('order_notifications')) {
            DB::table('order_notifications')->insert([
                'notification_id' => (string) Str::uuid(),
                'purchase_order_id' => $orderId,
                'recipient_id' => $businessRecipientId,
                'notification_type' => 'extension_response',
                'title' => 'Extension Request Response',
                'message' => "Customer {$decision}ed the extension request for order #{$order->order_no}.",
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', $decision === 'accept' ? 'Extension accepted.' : 'Extension declined.');
    }

    public function orders(Request $request)
    {
        $userId = session('user_id');
        $userName = session('user_name');

        $tab = (string) $request->query('tab', 'all');
        $search = trim((string) $request->query('q', ''));
        $tabToStatuses = [
            'all' => [],
            'to_confirm' => ['Pending', 'Confirmed'],
            'processing' => ['Processing', 'In Progress'],
            'final_process' => ['Ready for Pickup', 'Delivered'],
            'completed' => ['Completed'],
            'cancelled' => ['Cancelled'],
        ];

        if (!array_key_exists($tab, $tabToStatuses)) {
            $tab = 'all';
        }

        $latestStatusTimes = DB::table('order_status_history')
            ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
            ->groupBy('purchase_order_id');

        $ordersQuery = DB::table('customer_orders')
            ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
            ->leftJoinSub($latestStatusTimes, 'latest_status_times', function ($join) {
                $join->on('customer_orders.purchase_order_id', '=', 'latest_status_times.purchase_order_id');
            })
            ->leftJoin('order_status_history as osh', function ($join) {
                $join->on('customer_orders.purchase_order_id', '=', 'osh.purchase_order_id')
                    ->on('osh.timestamp', '=', 'latest_status_times.latest_time');
            })
            ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id')
            ->where('customer_orders.customer_id', $userId)
            ->select('customer_orders.*', 'enterprises.name as enterprise_name', 'statuses.status_name')
            ->orderBy('customer_orders.created_at', 'desc');

        $statusFilter = $tabToStatuses[$tab] ?? [];
        if (!empty($statusFilter)) {
            $ordersQuery->whereIn('statuses.status_name', $statusFilter);
        }

        if ($search !== '') {
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('customer_orders.purchase_order_id', 'like', '%' . $search . '%')
                    ->orWhere('customer_orders.purpose', 'like', '%' . $search . '%')
                    ->orWhere('enterprises.name', 'like', '%' . $search . '%');
            });
        }

        $orders = $ordersQuery->paginate(10)->withQueryString();

        $orderIds = collect($orders->items())
            ->pluck('purchase_order_id')
            ->filter()
            ->values();

        $orderItemsByOrder = collect();
        if ($orderIds->isNotEmpty()) {
            $orderItemsByOrder = DB::table('order_items')
                ->join('services', 'order_items.service_id', '=', 'services.service_id')
                ->whereIn('order_items.purchase_order_id', $orderIds)
                ->select(
                    'order_items.purchase_order_id',
                    'order_items.item_id',
                    'order_items.quantity',
                    'order_items.total_cost',
                    'order_items.unit_price',
                    'services.service_name'
                )
                ->orderBy('order_items.created_at', 'asc')
                ->get()
                ->groupBy('purchase_order_id');
        }

        return view('customer.orders', compact('orders', 'userName', 'tab', 'search', 'orderItemsByOrder'));
    }

    public function orderDetails(Request $request, $id)
    {
        $userId = session('user_id');
        $userName = session('user_name');

        $order = DB::table('customer_orders')
            ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
            ->where('customer_orders.purchase_order_id', $id)
            ->where('customer_orders.customer_id', $userId)
            ->select('customer_orders.*', 'enterprises.name as enterprise_name', 'enterprises.contact_number', 'enterprises.address')
            ->first();

        if (!$order) {
            abort(404);
        }

        $extensionRequests = collect();
        $pendingExtensionRequest = null;
        if (schema_has_table('order_extension_requests')) {
            $extensionRequests = DB::table('order_extension_requests')
                ->where('purchase_order_id', $id)
                ->where('customer_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            $pendingExtensionRequest = $extensionRequests->firstWhere('status', 'pending');
        }

        // Get order items with service info
        $itemsQuery = DB::table('order_items')
            ->join('services', 'order_items.service_id', '=', 'services.service_id')
            ->where('order_items.purchase_order_id', $id);

        $hasRequiresFileUploadColumn = \Illuminate\Support\Facades\schema_has_column('services', 'requires_file_upload');
        $hasFileUploadEnabledColumn = \Illuminate\Support\Facades\schema_has_column('services', 'file_upload_enabled');

        if ($hasRequiresFileUploadColumn && $hasFileUploadEnabledColumn) {
            $orderItems = $itemsQuery->select('order_items.*', 'services.service_name', 'services.description as service_description', 'services.requires_file_upload', 'services.file_upload_enabled')->get();
        } elseif ($hasRequiresFileUploadColumn) {
            $orderItems = $itemsQuery->select('order_items.*', 'services.service_name', 'services.description as service_description', 'services.requires_file_upload')->get();
        } else {
            $orderItems = $itemsQuery->select('order_items.*', 'services.service_name', 'services.description as service_description')->get();
        }

        $requiresFileUpload = $hasRequiresFileUploadColumn
            ? $orderItems->contains(fn($item) => !empty($item->requires_file_upload))
            : false;

        $fileUploadEnabled = $hasFileUploadEnabledColumn
            ? $orderItems->contains(fn($item) => !empty($item->file_upload_enabled) || !empty($item->requires_file_upload))
            : $requiresFileUpload;

        // Get customizations for each item
        foreach ($orderItems as $item) {
            $rawFields = $item->custom_fields ?? null;
            if (is_string($rawFields)) {
                $rawFields = json_decode($rawFields, true);
            } elseif (is_object($rawFields)) {
                $rawFields = (array) $rawFields;
            }
            $rawFields = is_array($rawFields) ? $rawFields : [];

            $fieldDefs = DB::table('service_custom_fields')
                ->where('service_id', $item->service_id)
                ->orderBy('sort_order')
                ->orderBy('field_label')
                ->get();

            $item->custom_field_values = collect($fieldDefs)
                ->filter(fn($f) => isset($rawFields[$f->field_id]) && trim((string) $rawFields[$f->field_id]) !== '')
                ->map(fn($f) => (object) [
                    'label' => $f->field_label,
                    'value' => $rawFields[$f->field_id],
                ])
                ->values();

            $item->customizations = DB::table('order_item_customizations')
                ->join('customization_options', 'order_item_customizations.option_id', '=', 'customization_options.option_id')
                ->where('order_item_customizations.order_item_id', $item->item_id)
                ->select('customization_options.*', 'order_item_customizations.price_snapshot')
                ->get();
        }

        // Get status history
        $statusHistory = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->leftJoin('users', 'order_status_history.user_id', '=', 'users.user_id')
            ->where('order_status_history.purchase_order_id', $id)
            ->select('order_status_history.*', 'statuses.status_name', 'statuses.description', 'users.name as user_name')
            ->orderBy('order_status_history.timestamp', 'desc')
            ->get();

        $currentStatusName = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('order_status_history.purchase_order_id', $id)
            ->orderBy('order_status_history.timestamp', 'desc')
            ->value('statuses.status_name') ?? 'Pending';

        // Get transaction if exists
        $transaction = DB::table('transactions')
            ->where('purchase_order_id', $id)
            ->first();

        // Get design files
        $designFiles = DB::table('order_design_files')
            ->where('purchase_order_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $reviewsByServiceId = collect();
        if (schema_has_table('reviews')) {
            $serviceIds = $orderItems->pluck('service_id')->filter()->unique()->values()->all();
            if (!empty($serviceIds)) {
                $reviewsByServiceId = DB::table('reviews')
                    ->where('customer_id', $userId)
                    ->whereIn('service_id', $serviceIds)
                    ->get()
                    ->keyBy('service_id');
            }
        }

        $view = view('customer.order-details', compact('order', 'orderItems', 'statusHistory', 'transaction', 'designFiles', 'userName', 'currentStatusName', 'requiresFileUpload', 'fileUploadEnabled', 'reviewsByServiceId', 'extensionRequests', 'pendingExtensionRequest'));

        if ($request->ajax()) {
            $sections = $view->renderSections();
            return $sections['content'] ?? $view->render();
        }

        return $view;
    }

    public function storeReview(Request $request, $id)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        if (!schema_has_table('reviews')) {
            return redirect()->back()->with('error', 'Reviews are not available. Please run migrations and try again.');
        }

        $request->validate([
            'service_id' => 'required|uuid',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'review_files' => 'nullable|array',
            'review_files.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:51200',
        ]);

        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('customer_id', $userId)
            ->first();

        if (!$order) {
            abort(404);
        }

        $currentStatusName = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('order_status_history.purchase_order_id', $id)
            ->orderBy('order_status_history.timestamp', 'desc')
            ->value('statuses.status_name') ?? 'Pending';

        if ($currentStatusName !== 'Completed') {
            return redirect()->back()->with('error', 'You can only review an order after it is completed.');
        }

        $serviceId = (string) $request->input('service_id');
        $belongsToOrder = DB::table('order_items')
            ->where('purchase_order_id', $id)
            ->where('service_id', $serviceId)
            ->exists();

        if (!$belongsToOrder) {
            return redirect()->back()->with('error', 'You can only review services that are part of this order.');
        }

        $existing = DB::table('reviews')
            ->where('customer_id', $userId)
            ->where('service_id', $serviceId)
            ->first();

        $payload = [
            'rating' => (int) $request->input('rating'),
            'comment' => $request->input('comment'),
            'updated_at' => now(),
        ];

        $reviewId = null;

        if ($existing) {
            DB::table('reviews')
                ->where('review_id', $existing->review_id)
                ->update($payload);

            $reviewId = (string) $existing->review_id;

        } else {
            $reviewId = (string) Str::uuid();
            $payload['review_id'] = $reviewId;
            $payload['service_id'] = $serviceId;
            $payload['customer_id'] = $userId;
            $payload['created_at'] = now();

            DB::table('reviews')->insert($payload);
        }

        if ($reviewId && $request->hasFile('review_files') && schema_has_table('review_files')) {
            foreach ((array) $request->file('review_files') as $file) {
                if (!$file) {
                    continue;
                }

                $fileName = time() . '_' . $file->getClientOriginalName();
                $disk = config('filesystems.default', 'public');
                $filePath = $file->storeAs('review_files/' . $reviewId, $fileName, $disk);

                DB::table('review_files')->insert([
                    'file_id' => (string) Str::uuid(),
                    'review_id' => $reviewId,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->back()->with('success', $existing ? 'Review updated. Thank you!' : 'Review submitted. Thank you!');
    }

    public function orderReviewsFragment(Request $request, $id)
    {
        $userId = session('user_id');
        if (!$userId) {
            abort(401);
        }

        $order = DB::table('customer_orders')
            ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
            ->where('customer_orders.purchase_order_id', $id)
            ->where('customer_orders.customer_id', $userId)
            ->select('customer_orders.*', 'enterprises.name as enterprise_name')
            ->first();

        if (! $order) {
            abort(404);
        }

        $currentStatusName = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('order_status_history.purchase_order_id', $id)
            ->orderBy('order_status_history.timestamp', 'desc')
            ->value('statuses.status_name') ?? 'Pending';

        $orderItems = DB::table('order_items')
            ->join('services', 'order_items.service_id', '=', 'services.service_id')
            ->where('order_items.purchase_order_id', $id)
            ->select('order_items.service_id', 'services.service_name')
            ->orderBy('order_items.created_at', 'asc')
            ->get();

        $reviewsByServiceId = collect();
        if (schema_has_table('reviews')) {
            $serviceIds = $orderItems->pluck('service_id')->filter()->unique()->values()->all();
            if (!empty($serviceIds)) {
                $reviewsByServiceId = DB::table('reviews')
                    ->where('customer_id', $userId)
                    ->whereIn('service_id', $serviceIds)
                    ->get()
                    ->keyBy('service_id');
            }
        }

        $reviewFilesByReviewId = [];
        if (schema_has_table('review_files') && $reviewsByServiceId->isNotEmpty()) {
            $reviewIds = $reviewsByServiceId->pluck('review_id')->filter()->unique()->values()->all();
            if (!empty($reviewIds)) {
                $files = DB::table('review_files')
                    ->whereIn('review_id', $reviewIds)
                    ->orderBy('created_at', 'asc')
                    ->get();

                $reviewFilesByReviewId = $files
                    ->groupBy('review_id')
                    ->map(fn ($rows) => $rows->values())
                    ->toArray();
            }
        }

        $view = view('customer.order-reviews-fragment', compact('order', 'orderItems', 'reviewsByServiceId', 'currentStatusName', 'reviewFilesByReviewId'));

        if ($request->ajax()) {
            return $view->render();
        }

        return $view;
    }

    public function confirmCompletion($id)
    {
        $userId = session('user_id');

        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('customer_id', $userId)
            ->first();

        if (! $order) {
            abort(404);
        }

        $currentStatusName = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('order_status_history.purchase_order_id', $id)
            ->orderBy('order_status_history.timestamp', 'desc')
            ->value('statuses.status_name') ?? 'Pending';

        if (! in_array($currentStatusName, ['Ready for Pickup', 'Delivered'], true)) {
            return redirect()->back()->with('error', 'You can only confirm orders that are ready for pickup or marked as delivered.');
        }

        $completedStatusId = DB::table('statuses')->where('status_name', 'Completed')->value('status_id');
        if (! $completedStatusId) {
            $newCompletedId = (string) \Illuminate\Support\Str::uuid();
            DB::table('statuses')->insertOrIgnore([
                'status_id' => $newCompletedId,
                'status_name' => 'Completed',
                'description' => 'Order has been received/confirmed by the customer',
            ]);

            $completedStatusId = DB::table('statuses')->where('status_name', 'Completed')->value('status_id');
            if (! $completedStatusId) {
                return redirect()->back()->with('error', 'Status "Completed" is not configured.');
            }
        }

        DB::table('order_status_history')->insert([
            'approval_id' => \Illuminate\Support\Str::uuid(),
            'purchase_order_id' => $id,
            'user_id' => $userId,
            'status_id' => $completedStatusId,
            'remarks' => 'Order confirmed complete by customer',
            'timestamp' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('customer_id', $userId)
            ->update([
                'status_id' => $completedStatusId,
                'updated_at' => now(),
            ]);

        // Notify enterprise owner (owner_user_id preferred, fallback to first staff user)
        $recipientId = null;
        if (\Illuminate\Support\Facades\schema_has_column('enterprises', 'owner_user_id')) {
            $recipientId = DB::table('enterprises')->where('enterprise_id', $order->enterprise_id)->value('owner_user_id');
        }
        if (! $recipientId && \Illuminate\Support\Facades\schema_has_table('staff')) {
            $recipientId = DB::table('staff')
                ->where('enterprise_id', $order->enterprise_id)
                ->orderByRaw("CASE WHEN position = 'Owner' THEN 0 ELSE 1 END")
                ->value('user_id');
        }

        DB::table('order_notifications')->insert([
            'notification_id' => \Illuminate\Support\Str::uuid(),
            'purchase_order_id' => $id,
            'recipient_id' => $recipientId ?: $order->customer_id,
            'notification_type' => 'status_change',
            'title' => 'Order Completed',
            'message' => "Customer confirmed completion for order #{$order->order_no}.",
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Thanks! Order marked as completed.');
    }

    public function cancelOrder($id)
    {
        $userId = session('user_id');

        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('customer_id', $userId)
            ->first();

        if (! $order) {
            abort(404);
        }

        $currentStatusName = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('order_status_history.purchase_order_id', $id)
            ->orderBy('order_status_history.timestamp', 'desc')
            ->value('statuses.status_name') ?? 'Pending';

        if ($currentStatusName !== 'Pending') {
            return redirect()->back()->with('error', 'You can only cancel orders that are still pending.');
        }

        $cancelledStatusId = DB::table('statuses')->where('status_name', 'Cancelled')->value('status_id');
        if (! $cancelledStatusId) {
            return redirect()->back()->with('error', 'Status "Cancelled" is not configured.');
        }

        DB::table('order_status_history')->insert([
            'approval_id' => \Illuminate\Support\Str::uuid(),
            'purchase_order_id' => $id,
            'user_id' => $userId,
            'status_id' => $cancelledStatusId,
            'remarks' => 'Order cancelled by customer',
            'timestamp' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('customer_id', $userId)
            ->update([
                'status_id' => $cancelledStatusId,
                'updated_at' => now(),
            ]);

        // Notify enterprise owner (owner_user_id preferred, fallback to first staff user)
        $recipientId = null;
        if (\Illuminate\Support\Facades\schema_has_column('enterprises', 'owner_user_id')) {
            $recipientId = DB::table('enterprises')->where('enterprise_id', $order->enterprise_id)->value('owner_user_id');
        }
        if (! $recipientId && \Illuminate\Support\Facades\schema_has_table('staff')) {
            $recipientId = DB::table('staff')
                ->where('enterprise_id', $order->enterprise_id)
                ->orderByRaw("CASE WHEN position = 'Owner' THEN 0 ELSE 1 END")
                ->value('user_id');
        }

        if ($recipientId) {
            $shortId = substr((string) ($order->order_no ?? $id), 0, 16);
            DB::table('order_notifications')->insert([
                'notification_id' => \Illuminate\Support\Str::uuid(),
                'purchase_order_id' => $id,
                'recipient_id' => $recipientId,
                'notification_type' => 'status_change',
                'title' => 'Order Cancelled',
                'message' => "Customer cancelled order #{$shortId}.",
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Order cancelled successfully.');
    }

    public function uploadDesignFile(Request $request, $orderId)
    {
        $request->validate([
            'design_file' => 'required|file|mimes:jpg,jpeg,png,pdf,ai,psd,eps|max:51200', // 50MB max
            'design_notes' => 'nullable|string|max:2000',
        ]);

        $userId = session('user_id');

        // Verify order belongs to customer
        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $orderId)
            ->where('customer_id', $userId)
            ->first();

        if (!$order) {
            abort(404);
        }

        $hasFileUploadEnabledColumn = \Illuminate\Support\Facades\schema_has_column('services', 'file_upload_enabled');
        $hasRequiresFileUploadColumn = \Illuminate\Support\Facades\schema_has_column('services', 'requires_file_upload');

        if ($hasFileUploadEnabledColumn || $hasRequiresFileUploadColumn) {
            $query = DB::table('order_items')
                ->join('services', 'order_items.service_id', '=', 'services.service_id')
                ->where('order_items.purchase_order_id', $orderId);

            $enabledForOrder = false;

            if ($hasFileUploadEnabledColumn) {
                $enabledForOrder = $query
                    ->where(function ($q) {
                        $q->where('services.file_upload_enabled', true)
                          ->orWhere('services.requires_file_upload', true);
                    })
                    ->exists();
            } elseif ($hasRequiresFileUploadColumn) {
                $enabledForOrder = $query
                    ->where('services.requires_file_upload', true)
                    ->exists();
            }

            if (!$enabledForOrder) {
                return redirect()->back()->with('error', 'File uploads are not enabled for this order.');
            }
        }

        $currentStatusName = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('order_status_history.purchase_order_id', $orderId)
            ->orderBy('order_status_history.timestamp', 'desc')
            ->value('statuses.status_name') ?? 'Pending';

        if (in_array($currentStatusName, ['Delivered', 'Cancelled', 'Completed'], true)) {
            return redirect()->back()->with('error', 'You cannot upload design files for this order at its current status.');
        }

        // Handle file upload
        $file = $request->file('design_file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $disk = config('filesystems.default', 'public');
        $filePath = $file->storeAs('design_files/' . $orderId, $fileName, $disk);

        // Get file version (count existing files + 1)
        $version = DB::table('order_design_files')
            ->where('purchase_order_id', $orderId)
            ->count() + 1;

        // Store file info in database
        $fileId = \Illuminate\Support\Str::uuid();
        DB::table('order_design_files')->insert([
            'file_id' => $fileId,
            'purchase_order_id' => $orderId,
            'uploaded_by' => $userId,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'design_notes' => $request->design_notes,
            'version' => $version,
            'is_approved' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notify business
        $businessUserId = DB::table('staff')
            ->where('enterprise_id', $order->enterprise_id)
            ->whereNotNull('user_id')
            ->value('user_id');

        DB::table('order_notifications')->insert([
            'notification_id' => \Illuminate\Support\Str::uuid(),
            'purchase_order_id' => $orderId,
            'recipient_id' => $businessUserId ?: $userId,
            'notification_type' => 'file_upload',
            'title' => 'New Design File Uploaded',
            'message' => "Customer uploaded a new design file for order #{$order->order_no}",
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Design file uploaded successfully! Version ' . $version);
    }

    public function deleteDesignFile($orderId, $fileId)
    {
        $userId = session('user_id');

        // Verify order belongs to customer
        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $orderId)
            ->where('customer_id', $userId)
            ->first();

        if (!$order) {
            abort(404);
        }

        // Get file info
        $file = DB::table('order_design_files')
            ->where('file_id', $fileId)
            ->where('purchase_order_id', $orderId)
            ->where('uploaded_by', $userId)
            ->first();

        if (!$file) {
            abort(404);
        }

        // Don't allow deletion if already approved
        if ($file->is_approved) {
            return redirect()->back()->with('error', 'Cannot delete an approved file');
        }

        // Delete file from storage
        $disk = config('filesystems.default', 'public');
        try {
            if (Storage::disk($disk)->exists($file->file_path)) {
                Storage::disk($disk)->delete($file->file_path);
            }
        } catch (\Throwable $e) {
            try {
                if (Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                }
            } catch (\Throwable $e2) {
            }
        }

        // Delete from database
        DB::table('order_design_files')->where('file_id', $fileId)->delete();

        return redirect()->back()->with('success', 'Design file deleted successfully');
    }

    public function notifications()
    {
        $userId = session('user_id');
        $userName = session('user_name');

        $query = DB::table('order_notifications')
            ->where('recipient_id', $userId)
            ->orderBy('created_at', 'desc');

        if (request()->expectsJson() || request()->ajax()) {
            $notifications = $query->limit(20)->get();
            $unreadCount = DB::table('order_notifications')
                ->where('recipient_id', $userId)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);
        }

        return redirect()->route('customer.dashboard');
    }

    public function markNotificationRead(Request $request, $id)
    {
        $userId = session('user_id');

        $updated = DB::table('order_notifications')
            ->where('notification_id', $id)
            ->where('recipient_id', $userId)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => (bool) $updated,
            ]);
        }

        return redirect()->back()->with('success', 'Notification marked as read');
    }

    public function enterpriseServices($id)
    {
        $enterpriseQuery = Enterprise::where('enterprise_id', $id)
            ->where('is_active', true);
        if (schema_has_column('enterprises', 'is_verified')) {
            $enterpriseQuery->where('is_verified', true);
        }
        $enterprise = $enterpriseQuery->firstOrFail();

        $services = Service::where('enterprise_id', $id)
            ->where('is_active', true)
            ->with('customizationOptions')
            ->paginate(12);

        return view('customer.services', compact('enterprise', 'services'));
    }

    public function serviceDetails($id)
    {
        $serviceQuery = Service::where('service_id', $id)
            ->where('is_active', true)
            ->with(['enterprise', 'customizationOptions', 'customFields']);

        if (schema_has_column('enterprises', 'is_verified')) {
            $serviceQuery->whereHas('enterprise', function ($q) {
                $q->where('is_active', true)->where('is_verified', true);
            });
        } else {
            $serviceQuery->whereHas('enterprise', function ($q) {
                $q->where('is_active', true);
            });
        }

        $service = $serviceQuery->firstOrFail();

        $customizationGroups = $service->customizationOptions->groupBy('option_type');

        $reviews = collect();
        if (schema_has_table('reviews')) {
            $reviews = DB::table('reviews')
                ->leftJoin('users', 'reviews.customer_id', '=', 'users.user_id')
                ->where('reviews.service_id', $id)
                ->orderBy('reviews.created_at', 'desc')
                ->select('reviews.*', 'users.name as customer_name')
                ->limit(20)
                ->get();
        }

        return view('customer.service-details', compact('service', 'customizationGroups', 'reviews'));
    }


    public function placeOrder(Request $request)
    {
        $request->validate([
            'service_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1|max:100',
            'customizations' => 'nullable|array',
            'customizations.*' => 'uuid',
            'custom_fields' => 'nullable|array',
            'custom_fields.*' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $saved = \App\Models\SavedService::saveService(
            $userId,
            $request->service_id,
            $request->quantity,
            $request->customizations ?? [],
            $request->custom_fields ?? [],
            $request->notes
        );

        return redirect()->route('checkout.index');
    }


    public function designAssets()
    {
        $user = Auth::user();

        if (! schema_has_table('design_assets')) {
            $assets = new LengthAwarePaginator(
                [],
                0,
                12,
                LengthAwarePaginator::resolveCurrentPage(),
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return view('customer.design-assets', compact('assets'));
        }

        $assets = DesignAsset::where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('customer.design-assets', compact('assets'));
    }
}
