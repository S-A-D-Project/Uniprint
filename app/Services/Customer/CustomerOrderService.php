<?php

namespace App\Services\Customer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * Customer Order Service
 * Handles order creation, management, and tracking for customers
 */
class CustomerOrderService
{
    /**
     * Get paginated orders with filtering
     *
     * @param string $userId
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getOrders(string $userId, array $filters = [], int $perPage = 10)
    {
        try {
            $query = DB::table('customer_orders')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->leftJoin(DB::raw('(
                    SELECT purchase_order_id, status_id,
                           ROW_NUMBER() OVER (PARTITION BY purchase_order_id ORDER BY timestamp DESC) as rn
                    FROM order_status_history
                ) as latest_status'), function($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'latest_status.purchase_order_id')
                         ->where('latest_status.rn', '=', 1);
                })
                ->leftJoin('statuses', 'latest_status.status_id', '=', 'statuses.status_id')
                ->where('customer_orders.customer_id', $userId)
                ->select(
                    'customer_orders.*',
                    'enterprises.name as enterprise_name',
                    'enterprises.category',
                    'statuses.status_name',
                    'statuses.description as status_description'
                );

            // Apply filters
            if (!empty($filters['status'])) {
                $query->where('statuses.status_name', $filters['status']);
            }

            if (!empty($filters['enterprise_id'])) {
                $query->where('customer_orders.enterprise_id', $filters['enterprise_id']);
            }

            if (!empty($filters['date_from'])) {
                $query->where('customer_orders.date_requested', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->where('customer_orders.date_requested', '<=', $filters['date_to']);
            }

            if (!empty($filters['search'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('customer_orders.order_no', 'LIKE', '%' . $filters['search'] . '%')
                      ->orWhere('customer_orders.purpose', 'LIKE', '%' . $filters['search'] . '%')
                      ->orWhere('enterprises.name', 'LIKE', '%' . $filters['search'] . '%');
                });
            }

            return $query->orderBy('customer_orders.created_at', 'desc')->paginate($perPage);
        } catch (\Exception $e) {
            Log::error('Error getting orders', [
                'user_id' => $userId,
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get detailed order information
     *
     * @param string $userId
     * @param string $orderId
     * @return object|null
     */
    public function getOrderDetails(string $userId, string $orderId)
    {
        try {
            // Get order
            $order = DB::table('customer_orders')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->where('customer_orders.purchase_order_id', $orderId)
                ->where('customer_orders.customer_id', $userId)
                ->select(
                    'customer_orders.*',
                    'enterprises.name as enterprise_name',
                    'enterprises.category',
                    'enterprises.contact_number',
                    'enterprises.email as enterprise_email',
                    'enterprises.address as enterprise_address'
                )
                ->first();

            if (!$order) {
                return null;
            }

            // Get order items
            $order->items = $this->getOrderItems($orderId);

            // Get status history
            $order->status_history = $this->getStatusHistory($orderId);

            // Get current status
            $order->current_status = $this->getCurrentStatus($orderId);

            // Get transactions
            $order->transactions = $this->getTransactions($orderId);

            // Get design files
            $order->design_files = $this->getDesignFiles($orderId);

            return $order;
        } catch (\Exception $e) {
            Log::error('Error getting order details', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create a new order
     *
     * @param string $userId
     * @param array $orderData
     * @return string Order ID
     * @throws ValidationException
     */
    public function createOrder(string $userId, array $orderData): string
    {
        // Validate input
        $validator = Validator::make($orderData, [
            'enterprise_id' => 'required|uuid|exists:enterprises,enterprise_id',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|uuid|exists:services,service_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.customizations' => 'nullable|array',
            'items.*.notes' => 'nullable|string|max:2000',
            'purpose' => 'required|string|max:255',
            'delivery_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();
        try {
            // Generate order number
            $orderNo = $this->generateOrderNumber();

            // Calculate totals
            $totals = $this->calculateOrderTotals($orderData['items']);

            // Apply discount if provided
            $discount = $orderData['discount'] ?? 0;
            $shippingFee = $orderData['shipping_fee'] ?? 0;
            $total = $totals['subtotal'] - $discount + $shippingFee;

            // Create order
            $orderId = \Illuminate\Support\Str::uuid()->toString();
            DB::table('customer_orders')->insert([
                'purchase_order_id' => $orderId,
                'customer_id' => $userId,
                'enterprise_id' => $orderData['enterprise_id'],
                'purpose' => $orderData['purpose'],
                'order_no' => $orderNo,
                'date_requested' => Carbon::now()->toDateString(),
                'delivery_date' => $orderData['delivery_date'],
                'shipping_fee' => $shippingFee,
                'discount' => $discount,
                'subtotal' => $totals['subtotal'],
                'total' => $total,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Create order items
            foreach ($totals['items'] as $item) {
                $this->createOrderItem($orderId, $item);
            }

            // Create initial status
            $this->createInitialStatus($orderId, $userId);

            // Notify business
            $this->notifyBusiness($orderData['enterprise_id'], $orderId, $orderNo);

            // Log audit
            $this->logAudit($userId, 'order_created', 'customer_orders', $orderId, [
                'order_no' => $orderNo,
                'total' => $total,
            ]);

            DB::commit();

            Log::info('Order created successfully', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'order_no' => $orderNo,
            ]);

            return $orderId;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Cancel an order
     *
     * @param string $userId
     * @param string $orderId
     * @param string $reason
     * @return bool
     */
    public function cancelOrder(string $userId, string $orderId, string $reason = ''): bool
    {
        DB::beginTransaction();
        try {
            // Verify order ownership
            $order = DB::table('customer_orders')
                ->where('purchase_order_id', $orderId)
                ->where('customer_id', $userId)
                ->first();

            if (!$order) {
                throw new \Exception('Order not found or access denied');
            }

            // Check if order can be cancelled
            $currentStatus = $this->getCurrentStatus($orderId);
            if (in_array($currentStatus->status_name, ['Delivered', 'Cancelled'])) {
                throw new \Exception('Order cannot be cancelled in current status');
            }

            // Get cancelled status ID
            $cancelledStatusId = DB::table('statuses')
                ->where('status_name', 'Cancelled')
                ->value('status_id');

            // Add status history
            DB::table('order_status_history')->insert([
                'approval_id' => \Illuminate\Support\Str::uuid()->toString(),
                'purchase_order_id' => $orderId,
                'status_id' => $cancelledStatusId,
                'timestamp' => Carbon::now(),
                'user_id' => $userId,
                'remarks' => $reason ?: 'Cancelled by customer',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Notify business
            $this->notifyBusiness($order->enterprise_id, $orderId, $order->order_no, 'order_cancelled');

            // Log audit
            $this->logAudit($userId, 'order_cancelled', 'customer_orders', $orderId, [
                'reason' => $reason,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling order', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get order items with customizations
     *
     * @param string $orderId
     * @return \Illuminate\Support\Collection
     */
    private function getOrderItems(string $orderId)
    {
        $items = DB::table('order_items')
            ->join('services', 'order_items.service_id', '=', 'services.service_id')
            ->where('order_items.purchase_order_id', $orderId)
            ->select(
                'order_items.*',
                'services.service_name',
                'services.description'
            )
            ->get();

        foreach ($items as $item) {
            $item->customizations = DB::table('order_item_customizations')
                ->join('customization_options', 'order_item_customizations.option_id', '=', 'customization_options.option_id')
                ->where('order_item_customizations.order_item_id', $item->item_id)
                ->select(
                    'customization_options.option_name',
                    'customization_options.option_type',
                    'order_item_customizations.price_snapshot'
                )
                ->get();
        }

        return $items;
    }

    /**
     * Get status history
     *
     * @param string $orderId
     * @return \Illuminate\Support\Collection
     */
    private function getStatusHistory(string $orderId)
    {
        return DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->leftJoin('users', 'order_status_history.user_id', '=', 'users.user_id')
            ->where('order_status_history.purchase_order_id', $orderId)
            ->select(
                'order_status_history.*',
                'statuses.status_name',
                'statuses.description',
                'users.name as updated_by_name'
            )
            ->orderBy('order_status_history.timestamp', 'desc')
            ->get();
    }

    /**
     * Get current status
     *
     * @param string $orderId
     * @return object|null
     */
    private function getCurrentStatus(string $orderId)
    {
        return DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('order_status_history.purchase_order_id', $orderId)
            ->orderBy('order_status_history.timestamp', 'desc')
            ->select('statuses.status_name', 'statuses.description', 'order_status_history.timestamp')
            ->first();
    }

    /**
     * Get transactions
     *
     * @param string $orderId
     * @return \Illuminate\Support\Collection
     */
    private function getTransactions(string $orderId)
    {
        return DB::table('transactions')
            ->where('purchase_order_id', $orderId)
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Get design files
     *
     * @param string $orderId
     * @return \Illuminate\Support\Collection
     */
    private function getDesignFiles(string $orderId)
    {
        return DB::table('order_design_files')
            ->leftJoin('users', 'order_design_files.uploaded_by', '=', 'users.user_id')
            ->where('order_design_files.purchase_order_id', $orderId)
            ->select(
                'order_design_files.*',
                'users.name as uploaded_by_name'
            )
            ->orderBy('order_design_files.created_at', 'desc')
            ->get();
    }

    /**
     * Calculate order totals
     *
     * @param array $items
     * @return array
     */
    private function calculateOrderTotals(array $items): array
    {
        $subtotal = 0;
        $processedItems = [];

        foreach ($items as $item) {
            $service = DB::table('services')
                ->where('service_id', $item['service_id'])
                ->where('is_active', true)
                ->first();

            if (!$service) {
                throw new \Exception("Service {$item['service_id']} not available");
            }

            $itemSubtotal = $service->base_price * $item['quantity'];

            // Calculate customization costs
            if (!empty($item['customizations'])) {
                foreach ($item['customizations'] as $optionId) {
                    $option = DB::table('customization_options')
                        ->where('option_id', $optionId)
                        ->first();
                    
                    if ($option) {
                        $itemSubtotal += $option->price_modifier * $item['quantity'];
                    }
                }
            }

            $subtotal += $itemSubtotal;

            $processedItems[] = [
                'service' => $service,
                'quantity' => $item['quantity'],
                'customizations' => $item['customizations'] ?? [],
                'notes' => $item['notes'] ?? null,
                'subtotal' => $itemSubtotal,
            ];
        }

        return [
            'subtotal' => $subtotal,
            'items' => $processedItems,
        ];
    }

    /**
     * Create order item
     *
     * @param string $orderId
     * @param array $itemData
     * @return void
     */
    private function createOrderItem(string $orderId, array $itemData): void
    {
        $itemId = \Illuminate\Support\Str::uuid()->toString();
        
        DB::table('order_items')->insert([
            'item_id' => $itemId,
            'purchase_order_id' => $orderId,
            'service_id' => $itemData['service']->service_id,
            'quantity' => $itemData['quantity'],
            'price_snapshot' => $itemData['service']->base_price,
            'item_subtotal' => $itemData['subtotal'],
            'notes_to_enterprise' => $itemData['notes'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Add customizations
        foreach ($itemData['customizations'] as $optionId) {
            $option = DB::table('customization_options')
                ->where('option_id', $optionId)
                ->first();
            
            if ($option) {
                DB::table('order_item_customizations')->insert([
                    'order_item_id' => $itemId,
                    'option_id' => $optionId,
                    'price_snapshot' => $option->price_modifier,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }

    /**
     * Create initial order status
     *
     * @param string $orderId
     * @param string $userId
     * @return void
     */
    private function createInitialStatus(string $orderId, string $userId): void
    {
        $pendingStatusId = DB::table('statuses')
            ->where('status_name', 'Pending')
            ->value('status_id');

        DB::table('order_status_history')->insert([
            'approval_id' => \Illuminate\Support\Str::uuid()->toString(),
            'purchase_order_id' => $orderId,
            'status_id' => $pendingStatusId,
            'timestamp' => Carbon::now(),
            'user_id' => $userId,
            'remarks' => 'Order created',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    /**
     * Notify business about order
     *
     * @param string $enterpriseId
     * @param string $orderId
     * @param string $orderNo
     * @param string $type
     * @return void
     */
    private function notifyBusiness(string $enterpriseId, string $orderId, string $orderNo, string $type = 'new_order'): void
    {
        $businessUserId = null;

        if (\Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'owner_user_id')) {
            $businessUserId = DB::table('enterprises')
                ->where('enterprise_id', $enterpriseId)
                ->value('owner_user_id');
        }

        if (! $businessUserId && \Illuminate\Support\Facades\Schema::hasTable('staff')) {
            $businessUserId = DB::table('staff')
                ->where('enterprise_id', $enterpriseId)
                ->whereNotNull('user_id')
                ->value('user_id');
        }

        if ($businessUserId) {
            $messages = [
                'new_order' => "You have received a new order #{$orderNo}",
                'order_cancelled' => "Order #{$orderNo} has been cancelled by the customer",
            ];

            DB::table('order_notifications')->insert([
                'notification_id' => \Illuminate\Support\Str::uuid()->toString(),
                'purchase_order_id' => $orderId,
                'recipient_id' => $businessUserId,
                'notification_type' => $type,
                'title' => ucwords(str_replace('_', ' ', $type)),
                'message' => $messages[$type] ?? "Update on order #{$orderNo}",
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    /**
     * Generate unique order number
     *
     * @return string
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = Carbon::now()->format('Ymd');
        
        $lastOrder = DB::table('customer_orders')
            ->where('order_no', 'LIKE', "{$prefix}{$date}%")
            ->orderBy('order_no', 'desc')
            ->value('order_no');

        if ($lastOrder) {
            $sequence = intval(substr($lastOrder, -4)) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Log audit trail
     *
     * @param string $userId
     * @param string $action
     * @param string $tableName
     * @param string $recordId
     * @param array $changes
     * @return void
     */
    private function logAudit(string $userId, string $action, string $tableName, string $recordId, array $changes = []): void
    {
        try {
            DB::table('audit_logs')->insert([
                'log_id' => \Illuminate\Support\Str::uuid()->toString(),
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $tableName,
                'entity_id' => $recordId,
                'description' => $action,
                'old_values' => null,
                'new_values' => empty($changes) ? null : json_encode($changes),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging audit', ['error' => $e->getMessage()]);
        }
    }
}
