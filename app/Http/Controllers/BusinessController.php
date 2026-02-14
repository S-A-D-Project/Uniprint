<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BusinessController extends Controller
{
    private function getUserEnterprise()
    {
        $userId = session('user_id');
        $role = null;
        try {
            $role = DB::table('roles')
                ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                ->where('roles.user_id', $userId)
                ->select('role_types.user_role_type')
                ->first();
        } catch (\Throwable $e) { $role = null; }

        $roleType = $role?->user_role_type;
        $enterprise = null;

        if ($roleType === 'admin') {
            $enterpriseId = request()->query('enterprise_id');
            if ($enterpriseId) {
                $enterprise = DB::table('enterprises')->where('enterprise_id', $enterpriseId)->first();
            }
            if (! $enterprise) {
                $serviceId = request()->route('id');
                if ($serviceId) {
                    $enterprise = DB::table('services')
                        ->join('enterprises', 'services.enterprise_id', '=', 'enterprises.enterprise_id')
                        ->where('services.service_id', $serviceId)
                        ->select('enterprises.*')
                        ->first();
                }
            }
            if (!$enterprise) abort(400, 'Enterprise context required.');
            return $enterprise;
        }

        if (Schema::hasColumn('enterprises', 'owner_user_id')) {
            $enterprise = DB::table('enterprises')->where('owner_user_id', $userId)->first();
        }
        if (!$enterprise && Schema::hasTable('staff')) {
            $enterprise = DB::table('staff')
                ->join('enterprises', 'staff.enterprise_id', '=', 'enterprises.enterprise_id')
                ->where('staff.user_id', $userId)
                ->select('enterprises.*')
                ->first();
        }
        if (!$enterprise) {
            redirect()->route('business.onboarding')->send();
            exit;
        }
        return $enterprise;
    }

    private function logAudit($action, $entityType, $entityId, $description, $oldValues = null, $newValues = null)
    {
        DB::table('audit_logs')->insert([
            'log_id' => Str::uuid(),
            'user_id' => session('user_id'),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function dashboard()
    {
        $userId = session('user_id');
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();
        $unreadNotificationsCount = DB::table('order_notifications')->where('recipient_id', $userId)->where('is_read', false)->count();
        
        $latestStatusTimes = DB::table('order_status_history')->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))->groupBy('purchase_order_id');
        $ordersWithLatestStatus = DB::table('customer_orders')
            ->where('customer_orders.enterprise_id', $enterprise->enterprise_id)
            ->leftJoinSub($latestStatusTimes, 'latest_status_times', function ($join) { $join->on('customer_orders.purchase_order_id', '=', 'latest_status_times.purchase_order_id'); })
            ->leftJoin('order_status_history as osh', function ($join) { $join->on('customer_orders.purchase_order_id', '=', 'osh.purchase_order_id')->on('osh.timestamp', '=', 'latest_status_times.latest_time'); })
            ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id');

        $stats = [
            'total_orders' => DB::table('customer_orders')->where('enterprise_id', $enterprise->enterprise_id)->count(),
            'pending_orders' => (clone $ordersWithLatestStatus)->where('statuses.status_name', 'Pending')->distinct()->count('customer_orders.purchase_order_id'),
            'in_progress_orders' => (clone $ordersWithLatestStatus)->whereIn('statuses.status_name', ['Processing', 'In Progress'])->distinct()->count('customer_orders.purchase_order_id'),
            'total_services' => DB::table('services')->where('enterprise_id', $enterprise->enterprise_id)->count(),
        ];
        $recent_orders = (clone $ordersWithLatestStatus)->join('users', 'customer_orders.customer_id', '=', 'users.user_id')->select('customer_orders.*', 'users.name as customer_name', 'statuses.status_name')->orderBy('customer_orders.created_at', 'desc')->limit(10)->get();
        return view('business.dashboard', compact('stats', 'recent_orders', 'enterprise', 'userName', 'unreadNotificationsCount'));
    }

    public function settings()
    {
        $userId = session('user_id');
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();
        $user = DB::table('users')->where('user_id', $userId)->first();
        return view('business.settings', compact('enterprise', 'userName', 'user'));
    }

    public function updateAccount(Request $request)
    {
        $userId = session('user_id');
        $request->validate(['name' => 'required|string|max:200', 'email' => 'required|email|max:255']);
        $existingUser = DB::table('users')->where('user_id', $userId)->first();
        if (!$existingUser) return redirect()->back()->with('error', 'User not found');
        DB::table('users')->where('user_id', $userId)->update(['name' => $request->name, 'email' => $request->email, 'updated_at' => now()]);
        session()->put(['user_name' => $request->name, 'user_email' => $request->email]);
        $this->logAudit('update', 'account', $userId, "Updated account: {$request->name}", (array) $existingUser, $request->only(['name', 'email']));
        return redirect()->back()->with('success', 'Account updated');
    }

    public function orders(Request $request)
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();
        $unreadNotificationsCount = DB::table('order_notifications')->where('recipient_id', session('user_id'))->where('is_read', false)->count();
        $tab = (string) $request->query('tab', 'all');
 
        $tabToStatuses = [
            'all' => [],
            'pending' => ['Pending'],
            'confirmed' => ['Confirmed'],
            'in_progress' => ['In Progress', 'Processing'],
            'ready_for_pickup' => ['Ready for Pickup'],
            'delivered' => ['Delivered', 'Shipped'],
            'completed' => ['Completed'],
            'cancelled' => ['Cancelled'],
        ];
        $statusFilter = $tabToStatuses[$tab] ?? [];

        $latestStatusTimes = DB::table('order_status_history')
            ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
            ->groupBy('purchase_order_id');

        $dueExpr = 'customer_orders.date_requested';
        if (Schema::hasColumn('customer_orders', 'pickup_date')) {
            $dueExpr = 'customer_orders.pickup_date';
        } elseif (Schema::hasColumn('customer_orders', 'requested_fulfillment_date')) {
            $dueExpr = 'customer_orders.requested_fulfillment_date';
        } elseif (Schema::hasColumn('customer_orders', 'delivery_date')) {
            $dueExpr = 'customer_orders.delivery_date';
        }
        $dueDateSql = "DATE({$dueExpr})";

        $ordersQuery = DB::table('customer_orders')
            ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
            ->leftJoinSub($latestStatusTimes, 'latest_status_times', function ($join) {
                $join->on('customer_orders.purchase_order_id', '=', 'latest_status_times.purchase_order_id');
            })
            ->leftJoin('order_status_history as osh', function ($join) {
                $join->on('customer_orders.purchase_order_id', '=', 'osh.purchase_order_id')
                    ->on('osh.timestamp', '=', 'latest_status_times.latest_time');
            })
            ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id')
            ->where('customer_orders.enterprise_id', $enterprise->enterprise_id)
            ->select('customer_orders.*', 'users.name as customer_name', 'statuses.status_name')
            ->addSelect(DB::raw("{$dueDateSql} as due_date"));

        if (Schema::hasTable('payments')) {
            $ordersQuery
                ->leftJoin('payments', 'customer_orders.purchase_order_id', '=', 'payments.purchase_order_id')
                ->addSelect(
                    'payments.amount_paid as payment_amount_paid',
                    'payments.amount_due as payment_amount_due',
                    'payments.is_verified as payment_is_verified'
                )
                ->addSelect(DB::raw("CASE WHEN payments.is_verified = true AND payments.amount_paid >= payments.amount_due THEN 1 ELSE 0 END as is_paid_calc"));
        } elseif (Schema::hasColumn('customer_orders', 'payment_status')) {
            $ordersQuery
                ->addSelect(DB::raw("CASE WHEN customer_orders.payment_status = 'paid' THEN 1 ELSE 0 END as is_paid_calc"));
        } else {
            $ordersQuery
                ->addSelect(DB::raw("0 as is_paid_calc"));
        }

        if (!empty($statusFilter)) {
            $ordersQuery->whereIn('statuses.status_name', $statusFilter);
        }

        if (Schema::hasColumn('customer_orders', 'rush_option')) {
            $ordersQuery->orderByRaw(
                "CASE customer_orders.rush_option WHEN 'same_day' THEN 0 WHEN 'rush' THEN 1 WHEN 'express' THEN 2 ELSE 3 END"
            );
        }

        $orders = $ordersQuery
            ->orderByRaw("{$dueDateSql} asc")
            ->orderBy('customer_orders.created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        return view('business.orders.index', compact('orders', 'enterprise', 'userName', 'tab', 'unreadNotificationsCount'));
    }

    public function orderDetails(Request $request, $id)
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();
        $order = DB::table('customer_orders')->join('users', 'customer_orders.customer_id', '=', 'users.user_id')->where('customer_orders.purchase_order_id', $id)->where('customer_orders.enterprise_id', $enterprise->enterprise_id)->select('customer_orders.*', 'users.name as customer_name', 'users.email as customer_email')->first();
        if (!$order) abort(404);

        $orderItems = DB::table('order_items')
            ->leftJoin('services', 'order_items.service_id', '=', 'services.service_id')
            ->where('order_items.purchase_order_id', $id)
            ->select('order_items.*', 'services.service_name')
            ->get();

        foreach ($orderItems as $item) {
            $item->customizations = collect();
            if (Schema::hasTable('order_item_customizations') && Schema::hasTable('customization_options')) {
                $item->customizations = DB::table('order_item_customizations')
                    ->join('customization_options', 'order_item_customizations.option_id', '=', 'customization_options.option_id')
                    ->where('order_item_customizations.order_item_id', $item->item_id)
                    ->select('customization_options.*', 'order_item_customizations.price_snapshot')
                    ->get();
            }
        }

        $statusHistory = collect();
        if (Schema::hasTable('order_status_history') && Schema::hasTable('statuses')) {
            $statusHistory = DB::table('order_status_history')
                ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
                ->leftJoin('users', 'order_status_history.user_id', '=', 'users.user_id')
                ->where('order_status_history.purchase_order_id', $id)
                ->select('order_status_history.*', 'statuses.status_name', 'statuses.description', 'users.name as user_name')
                ->orderBy('order_status_history.timestamp', 'desc')
                ->get();
        }

        $designFiles = collect();
        if (Schema::hasTable('order_design_files')) {
            $designFiles = DB::table('order_design_files')
                ->leftJoin('users as uploader', 'order_design_files.uploaded_by', '=', 'uploader.user_id')
                ->leftJoin('users as approver', 'order_design_files.approved_by', '=', 'approver.user_id')
                ->where('order_design_files.purchase_order_id', $id)
                ->select(
                    'order_design_files.*',
                    'uploader.name as uploaded_by_name',
                    'approver.name as approved_by_name'
                )
                ->orderBy('order_design_files.created_at', 'desc')
                ->get();
        }

        $latestExtensionRequest = null;
        if (Schema::hasTable('order_extension_requests')) {
            $latestExtensionRequest = DB::table('order_extension_requests')
                ->where('purchase_order_id', $id)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        $currentStatusName = 'Pending';
        if (Schema::hasTable('order_status_history') && Schema::hasTable('statuses')) {
            $currentStatusName = DB::table('order_status_history')
                ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
                ->where('order_status_history.purchase_order_id', $id)
                ->orderBy('order_status_history.timestamp', 'desc')
                ->value('statuses.status_name') ?? 'Pending';
        }

        $statusIds = [];
        if (Schema::hasTable('statuses')) {
            $statusIds = DB::table('statuses')
                ->select('status_id', 'status_name')
                ->get()
                ->pluck('status_id', 'status_name')
                ->toArray();
        }

        $businessActions = [];
        $nextByStatus = [
            'Pending' => 'Confirmed',
            'Confirmed' => 'In Progress',
            'Processing' => 'In Progress',
            'In Progress' => 'Ready for Pickup',
        ];
        $nextName = $nextByStatus[$currentStatusName] ?? null;
        if ($nextName && !empty($statusIds[$nextName])) {
            $label = match ($nextName) {
                'Confirmed' => 'Confirm Order',
                'In Progress' => 'Start Production',
                'Ready for Pickup' => 'Mark Ready for Pickup',
                default => $nextName,
            };
            $businessActions[] = ['id' => $statusIds[$nextName], 'name' => $nextName, 'label' => $label];
        }
        if (($currentStatusName === 'In Progress') && !empty($statusIds['Delivered'])) {
            $businessActions[] = ['id' => $statusIds['Delivered'], 'name' => 'Delivered', 'label' => 'Mark Delivered'];
        }
        if (!empty($statusIds['Cancelled']) && !in_array($currentStatusName, ['Completed', 'Cancelled'], true)) {
            $businessActions[] = ['id' => $statusIds['Cancelled'], 'name' => 'Cancelled', 'label' => 'Cancel Order'];
        }

        $payment = null;
        $isPaid = false;
        if (Schema::hasTable('payments')) {
            $payment = DB::table('payments')->where('purchase_order_id', $id)->first();
            if ($payment) {
                $amountPaid = (float) ($payment->amount_paid ?? 0);
                $amountDue = (float) ($payment->amount_due ?? 0);
                $isPaid = !empty($payment->is_verified) && ($amountPaid + 0.00001 >= $amountDue);
            }
        } elseif (Schema::hasColumn('customer_orders', 'payment_status')) {
            $isPaid = (($order->payment_status ?? null) === 'paid');
        }

        $canConfirmPayment = false;
        if (! $isPaid) {
            $canConfirmPayment = true;
        }

        if ($request->ajax()) {
            $items = $orderItems;
            return view('business.orders.details-partial', compact('order', 'orderItems', 'items', 'statusHistory', 'designFiles', 'latestExtensionRequest', 'currentStatusName', 'statusIds', 'businessActions', 'payment', 'isPaid', 'canConfirmPayment'))->render();
        }

        return view('business.orders.details', compact('order', 'orderItems', 'statusHistory', 'designFiles', 'latestExtensionRequest', 'currentStatusName', 'statusIds', 'businessActions', 'payment', 'isPaid', 'canConfirmPayment', 'enterprise', 'userName'));
    }

    public function confirmOrder(Request $request, string $id)
    {
        $enterprise = $this->getUserEnterprise();
        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $order) {
            abort(404);
        }

        $currentStatusName = null;
        if (Schema::hasTable('order_status_history') && Schema::hasTable('statuses')) {
            $currentStatusName = DB::table('order_status_history')
                ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
                ->where('order_status_history.purchase_order_id', $id)
                ->orderBy('order_status_history.timestamp', 'desc')
                ->value('statuses.status_name');
        }
        $currentStatusName = $currentStatusName ?: 'Pending';

        if ($currentStatusName !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending orders can be confirmed.');
        }

        $confirmedStatusId = DB::table('statuses')->where('status_name', 'Confirmed')->value('status_id');
        if (! $confirmedStatusId) {
            $newId = (string) Str::uuid();
            DB::table('statuses')->insertOrIgnore([
                'status_id' => $newId,
                'status_name' => 'Confirmed',
                'description' => 'Order confirmed by the business',
            ]);
            $confirmedStatusId = DB::table('statuses')->where('status_name', 'Confirmed')->value('status_id');
        }

        if (! $confirmedStatusId) {
            return redirect()->back()->with('error', 'Status "Confirmed" is not configured.');
        }

        if (Schema::hasTable('order_status_history')) {
            DB::table('order_status_history')->insert([
                'approval_id' => (string) Str::uuid(),
                'purchase_order_id' => $id,
                'user_id' => session('user_id'),
                'status_id' => $confirmedStatusId,
                'remarks' => 'Order confirmed by business',
                'timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->update([
                'status_id' => $confirmedStatusId,
                'updated_at' => now(),
            ]);

        if (Schema::hasTable('order_notifications')) {
            DB::table('order_notifications')->insert([
                'notification_id' => (string) Str::uuid(),
                'purchase_order_id' => $id,
                'recipient_id' => $order->customer_id,
                'notification_type' => 'status_change',
                'title' => 'Order Confirmed',
                'message' => "Your order #" . ($order->order_no ?? substr($id, 0, 12)) . " has been confirmed by the business.",
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Order confirmed.');
    }

    public function updateOrderStatus(Request $request, string $id)
    {
        $enterprise = $this->getUserEnterprise();
        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $order) {
            abort(404);
        }

        $request->validate([
            'status_id' => 'required|uuid',
            'remarks' => 'nullable|string|max:500',
        ]);

        $targetStatus = DB::table('statuses')->where('status_id', $request->input('status_id'))->first();
        if (! $targetStatus) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $currentStatusName = null;
        if (Schema::hasTable('order_status_history') && Schema::hasTable('statuses')) {
            $currentStatusName = DB::table('order_status_history')
                ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
                ->where('order_status_history.purchase_order_id', $id)
                ->orderBy('order_status_history.timestamp', 'desc')
                ->value('statuses.status_name');
        }
        $currentStatusName = $currentStatusName ?: 'Pending';

        $allowedNext = [
            'Pending' => ['Confirmed', 'Cancelled'],
            'Confirmed' => ['In Progress', 'Processing', 'Cancelled'],
            'Processing' => ['In Progress', 'Ready for Pickup', 'Delivered', 'Cancelled'],
            'In Progress' => ['Ready for Pickup', 'Delivered', 'Cancelled'],
            'Ready for Pickup' => ['Cancelled'],
            'Delivered' => ['Cancelled'],
        ];

        $targetName = (string) ($targetStatus->status_name ?? '');
        $allowed = $allowedNext[$currentStatusName] ?? [];
        if (! in_array($targetName, $allowed, true)) {
            return redirect()->back()->with('error', 'This status change is not allowed from the current status.');
        }

        if (Schema::hasTable('order_status_history')) {
            DB::table('order_status_history')->insert([
                'approval_id' => (string) Str::uuid(),
                'purchase_order_id' => $id,
                'user_id' => session('user_id'),
                'status_id' => $targetStatus->status_id,
                'remarks' => $request->input('remarks'),
                'timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->update([
                'status_id' => $targetStatus->status_id,
                'updated_at' => now(),
            ]);

        if (Schema::hasTable('order_notifications')) {
            DB::table('order_notifications')->insert([
                'notification_id' => (string) Str::uuid(),
                'purchase_order_id' => $id,
                'recipient_id' => $order->customer_id,
                'notification_type' => 'status_change',
                'title' => 'Order Status Updated',
                'message' => "Your order #" . ($order->order_no ?? substr($id, 0, 12)) . " status is now: {$targetName}.",
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Order status updated.');
    }

    public function confirmPayment(Request $request, string $id)
    {
        $enterprise = $this->getUserEnterprise();
        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $order) {
            abort(404);
        }

        $isPaid = false;
        $payment = null;

        if (Schema::hasTable('payments')) {
            $payment = DB::table('payments')->where('purchase_order_id', $id)->first();
            if ($payment) {
                $amountPaid = (float) ($payment->amount_paid ?? 0);
                $amountDue = (float) ($payment->amount_due ?? 0);
                $isPaid = !empty($payment->is_verified) && ($amountPaid + 0.00001 >= $amountDue);
            }
        } elseif (Schema::hasColumn('customer_orders', 'payment_status')) {
            $isPaid = (($order->payment_status ?? null) === 'paid');
        }

        if ($isPaid) {
            return redirect()->back()->with('success', 'Payment is already confirmed.');
        }

        if (Schema::hasTable('payments')) {
            if (! $payment) {
                DB::table('payments')->insert([
                    'payment_id' => (string) Str::uuid(),
                    'purchase_order_id' => $id,
                    'payment_method' => (string) ($order->payment_method ?? 'cash'),
                    'amount_paid' => (float) ($order->total ?? 0),
                    'amount_due' => (float) ($order->total ?? 0),
                    'payment_date_time' => now(),
                    'is_verified' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $amountDue = (float) ($payment->amount_due ?? 0);
                if ($amountDue <= 0) {
                    $amountDue = (float) ($order->total ?? 0);
                }

                DB::table('payments')
                    ->where('purchase_order_id', $id)
                    ->update([
                        'is_verified' => true,
                        'amount_due' => $amountDue,
                        'amount_paid' => (float) ($payment->amount_paid ?? 0) ?: $amountDue,
                        'updated_at' => now(),
                    ]);
            }
        } elseif (Schema::hasColumn('customer_orders', 'payment_status')) {
            DB::table('customer_orders')
                ->where('purchase_order_id', $id)
                ->where('enterprise_id', $enterprise->enterprise_id)
                ->update([
                    'payment_status' => 'paid',
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('order_notifications')) {
            DB::table('order_notifications')->insert([
                'notification_id' => (string) Str::uuid(),
                'purchase_order_id' => $id,
                'recipient_id' => $order->customer_id,
                'notification_type' => 'payment',
                'title' => 'Payment Confirmed',
                'message' => "Payment for order #" . ($order->order_no ?? substr($id, 0, 12)) . " has been confirmed by the business.",
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Payment confirmed.');
    }

    public function services()
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();
 
        $orderCounts = DB::table('order_items')
            ->select('service_id', DB::raw('COUNT(*) as order_count'))
            ->groupBy('service_id');

        $customizationCounts = DB::table('customization_options')
            ->select('service_id', DB::raw('COUNT(*) as customization_count'))
            ->groupBy('service_id');

        $services = DB::table('services')
            ->leftJoinSub($orderCounts, 'order_counts', function ($join) {
                $join->on('services.service_id', '=', 'order_counts.service_id');
            })
            ->leftJoinSub($customizationCounts, 'customization_counts', function ($join) {
                $join->on('services.service_id', '=', 'customization_counts.service_id');
            })
            ->where('services.enterprise_id', $enterprise->enterprise_id)
            ->select(
                'services.*',
                DB::raw('COALESCE(order_counts.order_count, 0) as order_count'),
                DB::raw('COALESCE(customization_counts.customization_count, 0) as customization_count')
            )
            ->orderBy('services.created_at', 'desc')
            ->paginate(12)
            ->withQueryString();
        return view('business.services.index', compact('services', 'enterprise', 'userName'));
    }

    public function createService()
    {
        $enterprise = $this->getUserEnterprise();
        return view('business.services.form', ['enterprise' => $enterprise, 'userName' => session('user_name')]);
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'fulfillment_type' => 'required|string|in:pickup,delivery,both',
            'allowed_payment_methods' => 'nullable|array',
            'allowed_payment_methods.*' => 'string|in:gcash,cash,paypal',
            'supports_rush' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $enterprise = $this->getUserEnterprise();

        $serviceId = (string) Str::uuid();
        $now = now();

        $insert = [
            'service_id' => $serviceId,
            'enterprise_id' => $enterprise->enterprise_id,
            'service_name' => $request->input('service_name'),
            'description' => $request->input('description'),
            'base_price' => $request->input('base_price'),
            'is_active' => $request->boolean('is_active', true),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('services', 'fulfillment_type')) {
            $insert['fulfillment_type'] = $request->input('fulfillment_type');
        }
        if (Schema::hasColumn('services', 'allowed_payment_methods')) {
            $insert['allowed_payment_methods'] = json_encode($request->input('allowed_payment_methods', []));
        }
        if (Schema::hasColumn('services', 'supports_rush')) {
            $insert['supports_rush'] = $request->boolean('supports_rush');
        }

        DB::table('services')->insert($insert);

        // Optional: store service images if supported
        if ($request->hasFile('images') && Schema::hasTable('service_images')) {
            $disk = config('filesystems.default', 'public');
            $images = (array) $request->file('images');
            $isFirst = true;

            foreach ($images as $img) {
                if (! $img) {
                    continue;
                }

                $fileName = time() . '_' . $img->getClientOriginalName();
                $path = $img->storeAs('service_images/' . $serviceId, $fileName, $disk);

                DB::table('service_images')->insert([
                    'image_id' => (string) Str::uuid(),
                    'service_id' => $serviceId,
                    'image_path' => $path,
                    'is_primary' => $isFirst,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $isFirst = false;
            }
        }

        return redirect()->route('business.services.index')->with('success', 'Service created');
    }

    public function updateEnterprise(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'shop_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'nullable|boolean',
            'checkout_payment_methods' => 'nullable|array',
            'checkout_payment_methods.*' => 'string|in:cash,gcash,paypal',
            'checkout_fulfillment_methods' => 'nullable|array',
            'checkout_fulfillment_methods.*' => 'string|in:pickup,delivery',
            'rush_options' => 'nullable|array',
            'gcash_enabled' => 'nullable|boolean',
            'gcash_instructions' => 'nullable|string',
        ]);

        $enterprise = $this->getUserEnterprise();
        
        $data = $request->only([
            'name', 'email', 'address', 'category', 'contact_person', 'contact_number'
        ]);

        if ($request->hasFile('shop_logo')) {
            $path = $request->file('shop_logo')->store('shop_logos', 'public');
            $data['shop_logo'] = $path;
        }

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        // Checkout settings
        $data['checkout_payment_methods'] = json_encode($request->input('checkout_payment_methods', []));
        $data['checkout_fulfillment_methods'] = json_encode($request->input('checkout_fulfillment_methods', []));
        
        $rushOptions = $request->input('rush_options', []);
        // Force standard to be enabled
        if (!isset($rushOptions['standard'])) {
            $rushOptions['standard'] = ['enabled' => true, 'fee' => 0, 'lead_hours' => 48];
        } else {
            $rushOptions['standard']['enabled'] = true;
        }
        
        // Clean up values in rush options
        foreach ($rushOptions as $k => $v) {
            $rushOptions[$k]['enabled'] = isset($v['enabled']) && $v['enabled'];
            $rushOptions[$k]['fee'] = (float) ($v['fee'] ?? 0);
            $rushOptions[$k]['lead_hours'] = (int) ($v['lead_hours'] ?? 0);
        }
        $data['checkout_rush_options'] = json_encode($rushOptions);

        if (Schema::hasColumn('enterprises', 'gcash_enabled')) {
            $data['gcash_enabled'] = $request->boolean('gcash_enabled');
        }
        if (Schema::hasColumn('enterprises', 'gcash_instructions')) {
            $data['gcash_instructions'] = $request->input('gcash_instructions');
        }

        DB::table('enterprises')->where('enterprise_id', $enterprise->enterprise_id)->update($data);

        return redirect()->back()->with('success', 'Shop settings updated successfully.');
    }

    public function editService($id)
    {
        $enterprise = $this->getUserEnterprise();
        $service = DB::table('services')->where('service_id', $id)->where('enterprise_id', $enterprise->enterprise_id)->first();
        if (!$service) abort(404);
        $serviceImages = DB::table('service_images')->where('service_id', $id)->orderBy('is_primary', 'desc')->get();
        return view('business.services.form', compact('service', 'serviceImages', 'enterprise'));
    }

    public function updateService(Request $request, $id)
    {
        $request->validate([
            'service_name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'fulfillment_type' => 'required|string|in:pickup,delivery,both',
            'allowed_payment_methods' => 'nullable|array',
            'allowed_payment_methods.*' => 'string|in:gcash,cash,paypal',
            'supports_rush' => 'nullable|boolean',
        ]);

        $enterprise = $this->getUserEnterprise();
        $service = \App\Models\Service::where('service_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->firstOrFail();

        $service->service_name = $request->service_name;
        $service->description = $request->description;
        $service->base_price = $request->base_price;
        $service->is_active = $request->has('is_active');
        $service->fulfillment_type = $request->fulfillment_type;
        if (Schema::hasColumn('services', 'allowed_payment_methods')) {
            $service->allowed_payment_methods = $request->input('allowed_payment_methods', []);
        }
        $service->supports_rush = $request->has('supports_rush');
        $service->save();

        return redirect()->route('business.services.index')->with('success', 'Service updated');
    }

    public function pricingRules()
    {
        $enterprise = $this->getUserEnterprise();
        $rules = DB::table('pricing_rules')->leftJoin('services', 'pricing_rules.service_id', '=', 'services.service_id')->where('pricing_rules.enterprise_id', $enterprise->enterprise_id)->select('pricing_rules.*', 'services.service_name')->orderBy('pricing_rules.priority')->paginate(20);
        return view('business.pricing.index', compact('rules', 'enterprise'));
    }

    public function createPricingRule(Request $request)
    {
        $enterprise = $this->getUserEnterprise();
        $services = DB::table('services')->where('enterprise_id', $enterprise->enterprise_id)->get();
        if ($request->ajax()) return view('business.pricing.form', compact('enterprise', 'services'))->render();
        return redirect()->route('business.pricing.index');
    }

    public function storePricingRule(Request $request)
    {
        $request->validate(['rule_name' => 'required|string|max:200', 'rule_type' => 'required|string|max:50', 'calculation_method' => 'required|in:percentage,fixed_amount,formula', 'priority' => 'required|integer|min:0']);
        $enterprise = $this->getUserEnterprise();
        $ruleId = (string) Str::uuid();
        $insert = [
            'rule_id' => $ruleId, 'enterprise_id' => $enterprise->enterprise_id, 'rule_name' => $request->rule_name, 'rule_type' => $request->rule_type,
            'rule_description' => $request->rule_description, 'service_id' => $request->apply_scope === 'service' ? $request->service_id : null,
            'calculation_method' => $request->calculation_method, 'value' => $request->calculation_method === 'formula' ? 0 : $request->value,
            'formula' => $request->formula, 'priority' => $request->priority, 'conditions' => $request->conditions ?? '[]',
            'is_active' => $request->has('is_active'), 'created_at' => now(), 'updated_at' => now(),
        ];
        DB::table('pricing_rules')->insert($insert);
        if ($request->ajax()) return response()->json(['success' => true]);
        return redirect()->route('business.pricing.index')->with('success', 'Rule created');
    }

    public function editPricingRule(Request $request, $id)
    {
        $enterprise = $this->getUserEnterprise();
        $rule = DB::table('pricing_rules')->where('rule_id', $id)->first();
        $services = DB::table('services')->where('enterprise_id', $enterprise->enterprise_id)->get();
        if ($request->ajax()) return view('business.pricing.form', compact('rule', 'enterprise', 'services'))->render();
        return redirect()->route('business.pricing.index');
    }

    public function updatePricingRule(Request $request, $id)
    {
        $request->validate(['rule_name' => 'required|string|max:200', 'rule_type' => 'required|string|max:50', 'calculation_method' => 'required|in:percentage,fixed_amount,formula', 'priority' => 'required|integer|min:0']);
        $update = [
            'rule_name' => $request->rule_name, 'rule_type' => $request->rule_type, 'rule_description' => $request->rule_description,
            'service_id' => $request->apply_scope === 'service' ? $request->service_id : null, 'calculation_method' => $request->calculation_method,
            'value' => $request->calculation_method === 'formula' ? 0 : $request->value, 'formula' => $request->formula,
            'priority' => $request->priority, 'conditions' => $request->conditions ?? '[]', 'is_active' => $request->has('is_active'), 'updated_at' => now(),
        ];
        DB::table('pricing_rules')->where('rule_id', $id)->update($update);
        if ($request->ajax()) return response()->json(['success' => true]);
        return redirect()->route('business.pricing.index')->with('success', 'Rule updated');
    }

    public function deletePricingRule($id)
    {
        DB::table('pricing_rules')->where('rule_id', $id)->delete();
        return redirect()->route('business.pricing.index')->with('success', 'Rule deleted');
    }

    public function createWalkInOrder(Request $request)
    {
        $enterprise = $this->getUserEnterprise();
        $services = DB::table('services')->where('enterprise_id', $enterprise->enterprise_id)->where('is_active', true)->get();
        if ($request->ajax()) return view('business.orders.walk-in-form', compact('enterprise', 'services'))->render();
        return view('business.orders.walk-in-create', compact('enterprise', 'services'));
    }

    public function storeWalkInOrder(Request $request)
    {
        $request->validate(['contact_name' => 'required|string|max:255', 'service_id' => 'required|uuid', 'quantity' => 'required|integer|min:1']);
        $enterprise = $this->getUserEnterprise();
        $orderId = (string) Str::uuid();
        DB::transaction(function () use ($enterprise, $orderId, $request) {
            $customerId = (string) Str::uuid();
            DB::table('users')->insert(['user_id' => $customerId, 'name' => $request->contact_name, 'email' => 'walkin-' . $orderId . '@uniprint.local', 'created_at' => now()]);
            DB::table('customer_orders')->insert(['purchase_order_id' => $orderId, 'customer_id' => $customerId, 'enterprise_id' => $enterprise->enterprise_id, 'order_no' => 'WK-' . strtoupper(Str::random(8)), 'date_requested' => now(), 'total' => 0, 'created_at' => now()]);
        });
        return redirect()->route('business.orders.index')->with('success', 'Walk-in order created');
    }

    public function updateServiceUploadSettings(Request $request, $id)
    {
        $enterprise = $this->getUserEnterprise();
        $request->validate(['file_upload_enabled' => 'nullable|boolean', 'requires_file_upload' => 'nullable|boolean']);
        DB::table('services')->where('service_id', $id)->update(['file_upload_enabled' => $request->has('file_upload_enabled'), 'requires_file_upload' => $request->has('requires_file_upload'), 'updated_at' => now()]);
        return redirect()->back()->with('success', 'Upload settings updated');
    }

    public function toggleServiceStatus($id)
    {
        $enterprise = $this->getUserEnterprise();
        $service = DB::table('services')->where('service_id', $id)->first();
        DB::table('services')->where('service_id', $id)->update(['is_active' => !$service->is_active, 'updated_at' => now()]);
        return redirect()->back()->with('success', 'Status toggled');
    }

    public function customizations($serviceId)
    {
        $enterprise = $this->getUserEnterprise();
        $service = DB::table('services')->where('service_id', $serviceId)->first();
        $customizations = DB::table('customization_options')->where('service_id', $serviceId)->get();
        $customFields = DB::table('service_custom_fields')->where('service_id', $serviceId)->get();
        return view('business.customizations.index', compact('service', 'customizations', 'customFields', 'enterprise'));
    }

    public function storeCustomization(Request $request, string $serviceId)
    {
        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $service) {
            abort(404);
        }

        if (! Schema::hasTable('customization_options')) {
            return redirect()->back()->with('error', 'Customization options are not available. Please run migrations.');
        }

        $request->validate([
            'option_name' => 'required|string|max:100',
            'option_type' => 'required|string|max:50',
            'price_modifier' => 'nullable|numeric',
            'is_default' => 'nullable|boolean',
        ]);

        $insert = [
            'option_id' => (string) Str::uuid(),
            'service_id' => $serviceId,
            'option_name' => $request->string('option_name')->toString(),
            'option_type' => $request->string('option_type')->toString(),
            'price_modifier' => (float) $request->input('price_modifier', 0),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('customization_options', 'is_default')) {
            $insert['is_default'] = $request->boolean('is_default');
        }

        DB::table('customization_options')->insert($insert);

        return redirect()->back()->with('success', 'Customization option added.');
    }

    public function updateCustomization(Request $request, string $serviceId, string $optionId)
    {
        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $service) {
            abort(404);
        }

        if (! Schema::hasTable('customization_options')) {
            return redirect()->back()->with('error', 'Customization options are not available. Please run migrations.');
        }

        $request->validate([
            'option_name' => 'required|string|max:100',
            'option_type' => 'required|string|max:50',
            'price_modifier' => 'nullable|numeric',
            'is_default' => 'nullable|boolean',
        ]);

        $update = [
            'option_name' => $request->string('option_name')->toString(),
            'option_type' => $request->string('option_type')->toString(),
            'price_modifier' => (float) $request->input('price_modifier', 0),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('customization_options', 'is_default')) {
            $update['is_default'] = $request->boolean('is_default');
        }

        $updated = DB::table('customization_options')
            ->where('option_id', $optionId)
            ->where('service_id', $serviceId)
            ->update($update);

        if (! $updated) {
            return redirect()->back()->with('error', 'Unable to update customization option.');
        }

        return redirect()->back()->with('success', 'Customization option updated.');
    }

    public function deleteCustomization(string $serviceId, string $optionId)
    {
        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $service) {
            abort(404);
        }

        if (! Schema::hasTable('customization_options')) {
            return redirect()->back()->with('error', 'Customization options are not available. Please run migrations.');
        }

        $deleted = DB::table('customization_options')
            ->where('option_id', $optionId)
            ->where('service_id', $serviceId)
            ->delete();

        if (! $deleted) {
            return redirect()->back()->with('error', 'Unable to delete customization option.');
        }

        return redirect()->back()->with('success', 'Customization option deleted.');
    }

    public function updateCustomSizeSettings(Request $request, string $serviceId)
    {
        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $service) {
            abort(404);
        }

        $request->validate([
            'supports_custom_size' => 'nullable|boolean',
            'custom_size_unit' => 'nullable|string|max:10',
            'custom_size_min_width' => 'nullable|numeric|min:0',
            'custom_size_max_width' => 'nullable|numeric|min:0',
            'custom_size_min_height' => 'nullable|numeric|min:0',
            'custom_size_max_height' => 'nullable|numeric|min:0',
        ]);

        $update = ['updated_at' => now()];

        if (Schema::hasColumn('services', 'supports_custom_size')) {
            $update['supports_custom_size'] = $request->boolean('supports_custom_size');
        }
        if (Schema::hasColumn('services', 'custom_size_unit')) {
            $update['custom_size_unit'] = $request->filled('custom_size_unit') ? strtolower($request->string('custom_size_unit')->toString()) : null;
        }
        if (Schema::hasColumn('services', 'custom_size_min_width')) {
            $update['custom_size_min_width'] = $request->filled('custom_size_min_width') ? (float) $request->input('custom_size_min_width') : null;
        }
        if (Schema::hasColumn('services', 'custom_size_max_width')) {
            $update['custom_size_max_width'] = $request->filled('custom_size_max_width') ? (float) $request->input('custom_size_max_width') : null;
        }
        if (Schema::hasColumn('services', 'custom_size_min_height')) {
            $update['custom_size_min_height'] = $request->filled('custom_size_min_height') ? (float) $request->input('custom_size_min_height') : null;
        }
        if (Schema::hasColumn('services', 'custom_size_max_height')) {
            $update['custom_size_max_height'] = $request->filled('custom_size_max_height') ? (float) $request->input('custom_size_max_height') : null;
        }

        DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->update($update);

        return redirect()->back()->with('success', 'Custom size settings updated.');
    }

    public function storeCustomField(Request $request, string $serviceId)
    {
        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $service) {
            abort(404);
        }

        if (! Schema::hasTable('service_custom_fields')) {
            return redirect()->back()->with('error', 'Custom fields are not available. Please run migrations.');
        }

        $request->validate([
            'field_label' => 'required|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'is_required' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:1000',
        ]);

        DB::table('service_custom_fields')->insert([
            'field_id' => (string) Str::uuid(),
            'service_id' => $serviceId,
            'field_label' => $request->string('field_label')->toString(),
            'placeholder' => $request->filled('placeholder') ? $request->string('placeholder')->toString() : null,
            'is_required' => $request->boolean('is_required'),
            'sort_order' => $request->filled('sort_order') ? (int) $request->input('sort_order') : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Custom field added.');
    }

    public function updateCustomField(Request $request, string $serviceId, string $fieldId)
    {
        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $service) {
            abort(404);
        }

        if (! Schema::hasTable('service_custom_fields')) {
            return redirect()->back()->with('error', 'Custom fields are not available. Please run migrations.');
        }

        $request->validate([
            'field_label' => 'required|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'is_required' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:1000',
        ]);

        $updated = DB::table('service_custom_fields')
            ->where('field_id', $fieldId)
            ->where('service_id', $serviceId)
            ->update([
                'field_label' => $request->string('field_label')->toString(),
                'placeholder' => $request->filled('placeholder') ? $request->string('placeholder')->toString() : null,
                'is_required' => $request->boolean('is_required'),
                'sort_order' => $request->filled('sort_order') ? (int) $request->input('sort_order') : 0,
                'updated_at' => now(),
            ]);

        if (! $updated) {
            return redirect()->back()->with('error', 'Unable to update custom field.');
        }

        return redirect()->back()->with('success', 'Custom field updated.');
    }

    public function deleteCustomField(string $serviceId, string $fieldId)
    {
        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $service) {
            abort(404);
        }

        if (! Schema::hasTable('service_custom_fields')) {
            return redirect()->back()->with('error', 'Custom fields are not available. Please run migrations.');
        }

        $deleted = DB::table('service_custom_fields')
            ->where('field_id', $fieldId)
            ->where('service_id', $serviceId)
            ->delete();

        if (! $deleted) {
            return redirect()->back()->with('error', 'Unable to delete custom field.');
        }

        return redirect()->back()->with('success', 'Custom field deleted.');
    }

    public function chat()
    {
        $enterprise = $this->getUserEnterprise();
        return view('business.chat', compact('enterprise'));
    }
}
