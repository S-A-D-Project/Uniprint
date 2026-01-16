<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\DesignAsset;
use App\Models\Enterprise;
use App\Models\Service;
use App\Models\CustomerOrder;
use App\Models\OrderItem;
use App\Models\OrderItemCustomization;
use App\Models\OrderStatusHistory;

class CustomerController extends Controller
{
    public function enterprises()
    {
        $userName = session('user_name');

        $enterprises = Enterprise::query()
            ->where('is_active', true)
            ->withCount(['services' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name')
            ->paginate(12);

        return view('customer.enterprises', compact('enterprises', 'userName'));
    }

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

    public function orders()
    {
        $userId = session('user_id');
        $userName = session('user_name');

        $latestStatusTimes = DB::table('order_status_history')
            ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
            ->groupBy('purchase_order_id');

        $orders = DB::table('customer_orders')
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
            ->orderBy('customer_orders.created_at', 'desc')
            ->paginate(10);

        return view('customer.orders', compact('orders', 'userName'));
    }

    public function orderDetails($id)
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

        // Get order items with service info
        $itemsQuery = DB::table('order_items')
            ->join('services', 'order_items.service_id', '=', 'services.service_id')
            ->where('order_items.purchase_order_id', $id);

        $hasRequiresFileUploadColumn = \Illuminate\Support\Facades\Schema::hasColumn('services', 'requires_file_upload');

        $orderItems = $hasRequiresFileUploadColumn
            ? $itemsQuery->select('order_items.*', 'services.service_name', 'services.requires_file_upload')->get()
            : $itemsQuery->select('order_items.*', 'services.service_name')->get();

        $requiresFileUpload = $hasRequiresFileUploadColumn
            ? $orderItems->contains(fn($item) => !empty($item->requires_file_upload))
            : false;

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

        return view('customer.order-details', compact('order', 'orderItems', 'statusHistory', 'transaction', 'designFiles', 'userName', 'currentStatusName', 'requiresFileUpload'));
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
            return redirect()->back()->with('error', 'You can only confirm orders that are ready or delivered.');
        }

        $completedStatusId = DB::table('statuses')->where('status_name', 'Completed')->value('status_id');
        if (! $completedStatusId) {
            return redirect()->back()->with('error', 'Status "Completed" is not configured.');
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
        if (\Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'owner_user_id')) {
            $recipientId = DB::table('enterprises')->where('enterprise_id', $order->enterprise_id)->value('owner_user_id');
        }
        if (! $recipientId && \Illuminate\Support\Facades\Schema::hasTable('staff')) {
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
        if (\Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'owner_user_id')) {
            $recipientId = DB::table('enterprises')->where('enterprise_id', $order->enterprise_id)->value('owner_user_id');
        }
        if (! $recipientId && \Illuminate\Support\Facades\Schema::hasTable('staff')) {
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
        $filePath = $file->storeAs('design_files/' . $orderId, $fileName, 'public');

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
        \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);

        // Delete from database
        DB::table('order_design_files')->where('file_id', $fileId)->delete();

        return redirect()->back()->with('success', 'Design file deleted successfully');
    }

    public function notifications()
    {
        $userId = session('user_id');
        $userName = session('user_name');

        $notifications = DB::table('order_notifications')
            ->where('recipient_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('customer.notifications', compact('notifications', 'userName'));
    }

    public function markNotificationRead($id)
    {
        $userId = session('user_id');

        DB::table('order_notifications')
            ->where('notification_id', $id)
            ->where('recipient_id', $userId)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Notification marked as read');
    }

    public function enterpriseServices($id)
    {
        $enterprise = Enterprise::where('enterprise_id', $id)
            ->where('is_active', true)
            ->firstOrFail();

        $services = Service::where('enterprise_id', $id)
            ->where('is_active', true)
            ->with('customizationOptions')
            ->paginate(12);

        return view('customer.services', compact('enterprise', 'services'));
    }

    public function serviceDetails($id)
    {
        $service = Service::where('service_id', $id)
            ->where('is_active', true)
            ->with(['enterprise', 'customizationOptions', 'customFields'])
            ->firstOrFail();

        $customizationGroups = $service->customizationOptions->groupBy('option_type');

        return view('customer.service-details', compact('service', 'customizationGroups'));
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

        $assets = DesignAsset::where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('customer.design-assets', compact('assets'));
    }
}
