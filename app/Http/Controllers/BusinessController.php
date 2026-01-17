<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    /**
     * Get the enterprise for the current business user
     */
    private function getUserEnterprise()
    {
        $userId = session('user_id');

        $enterprise = null;

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
    private function getBusinessStatusActions(?string $currentStatusName, $statusIds)
    {
        $current = $currentStatusName ?? 'Pending';

        $allowed = [
            'Pending' => ['Confirmed', 'Cancelled'],
            'Confirmed' => ['In Progress', 'Cancelled'],
            'In Progress' => ['Ready for Pickup', 'Delivered'],
            'Ready for Pickup' => [],
            'Delivered' => [],
            'Shipped' => ['Delivered'],
            'Completed' => [],
            'Cancelled' => [],
        ];

        $next = $allowed[$current] ?? [];

        // Map to id list
        $actions = [];
        foreach ($next as $statusName) {
            if (isset($statusIds[$statusName])) {
                $actions[] = [
                    'name' => $statusName,
                    'id' => $statusIds[$statusName],
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

        return view('business.dashboard', compact('stats', 'recent_orders', 'enterprise', 'userName'));
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
    
    public function orders()
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $latestStatusTimes = DB::table('order_status_history')
            ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
            ->groupBy('purchase_order_id');

        $orders = DB::table('customer_orders')
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
            ->orderBy('customer_orders.created_at', 'desc')
            ->paginate(20);

        return view('business.orders.index', compact('orders', 'enterprise', 'userName'));
    }

    public function orderDetails($id)
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $order = DB::table('customer_orders')
            ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
            ->where('customer_orders.purchase_order_id', $id)
            ->where('customer_orders.enterprise_id', $enterprise->enterprise_id)
            ->select('customer_orders.*', 'users.name as customer_name', 'users.email as customer_email')
            ->first();

        if (!$order) {
            abort(404);
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
        $businessActions = $this->getBusinessStatusActions($currentStatusName, $statusIds);

        return view('business.orders.details', compact('order', 'orderItems', 'statusHistory', 'designFiles', 'statuses', 'statusIds', 'currentStatusName', 'enterprise', 'userName', 'businessActions'));
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

        $statusLookup = DB::table('statuses')->pluck('status_name', 'status_id');
        $newStatusName = $statusLookup[$request->status_id] ?? null;

        if (! $newStatusName) {
            return redirect()->back()->with('error', 'Selected status is not configured.');
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
            'In Progress' => ['Ready for Pickup', 'Delivered'],
            'Ready for Pickup' => [],
            'Delivered' => [],
            'Shipped' => ['Delivered'],
            'Completed' => [],
            'Cancelled' => [],
        ];

        $allowedNext = $allowedTransitions[$currentStatusName] ?? [];

        if (! in_array($newStatusName, $allowedNext, true)) {
            return redirect()->back()->with('error', "Cannot move order from {$currentStatusName} to {$newStatusName}.");
        }

        // Create status history entry
        DB::table('order_status_history')->insert([
            'approval_id' => Str::uuid(),
            'purchase_order_id' => $id,
            'user_id' => $userId,
            'status_id' => $request->status_id,
            'remarks' => $request->remarks ?? 'Status updated by business',
            'timestamp' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->where('enterprise_id', $enterprise->enterprise_id)
            ->update([
                'status_id' => $request->status_id,
                'updated_at' => now(),
            ]);

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

    public function createService()
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        return view('business.services.create', compact('enterprise', 'userName'));
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

        $insert = [
            'service_id' => $serviceId,
            'enterprise_id' => $enterprise->enterprise_id,
            'service_name' => $request->service_name,
            'description' => $request->description,
            'image_path' => $imagePath,
            'fulfillment_type' => $request->fulfillment_type,
            'allowed_payment_methods' => $allowedPaymentMethodsJson,
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

        DB::table('services')->insert($insert);

        // Log audit
        $this->logAudit('create', 'service', $serviceId, "Created service: {$request->service_name}", null, $request->all());

        return redirect()->route('business.services.index')->with('success', 'Service created successfully');
    }

    public function editService($id)
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

        return view('business.services.edit', compact('service', 'enterprise', 'userName'));
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

        $update = [
            'service_name' => $request->service_name,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'is_active' => $request->has('is_active'),
            'fulfillment_type' => $request->fulfillment_type,
            'allowed_payment_methods' => $allowedPaymentMethodsJson,
            'file_upload_enabled' => $request->has('file_upload_enabled'),
            'requires_file_upload' => $request->has('requires_file_upload'),
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

    public function createPricingRule()
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        return view('business.pricing.create', compact('enterprise', 'userName'));
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

        return redirect()->route('business.pricing.index')->with('success', 'Pricing rule created successfully');
    }

    public function editPricingRule($id)
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

        return view('business.pricing.edit', compact('rule', 'enterprise', 'userName'));
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
