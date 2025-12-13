<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    /**
     * Get the enterprise for the current business user
     */
    private function getUserEnterprise()
    {
        $userId = session('user_id');
        
        // Get the enterprise linked to this business user via staff table
        $enterprise = DB::table('staff')
            ->join('enterprises', 'staff.enterprise_id', '=', 'enterprises.enterprise_id')
            ->where('staff.user_id', $userId)
            ->select('enterprises.*')
            ->first();
        
        if (!$enterprise) {
            abort(404, 'No enterprise found for this user. Please contact an administrator.');
        }
        
        return $enterprise;
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

        $stats = [
            'total_orders' => DB::table('customer_orders')
                ->where('enterprise_id', $enterprise->enterprise_id)->count(),
            'pending_orders' => DB::table('customer_orders')
                ->join('order_status_history', 'customer_orders.purchase_order_id', '=', 'order_status_history.purchase_order_id')
                ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
                ->where('customer_orders.enterprise_id', $enterprise->enterprise_id)
                ->where('statuses.status_name', 'Pending')
                ->distinct()
                ->count('customer_orders.purchase_order_id'),
            'in_progress_orders' => DB::table('customer_orders')
                ->join('order_status_history', 'customer_orders.purchase_order_id', '=', 'order_status_history.purchase_order_id')
                ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
                ->where('customer_orders.enterprise_id', $enterprise->enterprise_id)
                ->where('statuses.status_name', 'In Progress')
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

        $recent_orders = DB::table('customer_orders')
            ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
            ->leftJoin(DB::raw('(SELECT purchase_order_id, status_id, MAX(timestamp) as latest_time FROM order_status_history GROUP BY purchase_order_id, status_id) as latest_status'), 'customer_orders.purchase_order_id', '=', 'latest_status.purchase_order_id')
            ->leftJoin('statuses', 'latest_status.status_id', '=', 'statuses.status_id')
            ->where('customer_orders.enterprise_id', $enterprise->enterprise_id)
            ->select('customer_orders.*', 'users.name as customer_name', 'statuses.status_name')
            ->orderBy('customer_orders.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('business.dashboard', compact('stats', 'recent_orders', 'enterprise', 'userName'));
    }

    // =====================================================
    // ORDER MANAGEMENT
    // =====================================================
    
    public function orders()
    {
        $userName = session('user_name');
        $enterprise = $this->getUserEnterprise();

        $orders = DB::table('customer_orders')
            ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
            ->leftJoin(DB::raw('(SELECT purchase_order_id, status_id, MAX(timestamp) as latest_time FROM order_status_history GROUP BY purchase_order_id, status_id ORDER BY latest_time DESC LIMIT 1) as latest_status'), 'customer_orders.purchase_order_id', '=', 'latest_status.purchase_order_id')
            ->leftJoin('statuses', 'latest_status.status_id', '=', 'statuses.status_id')
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

        // Get order items
        $orderItems = DB::table('order_items')
            ->join('services', 'order_items.service_id', '=', 'services.service_id')
            ->where('order_items.purchase_order_id', $id)
            ->select('order_items.*', 'services.service_name')
            ->get();

        // Get customizations for each item
        foreach ($orderItems as $item) {
            $item->customizations = DB::table('order_item_customizations')
                ->join('customization_options', 'order_item_customizations.option_id', '=', 'customization_options.option_id')
                ->where('order_item_customizations.order_item_id', $item->item_id)
                ->select('customization_options.*', 'order_item_customizations.price_snapshot')
                ->get();
        }

        // Get status history
        $statusHistory = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->join('users', 'order_status_history.user_id', '=', 'users.user_id')
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

        return view('business.orders.details', compact('order', 'orderItems', 'statusHistory', 'designFiles', 'statuses', 'enterprise', 'userName'));
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

        // Get new status name
        $newStatus = DB::table('statuses')->where('status_id', $request->status_id)->first();

        // Send notification to customer
        DB::table('order_notifications')->insert([
            'notification_id' => Str::uuid(),
            'purchase_order_id' => $id,
            'recipient_id' => $order->customer_id,
            'notification_type' => 'status_change',
            'title' => 'Order Status Updated',
            'message' => "Your order #{$order->order_no} has been updated to: {$newStatus->status_name}. " . ($request->remarks ?? ''),
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log audit
        $this->logAudit('update', 'order_status', $id, "Order status changed from {$oldStatus->status_name} to {$newStatus->status_name}", 
            ['status' => $oldStatus->status_name],
            ['status' => $newStatus->status_name, 'remarks' => $request->remarks]
        );

        return redirect()->back()->with('success', 'Order status updated successfully');
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
        $request->validate([
            'service_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $enterprise = $this->getUserEnterprise();
        $serviceId = Str::uuid();

        DB::table('services')->insert([
            'service_id' => $serviceId,
            'enterprise_id' => $enterprise->enterprise_id,
            'service_name' => $request->service_name,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
        $request->validate([
            'service_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $enterprise = $this->getUserEnterprise();

        $oldService = DB::table('services')->where('service_id', $id)->where('enterprise_id', $enterprise->enterprise_id)->first();
        
        if (!$oldService) {
            abort(404);
        }

        DB::table('services')
            ->where('service_id', $id)
            ->update([
                'service_name' => $request->service_name,
                'description' => $request->description,
                'base_price' => $request->base_price,
                'is_active' => $request->has('is_active'),
                'updated_at' => now(),
            ]);

        // Log audit
        $this->logAudit('update', 'service', $id, "Updated service: {$request->service_name}", 
            (array)$oldService, 
            $request->all()
        );

        return redirect()->route('business.services.index')->with('success', 'Service updated successfully');
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

        return view('business.customizations.index', compact('service', 'customizations', 'enterprise', 'userName'));
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

        $oldOption = DB::table('customization_options')->where('option_id', $optionId)->first();

        DB::table('customization_options')
            ->where('option_id', $optionId)
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

        $option = DB::table('customization_options')->where('option_id', $optionId)->first();

        DB::table('customization_options')->where('option_id', $optionId)->delete();

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
            'value' => 'required|numeric',
            'formula' => 'nullable|string',
            'priority' => 'required|integer|min:0',
            'conditions' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $enterprise = $this->getUserEnterprise();
        $ruleId = Str::uuid();

        DB::table('pricing_rules')->insert([
            'rule_id' => $ruleId,
            'enterprise_id' => $enterprise->enterprise_id,
            'rule_name' => $request->rule_name,
            'rule_type' => $request->rule_type,
            'rule_description' => $request->rule_description,
            'conditions' => $request->conditions ?? '[]',
            'calculation_method' => $request->calculation_method,
            'value' => $request->value,
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
            'value' => 'required|numeric',
            'formula' => 'nullable|string',
            'priority' => 'required|integer|min:0',
            'conditions' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $enterprise = $this->getUserEnterprise();

        $oldRule = DB::table('pricing_rules')->where('rule_id', $id)->where('enterprise_id', $enterprise->enterprise_id)->first();

        if (!$oldRule) {
            abort(404);
        }

        DB::table('pricing_rules')
            ->where('rule_id', $id)
            ->update([
                'rule_name' => $request->rule_name,
                'rule_type' => $request->rule_type,
                'rule_description' => $request->rule_description,
                'conditions' => $request->conditions ?? '[]',
                'calculation_method' => $request->calculation_method,
                'value' => $request->value,
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
        return view('business.chat');
    }
}
