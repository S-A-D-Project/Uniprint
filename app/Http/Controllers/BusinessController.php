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
    /**
     * Get the enterprise for the current business user
     */
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
        } catch (\Throwable $e) {
            $role = null;
        }

        $roleType = $role?->user_role_type;

        $enterprise = null;

        if ($roleType === 'admin') {
            $enterpriseId = request()->query('enterprise_id');
            if ($enterpriseId) {
                $enterprise = DB::table('enterprises')->where('enterprise_id', $enterpriseId)->select('enterprises.*')->first();
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

            if (! $enterprise) {
                abort(400, 'Enterprise context is required.');
            }

            return $enterprise;
        }

        if (Schema::hasColumn('enterprises', 'owner_user_id')) {
            $enterprise = DB::table('enterprises')
                ->where('owner_user_id', $userId)
                ->select('enterprises.*')
                ->first();
        }

        if (! $enterprise && Schema::hasTable('staff')) {
            // Legacy fallback: enterprise linked via staff table
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

    /**
     * Resolve next allowed status buttons for business actions
     */
    private function getBusinessStatusActions(?string $currentStatusName, $statusIds, string $fulfillmentMethod = 'pickup')
    {
        $current = $currentStatusName ?? 'Pending';

        // Normalize legacy/alternate statuses
        if ($current === 'Processing') {
            $current = 'In Progress';
        }
        if ($current === 'Shipped') {
            $current = 'Delivered';
        }

        $fulfillmentMethod = in_array($fulfillmentMethod, ['pickup', 'delivery'], true)
            ? $fulfillmentMethod
            : 'pickup';

        $allowed = [
            'Pending' => ['Confirmed', 'Cancelled'],
            'Confirmed' => ['In Progress', 'Cancelled'],
            'In Progress' => $fulfillmentMethod === 'delivery' ? ['Delivered'] : ['Ready for Pickup'],
            'Ready for Pickup' => ['Delivered'],
            'Delivered' => [],
            'Completed' => [],
            'Cancelled' => [],
        ];

        $next = $allowed[$current] ?? [];

        // Map to id list
        $actions = [];
        foreach ($next as $statusName) {
            if (isset($statusIds[$statusName])) {
                $label = $statusName;
                if ($current === 'Pending' && $statusName === 'Confirmed') {
                    $label = 'Confirm Order';
                }
                if ($current === 'Ready for Pickup' && $statusName === 'Delivered') {
                    $label = 'Confirm Pickup';
                }
                if ($current === 'In Progress' && $statusName === 'Delivered') {
                    $label = 'Mark Delivered';
                }
                $actions[] = [
                    'name' => $statusName,
                    'id' => $statusIds[$statusName],
                    'label' => $label,
                ];
            }
        }

        return $actions;
    }

    /**
     * Log audit trail
     */
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

        $unreadNotificationsCount = DB::table('order_notifications')
            ->where('recipient_id', $userId)
            ->where('is_read', false)
            ->count();

        $latestStatusTimes = DB::table('order_status_history')
            ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
            ->groupBy('purchase_order_id');

        $ordersWithLatestStatus = DB::table('customer_orders')
            ->where('customer_orders.enterprise_id', $enterprise->enterprise_id)
            ->leftJoinSub($latestStatusTimes, 'latest_status_times', function ($join) {
                $join->on('customer_orders.purchase_order_id', '=', 'latest_status_times.purchase_order_id');
            })
            ->leftJoin('order_status_history as osh', function ($join) {
                $join->on('customer_orders.purchase_order_id', '=', 'osh.purchase_order_id')
                    ->on('osh.timestamp', '=', 'latest_status_times.latest_time');
            })
            ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id');

        $stats = [
            'total_orders' => DB::table('customer_orders')
                ->where('enterprise_id', $enterprise->enterprise_id)->count(),
            'pending_orders' => (clone $ordersWithLatestStatus)
                ->where('statuses.status_name', 'Pending')
                ->distinct()
                ->count('customer_orders.purchase_order_id'),
            'in_progress_orders' => (clone $ordersWithLatestStatus)
                ->whereIn('statuses.status_name', ['Processing', 'In Progress'])
                ->distinct()
                ->count('customer_orders.purchase_order_id'),
            'total_services' => DB::table('services')
                ->where('enterprise_id', $enterprise->enterprise_id)->count(),
            'total_revenue' => DB::table('transactions')
                ->join('customer_orders', 'transactions.purchase_order_id', '=', 'customer_orders.purchase_order_id')
                ->where('customer_orders.enterprise_id', $enterprise->enterprise_id)
                ->sum('transactions.amount'),
            'active_customizations' => DB::table('customization_options')
                ->join('services', 'customization_options.service_id', '=', 'services.service_id')
                ->where('services.enterprise_id', $enterprise->enterprise_id)
                ->count(),
            'pricing_rules' => DB::table('pricing_rules')
                ->where('enterprise_id', $enterprise->enterprise_id)
                ->where('is_active', true)
                ->count(),
        ];

        $recent_orders = (clone $ordersWithLatestStatus)
            ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
            ->select('customer_orders.*', 'users.name as customer_name', 'statuses.status_name')
            ->orderBy('customer_orders.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('business.dashboard', compact('stats', 'recent_orders', 'enterprise', 'userName', 'unreadNotificationsCount'));
    }

    // =====================================================
    // SETTINGS
    // =====================================================

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

        $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|email|max:255',
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
        ]);

        $existingUser = DB::table('users')->where('user_id', $userId)->first();
        if (!$existingUser) {
            return redirect()->back()->with('error', 'User not found');
        }

        $emailTaken = DB::table('users')
            ->where('email', $request->email)
            ->where('user_id', '!=', $userId)
            ->exists();

        if ($emailTaken) {
            return redirect()->back()->with('error', 'Email is already taken by another user');
        }

        DB::table('users')
            ->where('user_id', $userId)
            ->update([
                'name' => $request->name,
                'email' => $request->email,
                'position' => $request->position,
                'department' => $request->department,
                'updated_at' => now(),
            ]);

        session()->put([
            'user_name' => $request->name,
            'user_email' => $request->email,
        ]);

        $this->logAudit('update', 'account', $userId, "Updated account profile: {$request->name}", (array) $existingUser, $request->only(['name', 'email', 'position', 'department']));

        return redirect()->back()->with('success', 'Account updated successfully');
    }

    public function updateEnterprise(Request $request)
    {
        $enterprise = $this->getUserEnterprise();

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'shop_logo' => 'nullable|image|max:2048',
        ]);

        $oldEnterprise = DB::table('enterprises')->where('enterprise_id', $enterprise->enterprise_id)->first();
        if (!$oldEnterprise) {
            return redirect()->back()->with('error', 'Enterprise not found');
        }

        $update = [
            'name' => $request->name,
            'address' => $request->address,
            'contact_person' => $request->contact_person,
            'contact_number' => $request->contact_number,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('enterprises', 'email')) {
            $update['email'] = $request->email;
        }
        if (Schema::hasColumn('enterprises', 'category')) {
            $update['category'] = $request->category;
        }
        if (Schema::hasColumn('enterprises', 'is_active')) {
            $update['is_active'] = $request->has('is_active') ? (bool) $request->is_active : (bool) ($oldEnterprise->is_active ?? true);
        }

        if ($request->hasFile('shop_logo') && Schema::hasColumn('enterprises', 'shop_logo')) {
            $path = $request->file('shop_logo')->store('shop-logos', 'public');
            $update['shop_logo'] = $path;
        }

        DB::table('enterprises')
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->update($update);

        $this->logAudit('update', 'enterprise', $enterprise->enterprise_id, "Updated enterprise settings: {$request->name}", (array) $oldEnterprise, $update);

        return redirect()->back()->with('success', 'Print shop info updated successfully');
    }

    // =====================================================
    // ORDER MANAGEMENT
    // =====================================================
    
    public function orders(Request $request)
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $unreadNotificationsCount = DB::table('order_notifications')
            ->where('recipient_id', session('user_id'))
            ->where('is_read', false)
            ->count();

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
        $today = Carbon::today()->toDateString();
        $dueSoon = Carbon::today()->addDay()->toDateString();

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

        if (! empty($statusFilter)) {
            $ordersQuery->whereIn('statuses.status_name', $statusFilter);
        }

        if (Schema::hasColumn('customer_orders', 'rush_option')) {
            $ordersQuery->orderByRaw(
                "CASE customer_orders.rush_option WHEN 'same_day' THEN 0 WHEN 'rush' THEN 1 WHEN 'express' THEN 2 ELSE 3 END"
            );
        }

        $orders = $ordersQuery
            ->orderByRaw(
                "CASE WHEN {$dueExpr} IS NULL THEN 3 WHEN {$dueDateSql} < ? THEN 0 WHEN {$dueDateSql} <= ? THEN 1 ELSE 2 END",
                [$today, $dueSoon]
            )
            ->orderByRaw("{$dueDateSql} asc")
            ->orderBy('customer_orders.created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        return view('business.orders.index', compact('orders', 'enterprise', 'userName', 'tab', 'unreadNotificationsCount'));
    }

    public function createWalkInOrder()
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $unreadNotificationsCount = DB::table('order_notifications')
            ->where('recipient_id', session('user_id'))
            ->where('is_read', false)
            ->count();

        $services = DB::table('services')
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->where('is_active', true)
            ->orderBy('service_name')
            ->get();

        return view('business.orders.walk-in-create', compact('enterprise', 'userName', 'services', 'unreadNotificationsCount'));
    }

    public function storeWalkInOrder(Request $request)
    {
        $enterprise = $this->getUserEnterprise();
        $businessUserId = session('user_id');

        $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            'purpose' => 'required|string|max:255',
            'fulfillment_method' => 'required|in:pickup,delivery',
            'requested_fulfillment_date' => 'nullable|date',
            'rush_option' => 'nullable|string|max:50',
            'service_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $service = DB::table('services')
            ->where('service_id', $request->service_id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $service) {
            return redirect()->back()->with('error', 'Selected service not found.');
        }

        $pendingStatusId = DB::table('statuses')->where('status_name', 'Pending')->value('status_id');
        if (! $pendingStatusId) {
            $newId = (string) Str::uuid();
            DB::table('statuses')->insertOrIgnore([
                'status_id' => $newId,
                'status_name' => 'Pending',
                'description' => 'Order has been placed and is awaiting confirmation',
            ]);
            $pendingStatusId = DB::table('statuses')->where('status_name', 'Pending')->value('status_id');
        }
        if (! $pendingStatusId) {
            return redirect()->back()->with('error', 'Status "Pending" is not configured.');
        }

        $orderId = (string) Str::uuid();
        $orderNo = $this->generateOrderNumber();

        $normalizedEmail = $request->filled('contact_email') ? Str::lower(trim((string) $request->contact_email)) : null;
        $existingUser = null;
        if ($normalizedEmail) {
            $existingUser = DB::table('users')
                ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                ->first();
        }

        $shouldCreateUser = $existingUser === null;
        $customerId = $shouldCreateUser ? (string) Str::uuid() : (string) $existingUser->user_id;
        $email = $existingUser ? (string) $existingUser->email : ($request->contact_email ?: ('walkin+' . now()->format('YmdHis') . '+' . substr((string) Str::uuid(), 0, 8) . '@uniprint.local'));

        $quantity = (int) $request->quantity;
        $unitPrice = $request->filled('unit_price') ? (float) $request->unit_price : (float) ($service->base_price ?? 0);
        $itemTotal = $quantity * $unitPrice;

        $dateRequested = Carbon::today()->toDateString();
        $deliveryDate = Carbon::today()->toDateString();

        DB::transaction(function () use (
            $enterprise,
            $businessUserId,
            $orderId,
            $orderNo,
            $customerId,
            $email,
            $shouldCreateUser,
            $request,
            $pendingStatusId,
            $quantity,
            $unitPrice,
            $itemTotal,
            $service,
            $dateRequested,
            $deliveryDate
        ) {
            if ($shouldCreateUser) {
                DB::table('users')->insert([
                    'user_id' => $customerId,
                    'name' => $request->contact_name,
                    'email' => $email,
                    'position' => 'Walk-in',
                    'department' => 'Walk-in',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('customer_orders')->insert([
                'purchase_order_id' => $orderId,
                'customer_id' => $customerId,
                'enterprise_id' => $enterprise->enterprise_id,
                'purpose' => $request->purpose,
                'order_no' => $orderNo,
                'official_receipt_no' => null,
                'date_requested' => $dateRequested,
                'delivery_date' => $deliveryDate,
                'shipping_fee' => $request->fulfillment_method === 'delivery' ? 0 : 0,
                'discount' => 0,
                'subtotal' => $itemTotal,
                'total' => $itemTotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (Schema::hasColumn('customer_orders', 'status_id')) {
                DB::table('customer_orders')->where('purchase_order_id', $orderId)->update([
                    'status_id' => $pendingStatusId,
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasColumn('customer_orders', 'contact_name')) {
                DB::table('customer_orders')->where('purchase_order_id', $orderId)->update([
                    'contact_name' => $request->contact_name,
                    'contact_phone' => $request->contact_phone,
                    'contact_email' => $request->contact_email,
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasColumn('customer_orders', 'rush_option')) {
                DB::table('customer_orders')->where('purchase_order_id', $orderId)->update([
                    'rush_option' => $request->rush_option ?: 'standard',
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasColumn('customer_orders', 'fulfillment_method')) {
                DB::table('customer_orders')->where('purchase_order_id', $orderId)->update([
                    'fulfillment_method' => $request->fulfillment_method,
                    'requested_fulfillment_date' => $request->requested_fulfillment_date,
                    'updated_at' => now(),
                ]);
            }

            DB::table('order_items')->insert([
                'item_id' => (string) Str::uuid(),
                'purchase_order_id' => $orderId,
                'service_id' => $service->service_id,
                'item_description' => $service->service_name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_cost' => $itemTotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('order_status_history')->insert([
                'approval_id' => (string) Str::uuid(),
                'purchase_order_id' => $orderId,
                'user_id' => $businessUserId,
                'status_id' => $pendingStatusId,
                'remarks' => 'Walk-in order created by business',
                'timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('business.orders.details', $orderId)->with('success', 'Walk-in order created.');
    }

    public function orderDetails($id)
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $unreadNotificationsCount = DB::table('order_notifications')
            ->where('recipient_id', session('user_id'))
            ->where('is_read', false)
            ->count();

        $dueExpr = 'customer_orders.date_requested';
        if (Schema::hasColumn('customer_orders', 'pickup_date')) {
            $dueExpr = 'customer_orders.pickup_date';
        } elseif (Schema::hasColumn('customer_orders', 'requested_fulfillment_date')) {
            $dueExpr = 'customer_orders.requested_fulfillment_date';
        } elseif (Schema::hasColumn('customer_orders', 'delivery_date')) {
            $dueExpr = 'customer_orders.delivery_date';
        }

        $dueDateSql = "DATE({$dueExpr})";

        $order = DB::table('customer_orders')
            ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
            ->where('customer_orders.purchase_order_id', $id)
            ->where('customer_orders.enterprise_id', $enterprise->enterprise_id)
            ->select('customer_orders.*', 'users.name as customer_name', 'users.email as customer_email')
            ->addSelect(DB::raw("{$dueDateSql} as due_date"))
            ->first();

        if (!$order) {
            abort(404);
        }

        $fulfillmentMethod = 'pickup';
        if (Schema::hasColumn('customer_orders', 'fulfillment_method')) {
            $fulfillmentMethod = (string) ($order->fulfillment_method ?? 'pickup');
        } elseif (Schema::hasColumn('customer_orders', 'shipping_fee')) {
            $fulfillmentMethod = ((float) ($order->shipping_fee ?? 0)) > 0 ? 'delivery' : 'pickup';
        }

        if (! in_array($fulfillmentMethod, ['pickup', 'delivery'], true)) {
            $fulfillmentMethod = 'pickup';
        }

        // Guard: cash is only valid for pickup fulfillment.
        if (Schema::hasColumn('customer_orders', 'payment_method')) {
            $paymentMethod = (string) ($order->payment_method ?? 'cash');
            if ($paymentMethod === 'cash' && $fulfillmentMethod !== 'pickup') {
                return redirect()->back()->with('error', 'Cash payments are only supported for pickup orders.');
            }
        }

        $requiresFiles = false;
        if (Schema::hasColumn('services', 'requires_file_upload')) {
            $requiresFiles = DB::table('order_items')
                ->join('services', 'order_items.service_id', '=', 'services.service_id')
                ->where('order_items.purchase_order_id', $id)
                ->where('services.requires_file_upload', true)
                ->exists();
        }

        if ($requiresFiles) {
            $hasFiles = DB::table('order_design_files')
                ->where('purchase_order_id', $id)
                ->exists();

            if (! $hasFiles) {
                return redirect()->back()->with('error', 'This order requires a design file upload before it can be confirmed.');
            }
        }

        // Get order items
        $orderItems = DB::table('order_items')
            ->join('services', 'order_items.service_id', '=', 'services.service_id')
            ->where('order_items.purchase_order_id', $id)
            ->select('order_items.*', 'services.service_name')
            ->get();

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

        // Get design files
        $designFiles = DB::table('order_design_files')
            ->where('purchase_order_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get available statuses
        $statuses = DB::table('statuses')->get();

        $currentStatusName = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('order_status_history.purchase_order_id', $id)
            ->orderBy('order_status_history.timestamp', 'desc')
            ->value('statuses.status_name');

        $statusIds = $statuses->pluck('status_id', 'status_name');
        $businessActions = $this->getBusinessStatusActions($currentStatusName, $statusIds, $fulfillmentMethod);

        return view('business.orders.details', compact('order', 'orderItems', 'statusHistory', 'designFiles', 'statuses', 'statusIds', 'currentStatusName', 'enterprise', 'userName', 'businessActions', 'unreadNotificationsCount'));
    }

    public function printOrder($id)
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $dueExpr = 'customer_orders.date_requested';
        if (Schema::hasColumn('customer_orders', 'pickup_date')) {
            $dueExpr = 'customer_orders.pickup_date';
        } elseif (Schema::hasColumn('customer_orders', 'requested_fulfillment_date')) {
            $dueExpr = 'customer_orders.requested_fulfillment_date';
        } elseif (Schema::hasColumn('customer_orders', 'delivery_date')) {
            $dueExpr = 'customer_orders.delivery_date';
        }
        $dueDateSql = "DATE({$dueExpr})";

        $order = DB::table('customer_orders')
            ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
            ->where('customer_orders.purchase_order_id', $id)
            ->where('customer_orders.enterprise_id', $enterprise->enterprise_id)
            ->select('customer_orders.*', 'users.name as customer_name', 'users.email as customer_email')
            ->addSelect(DB::raw("{$dueDateSql} as due_date"))
            ->first();

        if (! $order) {
            abort(404);
        }

        $orderItems = DB::table('order_items')
            ->join('services', 'order_items.service_id', '=', 'services.service_id')
            ->where('order_items.purchase_order_id', $id)
            ->select('order_items.*', 'services.service_name')
            ->get();

        $currentStatusName = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('order_status_history.purchase_order_id', $id)
            ->orderBy('order_status_history.timestamp', 'desc')
            ->value('statuses.status_name') ?? 'Pending';

        return view('business.orders.print', compact('order', 'orderItems', 'enterprise', 'userName', 'currentStatusName'));
    }

    public function notifications(Request $request)
    {
        $userId = session('user_id');
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $query = DB::table('order_notifications')
            ->where('recipient_id', $userId)
            ->orderBy('created_at', 'desc');

        $unreadNotificationsCount = DB::table('order_notifications')
            ->where('recipient_id', $userId)
            ->where('is_read', false)
            ->count();

        $notifications = $query->paginate(20);

        return view('business.notifications', compact('notifications', 'enterprise', 'userName', 'unreadNotificationsCount'));
    }

    public function markNotificationRead(Request $request, $id)
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

    private function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = Carbon::now()->format('Ymd');

        $lastOrder = DB::table('customer_orders')
            ->where('order_no', 'LIKE', "{$prefix}{$date}%")
            ->orderBy('order_no', 'desc')
            ->value('order_no');

        $sequence = $lastOrder ? (int) substr($lastOrder, -4) + 1 : 1;

        return $prefix . $date . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function confirmOrder($id)
    {
        $enterprise = $this->getUserEnterprise();
        $userId = session('user_id');

        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (!$order) {
            abort(404);
        }

        $currentStatusName = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('order_status_history.purchase_order_id', $id)
            ->orderBy('order_status_history.timestamp', 'desc')
            ->value('statuses.status_name');

        if ($currentStatusName && $currentStatusName !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending orders can be confirmed.');
        }

        $confirmedStatusId = DB::table('statuses')->where('status_name', 'Confirmed')->value('status_id');
        if (!$confirmedStatusId) {
            $confirmedStatusId = DB::table('statuses')->where('status_name', 'Processing')->value('status_id');
        }

        if (!$confirmedStatusId) {
            return redirect()->back()->with('error', 'Order status "Confirmed" is not configured. Please seed statuses.');
        }

        DB::table('order_status_history')->insert([
            'approval_id' => Str::uuid(),
            'purchase_order_id' => $id,
            'user_id' => $userId,
            'status_id' => $confirmedStatusId,
            'remarks' => 'Order confirmed by business',
            'timestamp' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (Schema::hasColumn('customer_orders', 'status_id')) {
            DB::table('customer_orders')
                ->where('purchase_order_id', $id)
                ->where('enterprise_id', $enterprise->enterprise_id)
                ->update([
                    'status_id' => $confirmedStatusId,
                    'updated_at' => now(),
                ]);
        }

        $newStatusName = DB::table('statuses')->where('status_id', $confirmedStatusId)->value('status_name') ?? 'Confirmed';

        DB::table('order_notifications')->insert([
            'notification_id' => Str::uuid(),
            'purchase_order_id' => $id,
            'recipient_id' => $order->customer_id,
            'notification_type' => 'status_change',
            'title' => 'Order Confirmed',
            'message' => "Your order #{$order->order_no} has been confirmed and is now: {$newStatusName}.",
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logAudit('update', 'order_status', $id, "Order confirmed by business", ['status' => $currentStatusName], ['status' => $newStatusName]);

        return redirect()->back()->with('success', 'Order confirmed successfully');
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|uuid',
            'remarks' => 'nullable|string|max:1000',
            'expected_completion' => 'nullable|date',
        ]);

        $enterprise = $this->getUserEnterprise();
        $userId = session('user_id');

        // Verify order belongs to enterprise
        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (!$order) {
            abort(404);
        }

        // Get old status
        $oldStatus = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('purchase_order_id', $id)
            ->orderBy('timestamp', 'desc')
            ->select('statuses.status_name')
            ->first();

        $currentStatusName = $oldStatus?->status_name ?? 'Pending';

        // Normalize legacy/alternate statuses
        if ($currentStatusName === 'Processing') {
            $currentStatusName = 'In Progress';
        }
        if ($currentStatusName === 'Shipped') {
            $currentStatusName = 'Delivered';
        }

        $statusLookup = DB::table('statuses')->pluck('status_name', 'status_id');
        $newStatusName = $statusLookup[$request->status_id] ?? null;

        if (! $newStatusName) {
            return redirect()->back()->with('error', 'Selected status is not configured.');
        }

        // Legacy compatibility: treat Shipped as Delivered.
        $targetStatusId = $request->status_id;
        if ($newStatusName === 'Shipped') {
            $deliveredStatusId = DB::table('statuses')->where('status_name', 'Delivered')->value('status_id');
            if (! $deliveredStatusId) {
                return redirect()->back()->with('error', 'Status "Delivered" is not configured.');
            }
            $newStatusName = 'Delivered';
            $targetStatusId = $deliveredStatusId;
        }

        $fulfillmentMethod = 'pickup';
        if (Schema::hasColumn('customer_orders', 'fulfillment_method')) {
            $fulfillmentMethod = (string) ($order->fulfillment_method ?? 'pickup');
        } elseif (Schema::hasColumn('customer_orders', 'shipping_fee')) {
            $fulfillmentMethod = ((float) ($order->shipping_fee ?? 0)) > 0 ? 'delivery' : 'pickup';
        }

        if (! in_array($fulfillmentMethod, ['pickup', 'delivery'], true)) {
            $fulfillmentMethod = 'pickup';
        }

        $requiresFiles = false;
        if (Schema::hasColumn('services', 'requires_file_upload')) {
            $requiresFiles = DB::table('order_items')
                ->join('services', 'order_items.service_id', '=', 'services.service_id')
                ->where('order_items.purchase_order_id', $id)
                ->where('services.requires_file_upload', true)
                ->exists();
        }

        if ($requiresFiles && $newStatusName !== 'Cancelled') {
            $hasFiles = DB::table('order_design_files')
                ->where('purchase_order_id', $id)
                ->exists();

            if (! $hasFiles) {
                return redirect()->back()->with('error', 'This order requires design files from the customer before you can proceed.');
            }
        }

        // Prevent business from marking as Completed; this is for customers.
        if ($newStatusName === 'Completed') {
            return redirect()->back()->with('error', 'Customers must confirm completion.');
        }

        $allowedTransitions = [
            'Pending' => ['Confirmed', 'Cancelled'],
            'Confirmed' => ['In Progress', 'Cancelled'],
            'In Progress' => $fulfillmentMethod === 'pickup' ? ['Ready for Pickup', 'Delivered'] : ['Delivered'],
            'Ready for Pickup' => ['Delivered'],
            'Delivered' => [],
            'Completed' => [],
            'Cancelled' => [],
        ];

        // Enforce pickup vs delivery flow.
        if ($currentStatusName === 'In Progress') {
            if ($fulfillmentMethod === 'pickup' && $newStatusName === 'Delivered') {
                return redirect()->back()->with('error', 'Pickup orders must be marked Ready for Pickup before confirming pickup.');
            }
            if ($fulfillmentMethod === 'delivery' && $newStatusName === 'Ready for Pickup') {
                return redirect()->back()->with('error', 'Delivery orders cannot be marked Ready for Pickup.');
            }
        }

        $allowedNext = $allowedTransitions[$currentStatusName] ?? [];

        if (! in_array($newStatusName, $allowedNext, true)) {
            return redirect()->back()->with('error', "Cannot move order from {$currentStatusName} to {$newStatusName}.");
        }

        // Downpayment gating: block starting production unless the required downpayment is verified.
        if ($currentStatusName === 'Confirmed' && $newStatusName === 'In Progress') {
            if (Schema::hasColumn('services', 'requires_downpayment') && Schema::hasColumn('services', 'downpayment_percent')) {
                $downpaymentPercent = (float) (DB::table('order_items')
                    ->join('services', 'order_items.service_id', '=', 'services.service_id')
                    ->where('order_items.purchase_order_id', $id)
                    ->where('services.requires_downpayment', true)
                    ->max('services.downpayment_percent') ?? 0);

                if ($downpaymentPercent > 0) {
                    $requiredAmount = ((float) ($order->total ?? 0)) * ($downpaymentPercent / 100);
                    $paidAmount = 0.0;

                    if (Schema::hasTable('payments')) {
                        $p = DB::table('payments')->where('purchase_order_id', $id)->first();
                        if ($p && !empty($p->is_verified)) {
                            $paidAmount = (float) ($p->amount_paid ?? 0);
                        }
                    }

                    if ($paidAmount + 0.00001 < $requiredAmount) {
                        return redirect()->back()->with('error', 'This order requires a downpayment before production can start.');
                    }
                }
            }
        }

        // Create status history entry
        DB::table('order_status_history')->insert([
            'approval_id' => Str::uuid(),
            'purchase_order_id' => $id,
            'user_id' => $userId,
            'status_id' => $targetStatusId,
            'remarks' => $request->remarks ?? 'Status updated by business',
            'timestamp' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->update([
                'status_id' => $targetStatusId,
                'updated_at' => now(),
            ]);

        // Cash-on-pickup: mark paid when pickup is confirmed (Delivered for pickup flow)
        if (Schema::hasColumn('customer_orders', 'payment_method') && Schema::hasColumn('customer_orders', 'payment_status')) {
            $isCash = ($order->payment_method ?? null) === 'cash';
            $isPickup = $fulfillmentMethod === 'pickup';
            if ($isCash && $isPickup && $newStatusName === 'Delivered') {
                DB::table('customer_orders')
                    ->where('purchase_order_id', $id)
                    ->where('enterprise_id', $enterprise->enterprise_id)
                    ->update([
                        'payment_status' => 'paid',
                        'updated_at' => now(),
                    ]);

                if (Schema::hasTable('payments')) {
                    DB::table('payments')
                        ->where('purchase_order_id', $id)
                        ->update([
                            'amount_paid' => DB::raw('amount_due'),
                            'is_verified' => true,
                            'payment_date_time' => now(),
                            'updated_at' => now(),
                        ]);
                }
            }
        }

        // Send notification to customer
        DB::table('order_notifications')->insert([
            'notification_id' => Str::uuid(),
            'purchase_order_id' => $id,
            'recipient_id' => $order->customer_id,
            'notification_type' => 'status_change',
            'title' => 'Order Status Updated',
            'message' => "Your order #{$order->order_no} has been updated to: {$newStatusName}. " . ($request->remarks ?? ''),
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log audit
        $this->logAudit('update', 'order_status', $id, "Order status changed from {$currentStatusName} to {$newStatusName}", 
            ['status' => $currentStatusName],
            ['status' => $newStatusName, 'remarks' => $request->remarks]
        );

        return redirect()->back()->with('success', "Order moved to {$newStatusName}");
    }

    public function markDownpaymentReceived(Request $request, $id)
    {
        $enterprise = $this->getUserEnterprise();
        $userId = session('user_id');

        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $order) {
            abort(404);
        }

        if (!Schema::hasColumn('services', 'requires_downpayment') || !Schema::hasColumn('services', 'downpayment_percent')) {
            return redirect()->back()->with('error', 'Downpayment settings are not available.');
        }

        $downpaymentPercent = (float) (DB::table('order_items')
            ->join('services', 'order_items.service_id', '=', 'services.service_id')
            ->where('order_items.purchase_order_id', $id)
            ->where('services.requires_downpayment', true)
            ->max('services.downpayment_percent') ?? 0);

        if ($downpaymentPercent <= 0) {
            return redirect()->back()->with('error', 'This order does not require a downpayment.');
        }

        $requiredAmount = ((float) ($order->total ?? 0)) * ($downpaymentPercent / 100);
        if ($requiredAmount <= 0) {
            return redirect()->back()->with('error', 'Cannot compute required downpayment amount for this order.');
        }

        $paymentMethod = 'gcash';

        if (Schema::hasTable('payments')) {
            $p = DB::table('payments')->where('purchase_order_id', $id)->first();
            if ($p) {
                DB::table('payments')->where('purchase_order_id', $id)->update([
                    'amount_paid' => max((float) ($p->amount_paid ?? 0), $requiredAmount),
                    'is_verified' => true,
                    'payment_date_time' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('payments')->insert([
                    'payment_id' => Str::uuid(),
                    'purchase_order_id' => $id,
                    'payment_method' => $paymentMethod,
                    'amount_paid' => $requiredAmount,
                    'amount_due' => (float) ($order->total ?? 0),
                    'payment_date_time' => now(),
                    'is_verified' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('transactions')) {
            DB::table('transactions')->insert([
                'transaction_id' => Str::uuid(),
                'purchase_order_id' => $id,
                'payment_method' => $paymentMethod,
                'transaction_ref' => 'DP-' . Str::uuid(),
                'amount' => $requiredAmount,
                'transaction_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('order_status_history')) {
            $statusId = DB::table('statuses')->where('status_name', 'Confirmed')->value('status_id');
            if ($statusId) {
                DB::table('order_status_history')->insert([
                    'approval_id' => Str::uuid(),
                    'purchase_order_id' => $id,
                    'user_id' => $userId,
                    'status_id' => $statusId,
                    'remarks' => 'Downpayment marked as received by business',
                    'timestamp' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->logAudit('update', 'payment', $id, 'Marked downpayment received', null, [
            'downpayment_percent' => $downpaymentPercent,
            'required_amount' => $requiredAmount,
        ]);

        return redirect()->back()->with('success', 'Downpayment marked as received.');
    }

    // =====================================================
    // SERVICE MANAGEMENT
    // =====================================================

    public function services()
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $services = DB::table('services')
            ->leftJoin(DB::raw('(SELECT service_id, COUNT(*) as order_count FROM order_items GROUP BY service_id) as orders'), 'services.service_id', '=', 'orders.service_id')
            ->leftJoin(DB::raw('(SELECT service_id, COUNT(*) as customization_count FROM customization_options GROUP BY service_id) as customs'), 'services.service_id', '=', 'customs.service_id')
            ->where('services.enterprise_id', $enterprise->enterprise_id)
            ->select('services.*', DB::raw('COALESCE(orders.order_count, 0) as order_count'), DB::raw('COALESCE(customs.customization_count, 0) as customization_count'))
            ->orderBy('services.created_at', 'desc')
            ->paginate(20);

        return view('business.services.index', compact('services', 'enterprise', 'userName'));
    }

    public function createService(Request $request)
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $view = view('business.services.create', compact('enterprise', 'userName'));
        if ($request->ajax()) {
            $sections = $view->renderSections();
            return $sections['content'] ?? $view->render();
        }

        return $view;
    }

    public function storeService(Request $request)
    {
        $rules = [
            'service_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'base_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active' => 'boolean',
        ];

        if (Schema::hasColumn('services', 'file_upload_enabled')) {
            $rules['file_upload_enabled'] = 'boolean';
        }

        if (Schema::hasColumn('services', 'requires_file_upload')) {
            $rules['requires_file_upload'] = 'boolean';
        }

        if (Schema::hasColumn('services', 'fulfillment_type')) {
            $rules['fulfillment_type'] = 'required|in:pickup,delivery,both';
        }

        if (Schema::hasColumn('services', 'allowed_payment_methods')) {
            $rules['allowed_payment_methods'] = 'nullable|array';
            $rules['allowed_payment_methods.*'] = 'in:gcash,cash';
        }

        if (Schema::hasColumn('services', 'requires_downpayment')) {
            $rules['requires_downpayment'] = 'boolean';
        }
        if (Schema::hasColumn('services', 'downpayment_percent')) {
            $rules['downpayment_percent'] = 'nullable|numeric|min:0|max:100';
        }

        $request->validate($rules);

        $enterprise = $this->getUserEnterprise();
        $serviceId = Str::uuid();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('service-images', 'public');
        }

        $allowedPaymentMethods = $request->input('allowed_payment_methods', []);
        if (!is_array($allowedPaymentMethods)) {
            $allowedPaymentMethods = [];
        }
        $allowedPaymentMethodsJson = json_encode(array_values(array_unique($allowedPaymentMethods)));

        $requiresDownpayment = Schema::hasColumn('services', 'requires_downpayment')
            ? (bool) $request->has('requires_downpayment')
            : false;
        $downpaymentPercent = Schema::hasColumn('services', 'downpayment_percent')
            ? (float) $request->input('downpayment_percent', 0)
            : 0;

        if ($requiresDownpayment && $downpaymentPercent <= 0) {
            return redirect()->back()->with('error', 'Downpayment percent must be greater than 0 when downpayment is required.');
        }
        if (! $requiresDownpayment) {
            $downpaymentPercent = 0;
        }

        if ($requiresDownpayment && !in_array('gcash', $allowedPaymentMethods, true)) {
            return redirect()->back()->with('error', 'Downpayment requires GCash to be enabled in allowed payment methods.');
        }

        $insert = [
            'service_id' => $serviceId,
            'enterprise_id' => $enterprise->enterprise_id,
            'service_name' => $request->service_name,
            'description' => $request->description,
            'image_path' => $imagePath,
            'fulfillment_type' => $request->fulfillment_type,
            'allowed_payment_methods' => $allowedPaymentMethodsJson,
            'requires_downpayment' => $requiresDownpayment,
            'downpayment_percent' => $downpaymentPercent,
            'base_price' => $request->base_price,
            'is_active' => $request->has('is_active'),
            'file_upload_enabled' => $request->has('file_upload_enabled'),
            'requires_file_upload' => $request->has('requires_file_upload'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('services', 'file_upload_enabled') && $insert['requires_file_upload']) {
            $insert['file_upload_enabled'] = true;
        }

        if (!Schema::hasColumn('services', 'image_path')) {
            unset($insert['image_path']);
        }

        if (!Schema::hasColumn('services', 'fulfillment_type')) {
            unset($insert['fulfillment_type']);
        }

        if (!Schema::hasColumn('services', 'allowed_payment_methods')) {
            unset($insert['allowed_payment_methods']);
        }

        if (!Schema::hasColumn('services', 'file_upload_enabled')) {
            unset($insert['file_upload_enabled']);
        }

        if (!Schema::hasColumn('services', 'requires_file_upload')) {
            unset($insert['requires_file_upload']);
        }

        if (!Schema::hasColumn('services', 'requires_downpayment')) {
            unset($insert['requires_downpayment']);
        }
        if (!Schema::hasColumn('services', 'downpayment_percent')) {
            unset($insert['downpayment_percent']);
        }

        DB::table('services')->insert($insert);

        // Log audit
        $this->logAudit('create', 'service', $serviceId, "Created service: {$request->service_name}", null, $request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Service created successfully',
                'service_id' => (string) $serviceId,
            ]);
        }

        return redirect()->route('business.services.index')->with('success', 'Service created successfully');
    }

    public function editService(Request $request, $id)
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (!$service) {
            abort(404);
        }

        $view = view('business.services.edit', compact('service', 'enterprise', 'userName'));
        if ($request->ajax()) {
            $sections = $view->renderSections();
            return $sections['content'] ?? $view->render();
        }

        return $view;
    }

    public function updateService(Request $request, $id)
    {
        $rules = [
            'service_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'base_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active' => 'boolean',
        ];

        if (Schema::hasColumn('services', 'file_upload_enabled')) {
            $rules['file_upload_enabled'] = 'boolean';
        }

        if (Schema::hasColumn('services', 'requires_file_upload')) {
            $rules['requires_file_upload'] = 'boolean';
        }

        if (Schema::hasColumn('services', 'fulfillment_type')) {
            $rules['fulfillment_type'] = 'required|in:pickup,delivery,both';
        }

        if (Schema::hasColumn('services', 'allowed_payment_methods')) {
            $rules['allowed_payment_methods'] = 'nullable|array';
            $rules['allowed_payment_methods.*'] = 'in:gcash,cash';
        }

        if (Schema::hasColumn('services', 'requires_downpayment')) {
            $rules['requires_downpayment'] = 'boolean';
        }
        if (Schema::hasColumn('services', 'downpayment_percent')) {
            $rules['downpayment_percent'] = 'nullable|numeric|min:0|max:100';
        }

        $request->validate($rules);

        $enterprise = $this->getUserEnterprise();

        $oldService = DB::table('services')->where('service_id', $id)->where('enterprise_id', $enterprise->enterprise_id)->first();
        
        if (!$oldService) {
            abort(404);
        }

        $allowedPaymentMethods = $request->input('allowed_payment_methods', []);
        if (!is_array($allowedPaymentMethods)) {
            $allowedPaymentMethods = [];
        }
        $allowedPaymentMethodsJson = json_encode(array_values(array_unique($allowedPaymentMethods)));

        $requiresDownpayment = Schema::hasColumn('services', 'requires_downpayment')
            ? (bool) $request->has('requires_downpayment')
            : false;
        $downpaymentPercent = Schema::hasColumn('services', 'downpayment_percent')
            ? (float) $request->input('downpayment_percent', 0)
            : 0;

        if ($requiresDownpayment && $downpaymentPercent <= 0) {
            return redirect()->back()->with('error', 'Downpayment percent must be greater than 0 when downpayment is required.');
        }
        if (! $requiresDownpayment) {
            $downpaymentPercent = 0;
        }

        if ($requiresDownpayment && !in_array('gcash', $allowedPaymentMethods, true)) {
            return redirect()->back()->with('error', 'Downpayment requires GCash to be enabled in allowed payment methods.');
        }

        $update = [
            'service_name' => $request->service_name,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'is_active' => $request->has('is_active'),
            'fulfillment_type' => $request->fulfillment_type,
            'allowed_payment_methods' => $allowedPaymentMethodsJson,
            'file_upload_enabled' => $request->has('file_upload_enabled'),
            'requires_file_upload' => $request->has('requires_file_upload'),
            'requires_downpayment' => $requiresDownpayment,
            'downpayment_percent' => $downpaymentPercent,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('services', 'file_upload_enabled') && $update['requires_file_upload']) {
            $update['file_upload_enabled'] = true;
        }

        if (!Schema::hasColumn('services', 'fulfillment_type')) {
            unset($update['fulfillment_type']);
        }

        if (!Schema::hasColumn('services', 'allowed_payment_methods')) {
            unset($update['allowed_payment_methods']);
        }

        if (!Schema::hasColumn('services', 'file_upload_enabled')) {
            unset($update['file_upload_enabled']);
        }

        if (!Schema::hasColumn('services', 'requires_file_upload')) {
            unset($update['requires_file_upload']);
        }

        if (!Schema::hasColumn('services', 'requires_downpayment')) {
            unset($update['requires_downpayment']);
        }
        if (!Schema::hasColumn('services', 'downpayment_percent')) {
            unset($update['downpayment_percent']);
        }

        if ($request->hasFile('image') && Schema::hasColumn('services', 'image_path')) {
            if (!empty($oldService->image_path)) {
                Storage::disk('public')->delete($oldService->image_path);
            }
            $update['image_path'] = $request->file('image')->store('service-images', 'public');
        }

        DB::table('services')
            ->where('service_id', $id)
            ->update($update);

        // Log audit
        $this->logAudit('update', 'service', $id, "Updated service: {$request->service_name}", 
            (array)$oldService, 
            $request->all()
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Service updated successfully',
                'service_id' => (string) $id,
            ]);
        }

        return redirect()->route('business.services.index')->with('success', 'Service updated successfully');
    }

    public function updateServiceUploadSettings(Request $request, $id)
    {
        if (! Schema::hasColumn('services', 'requires_file_upload')) {
            return redirect()->back()->with('error', 'File upload settings are not available. Please run migrations.');
        }

        $request->validate([
            'file_upload_enabled' => 'nullable|boolean',
            'requires_file_upload' => 'nullable|boolean',
        ]);

        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (! $service) {
            abort(404);
        }

        $requires = $request->has('requires_file_upload');
        $enabled = Schema::hasColumn('services', 'file_upload_enabled') ? $request->has('file_upload_enabled') : $requires;

        if ($requires) {
            $enabled = true;
        }

        $update = [
            'requires_file_upload' => $requires,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('services', 'file_upload_enabled')) {
            $update['file_upload_enabled'] = $enabled;
        }

        DB::table('services')
            ->where('service_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->update($update);

        $this->logAudit(
            'update',
            'service',
            $id,
            'Updated service file upload settings',
            [
                'file_upload_enabled' => (bool) ($service->file_upload_enabled ?? false),
                'requires_file_upload' => (bool) ($service->requires_file_upload ?? false),
            ],
            [
                'file_upload_enabled' => $enabled,
                'requires_file_upload' => $requires,
            ]
        );

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'ok' => true,
                'service_id' => $id,
                'file_upload_enabled' => $enabled,
                'requires_file_upload' => $requires,
            ]);
        }

        return redirect()->back()->with('success', 'File upload settings updated.');
    }

    public function toggleServiceStatus($id)
    {
        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (!$service) {
            abort(404);
        }

        $newStatus = !(bool) $service->is_active;

        DB::table('services')
            ->where('service_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->update([
                'is_active' => $newStatus,
                'updated_at' => now(),
            ]);

        $this->logAudit('update', 'service', $id, ($newStatus ? 'Activated' : 'Deactivated') . " service: {$service->service_name}", ['is_active' => (bool) $service->is_active], ['is_active' => $newStatus]);

        return redirect()->back()->with('success', $newStatus ? 'Service activated successfully' : 'Service deactivated successfully');
    }

    public function deleteService($id)
    {
        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')->where('service_id', $id)->where('enterprise_id', $enterprise->enterprise_id)->first();
        
        if (!$service) {
            abort(404);
        }

        DB::table('services')->where('service_id', $id)->delete();

        // Log audit
        $this->logAudit('delete', 'service', $id, "Deleted service: {$service->service_name}", (array)$service, null);

        return redirect()->route('business.services.index')->with('success', 'Service deleted successfully');
    }


    // =====================================================
    // CUSTOMIZATION MANAGEMENT
    // =====================================================

    public function customizations($serviceId)
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (!$service) {
            abort(404);
        }

        $customizations = DB::table('customization_options')
            ->where('service_id', $serviceId)
            ->orderBy('option_type')
            ->orderBy('option_name')
            ->get();

        $customFields = collect();
        if (Schema::hasTable('service_custom_fields')) {
            $customFields = DB::table('service_custom_fields')
                ->where('service_id', $serviceId)
                ->orderBy('sort_order')
                ->orderBy('field_label')
                ->get();
        }

        return view('business.customizations.index', compact('service', 'customizations', 'customFields', 'enterprise', 'userName'));
    }

    public function storeCustomField(Request $request, $serviceId)
    {
        $request->validate([
            'field_label' => 'required|string|max:150',
            'placeholder' => 'nullable|string|max:255',
            'is_required' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:1000',
        ]);

        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (!$service) {
            abort(404);
        }

        $fieldId = Str::uuid();

        DB::table('service_custom_fields')->insert([
            'field_id' => $fieldId,
            'service_id' => $serviceId,
            'field_label' => $request->field_label,
            'placeholder' => $request->placeholder,
            'is_required' => (bool) $request->input('is_required', false),
            'sort_order' => (int) $request->input('sort_order', 0),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logAudit('create', 'service_custom_fields', $fieldId, "Created custom text field: {$request->field_label} for service {$service->service_name}", null, $request->all());

        return redirect()->back()->with('success', 'Custom field created successfully');
    }

    public function updateCustomField(Request $request, $serviceId, $fieldId)
    {
        $request->validate([
            'field_label' => 'required|string|max:150',
            'placeholder' => 'nullable|string|max:255',
            'is_required' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:1000',
        ]);

        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (!$service) {
            abort(404);
        }

        $oldField = DB::table('service_custom_fields')
            ->where('field_id', $fieldId)
            ->where('service_id', $serviceId)
            ->first();

        if (!$oldField) {
            abort(404);
        }

        DB::table('service_custom_fields')
            ->where('field_id', $fieldId)
            ->where('service_id', $serviceId)
            ->update([
                'field_label' => $request->field_label,
                'placeholder' => $request->placeholder,
                'is_required' => (bool) $request->input('is_required', false),
                'sort_order' => (int) $request->input('sort_order', 0),
                'updated_at' => now(),
            ]);

        $this->logAudit('update', 'service_custom_fields', $fieldId, "Updated custom text field: {$request->field_label}", (array) $oldField, $request->all());

        return redirect()->back()->with('success', 'Custom field updated successfully');
    }

    public function deleteCustomField($serviceId, $fieldId)
    {
        $enterprise = $this->getUserEnterprise();

        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (!$service) {
            abort(404);
        }

        $field = DB::table('service_custom_fields')
            ->where('field_id', $fieldId)
            ->where('service_id', $serviceId)
            ->first();

        if (!$field) {
            abort(404);
        }

        DB::table('service_custom_fields')
            ->where('field_id', $fieldId)
            ->where('service_id', $serviceId)
            ->delete();

        $this->logAudit('delete', 'service_custom_fields', $fieldId, "Deleted custom text field: {$field->field_label}", (array) $field, null);

        return redirect()->back()->with('success', 'Custom field deleted successfully');
    }

    public function storeCustomization(Request $request, $serviceId)
    {
        $request->validate([
            'option_name' => 'required|string|max:200',
            'option_type' => 'required|string|max:100',
            'price_modifier' => 'required|numeric',
        ]);

        $enterprise = $this->getUserEnterprise();

        // Verify service belongs to enterprise
        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (!$service) {
            abort(404);
        }

        $optionId = Str::uuid();

        DB::table('customization_options')->insert([
            'option_id' => $optionId,
            'service_id' => $serviceId,
            'option_name' => $request->option_name,
            'option_type' => $request->option_type,
            'price_modifier' => $request->price_modifier,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log audit
        $this->logAudit('create', 'customization', $optionId, "Created customization option: {$request->option_name} for service {$service->service_name}", null, $request->all());

        return redirect()->back()->with('success', 'Customization option created successfully');
    }

    public function updateCustomization(Request $request, $serviceId, $optionId)
    {
        $request->validate([
            'option_name' => 'required|string|max:200',
            'option_type' => 'required|string|max:100',
            'price_modifier' => 'required|numeric',
        ]);

        $enterprise = $this->getUserEnterprise();

        // Verify service belongs to enterprise
        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (!$service) {
            abort(404);
        }

        $oldOption = DB::table('customization_options')
            ->where('option_id', $optionId)
            ->where('service_id', $serviceId)
            ->first();

        if (!$oldOption) {
            abort(404);
        }

        DB::table('customization_options')
            ->where('option_id', $optionId)
            ->where('service_id', $serviceId)
            ->update([
                'option_name' => $request->option_name,
                'option_type' => $request->option_type,
                'price_modifier' => $request->price_modifier,
                'updated_at' => now(),
            ]);

        // Log audit
        $this->logAudit('update', 'customization', $optionId, "Updated customization option: {$request->option_name}", (array)$oldOption, $request->all());

        return redirect()->back()->with('success', 'Customization option updated successfully');
    }

    public function deleteCustomization($serviceId, $optionId)
    {
        $enterprise = $this->getUserEnterprise();

        // Verify service belongs to enterprise
        $service = DB::table('services')
            ->where('service_id', $serviceId)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (!$service) {
            abort(404);
        }

        $option = DB::table('customization_options')
            ->where('option_id', $optionId)
            ->where('service_id', $serviceId)
            ->first();

        if (!$option) {
            abort(404);
        }

        DB::table('customization_options')
            ->where('option_id', $optionId)
            ->where('service_id', $serviceId)
            ->delete();

        // Log audit
        $this->logAudit('delete', 'customization', $optionId, "Deleted customization option: {$option->option_name}", (array)$option, null);

        return redirect()->back()->with('success', 'Customization option deleted successfully');
    }

    // =====================================================
    // PRICING RULES MANAGEMENT
    // =====================================================

    public function pricingRules()
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $rules = DB::table('pricing_rules')
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->orderBy('priority')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('business.pricing.index', compact('rules', 'enterprise', 'userName'));
    }

    public function createPricingRule(Request $request)
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $view = view('business.pricing.create', compact('enterprise', 'userName'));
        if ($request->ajax()) {
            $sections = $view->renderSections();
            return $sections['content'] ?? $view->render();
        }

        return $view;
    }

    public function storePricingRule(Request $request)
    {
        $request->validate([
            'rule_name' => 'required|string|max:200',
            'rule_type' => 'required|string|max:50',
            'rule_description' => 'nullable|string',
            'calculation_method' => 'required|in:percentage,fixed_amount,formula',
            'value' => 'required_unless:calculation_method,formula|numeric',
            'formula' => 'required_if:calculation_method,formula|nullable|string',
            'priority' => 'required|integer|min:0',
            'conditions' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $enterprise = $this->getUserEnterprise();
        $ruleId = Str::uuid();

        $value = $request->calculation_method === 'formula' ? 0 : $request->value;

        DB::table('pricing_rules')->insert([
            'rule_id' => $ruleId,
            'enterprise_id' => $enterprise->enterprise_id,
            'rule_name' => $request->rule_name,
            'rule_type' => $request->rule_type,
            'rule_description' => $request->rule_description,
            'conditions' => $request->conditions ?? '[]',
            'calculation_method' => $request->calculation_method,
            'value' => $value,
            'formula' => $request->formula,
            'priority' => $request->priority,
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log audit
        $this->logAudit('create', 'pricing_rule', $ruleId, "Created pricing rule: {$request->rule_name}", null, $request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pricing rule created successfully',
                'rule_id' => (string) $ruleId,
            ]);
        }

        return redirect()->route('business.pricing.index')->with('success', 'Pricing rule created successfully');
    }

    public function editPricingRule(Request $request, $id)
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $rule = DB::table('pricing_rules')
            ->where('rule_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->first();

        if (!$rule) {
            abort(404);
        }

        $view = view('business.pricing.edit', compact('rule', 'enterprise', 'userName'));
        if ($request->ajax()) {
            $sections = $view->renderSections();
            return $sections['content'] ?? $view->render();
        }

        return $view;
    }

    public function updatePricingRule(Request $request, $id)
    {
        $request->validate([
            'rule_name' => 'required|string|max:200',
            'rule_type' => 'required|string|max:50',
            'rule_description' => 'nullable|string',
            'calculation_method' => 'required|in:percentage,fixed_amount,formula',
            'value' => 'required_unless:calculation_method,formula|numeric',
            'formula' => 'required_if:calculation_method,formula|nullable|string',
            'priority' => 'required|integer|min:0',
            'conditions' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $enterprise = $this->getUserEnterprise();

        $oldRule = DB::table('pricing_rules')->where('rule_id', $id)->where('enterprise_id', $enterprise->enterprise_id)->first();

        if (!$oldRule) {
            abort(404);
        }

        $value = $request->calculation_method === 'formula' ? 0 : $request->value;

        DB::table('pricing_rules')
            ->where('rule_id', $id)
            ->update([
                'rule_name' => $request->rule_name,
                'rule_type' => $request->rule_type,
                'rule_description' => $request->rule_description,
                'conditions' => $request->conditions ?? '[]',
                'calculation_method' => $request->calculation_method,
                'value' => $value,
                'formula' => $request->formula,
                'priority' => $request->priority,
                'is_active' => $request->has('is_active'),
                'updated_at' => now(),
            ]);

        // Log audit
        $this->logAudit('update', 'pricing_rule', $id, "Updated pricing rule: {$request->rule_name}", (array)$oldRule, $request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pricing rule updated successfully',
                'rule_id' => (string) $id,
            ]);
        }

        return redirect()->route('business.pricing.index')->with('success', 'Pricing rule updated successfully');
    }

    public function deletePricingRule($id)
    {
        $enterprise = $this->getUserEnterprise();

        $rule = DB::table('pricing_rules')->where('rule_id', $id)->where('enterprise_id', $enterprise->enterprise_id)->first();

        if (!$rule) {
            abort(404);
        }

        DB::table('pricing_rules')->where('rule_id', $id)->delete();

        // Log audit
        $this->logAudit('delete', 'pricing_rule', $id, "Deleted pricing rule: {$rule->rule_name}", (array)$rule, null);

        return redirect()->route('business.pricing.index')->with('success', 'Pricing rule deleted successfully');
    }

    // =====================================================
    // DESIGN FILE MANAGEMENT
    // =====================================================

    public function approveDesignFile(Request $request, $fileId)
    {
        $userId = session('user_id');
        $enterprise = $this->getUserEnterprise();

        $file = DB::table('order_design_files')
            ->join('customer_orders', 'order_design_files.purchase_order_id', '=', 'customer_orders.purchase_order_id')
            ->where('order_design_files.file_id', $fileId)
            ->where('customer_orders.enterprise_id', $enterprise->enterprise_id)
            ->select('order_design_files.*', 'customer_orders.customer_id')
            ->first();

        if (!$file) {
            abort(404);
        }

        DB::table('order_design_files')
            ->where('file_id', $fileId)
            ->update([
                'is_approved' => true,
                'approved_by' => $userId,
                'approved_at' => now(),
                'updated_at' => now(),
            ]);

        // Send notification to customer
        DB::table('order_notifications')->insert([
            'notification_id' => Str::uuid(),
            'purchase_order_id' => $file->purchase_order_id,
            'recipient_id' => $file->customer_id,
            'notification_type' => 'file_upload',
            'title' => 'Design File Approved',
            'message' => "Your design file '{$file->file_name}' has been approved and is now in production.",
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log audit
        $this->logAudit('update', 'design_file', $fileId, "Approved design file: {$file->file_name}", null, ['approved' => true]);

        return redirect()->back()->with('success', 'Design file approved successfully');
    }

    public function rejectDesignFile(Request $request, $fileId)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $userId = session('user_id');
        $enterprise = $this->getUserEnterprise();

        $file = DB::table('order_design_files')
            ->join('customer_orders', 'order_design_files.purchase_order_id', '=', 'customer_orders.purchase_order_id')
            ->where('order_design_files.file_id', $fileId)
            ->where('customer_orders.enterprise_id', $enterprise->enterprise_id)
            ->select('order_design_files.*', 'customer_orders.customer_id')
            ->first();

        if (!$file) {
            abort(404);
        }

        // Send notification to customer with rejection reason
        DB::table('order_notifications')->insert([
            'notification_id' => Str::uuid(),
            'purchase_order_id' => $file->purchase_order_id,
            'recipient_id' => $file->customer_id,
            'notification_type' => 'file_upload',
            'title' => 'Design File Needs Revision',
            'message' => "Your design file '{$file->file_name}' needs revision. Reason: {$request->rejection_reason}",
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log audit
        $this->logAudit('update', 'design_file', $fileId, "Rejected design file: {$file->file_name}", null, ['rejected' => true, 'reason' => $request->rejection_reason]);

        return redirect()->back()->with('success', 'Design file rejected. Customer has been notified.');
    }

    /**
     * Display the business chat interface
     */
    public function chat()
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        return view('business.chat', compact('enterprise', 'userName'));
    }
}
