<?php

namespace App\Services;

use App\Models\SavedService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\NotifyBusinessOwnerOfNewOrder;

class OrderProcessingException extends \RuntimeException
{
    public function __construct(string $message, private readonly int $status)
    {
        parent::__construct($message, $status);
    }

    public function status(): int
    {
        return $this->status;
    }
}

class OrderProcessingService
{
    public function __construct(
        private readonly PricingEngine $pricingEngine
    ) {
    }

    /**
     * @param array{
     *  payment_method:string,
     *  fulfillment_method:string,
     *  requested_fulfillment_date?:string|null,
     *  notes?:string|null,
     *  contact_name:string,
     *  contact_phone:string,
     *  contact_email:string,
     *  rush_option:string,
     *  direct:bool,
     *  selected_ids?:array|null,
     *  saved_services:Collection,
     *  temp_design_files?:Collection,
     *  direct_design_files?:array
     * } $payload
     */
    public function process(string $userId, array $payload): array
    {
        $paymentMethod = (string) ($payload['payment_method'] ?? '');
        $fulfillmentMethod = (string) ($payload['fulfillment_method'] ?? '');
        $requestedFulfillmentDate = $payload['requested_fulfillment_date'] ?? null;
        $notes = $payload['notes'] ?? null;
        $contactName = (string) ($payload['contact_name'] ?? '');
        $contactPhone = (string) ($payload['contact_phone'] ?? '');
        $contactEmail = (string) ($payload['contact_email'] ?? '');
        $rushOption = (string) ($payload['rush_option'] ?? 'standard');

        $direct = (bool) ($payload['direct'] ?? false);
        $selectedIds = $payload['selected_ids'] ?? null;

        /** @var Collection $savedServices */
        $savedServices = $payload['saved_services'] ?? collect();
        if (!($savedServices instanceof Collection)) {
            $savedServices = collect($savedServices);
        }

        /** @var array $directDesignFiles */
        $directDesignFiles = $payload['direct_design_files'] ?? [];

        /** @var Collection $tempDesignFiles */
        $tempDesignFiles = $payload['temp_design_files'] ?? collect();
        if (!($tempDesignFiles instanceof Collection)) {
            $tempDesignFiles = collect($tempDesignFiles);
        }

        if ($savedServices->isEmpty()) {
            return ['success' => false, 'status' => 400, 'message' => 'No saved services found'];
        }

        // We'll calculate rush fee per enterprise below
        // $rushFee = $this->pricingEngine->calculateRushFee($rushOption); 

        DB::beginTransaction();

        try {
            $serviceIds = $savedServices->pluck('service_id')->filter()->unique()->values();
            $servicesById = collect();
            if ($serviceIds->isNotEmpty()) {
                $servicesById = DB::table('services')
                    ->whereIn('service_id', $serviceIds)
                    ->get()
                    ->keyBy('service_id');
            }

            $ordersByEnterprise = [];
            foreach ($savedServices as $savedService) {
                $serviceData = $servicesById->get($savedService->service_id);
                if (!$serviceData) {
                    continue;
                }

                $ordersByEnterprise[$serviceData->enterprise_id] ??= [];
                $ordersByEnterprise[$serviceData->enterprise_id][] = $savedService;
            }

            $pendingStatus = DB::table('statuses')->where('status_name', 'Pending')->first();
            $createdOrders = [];

            foreach ($ordersByEnterprise as $enterpriseId => $items) {
                $orderId = (string) Str::uuid();
                $orderNo = 'ORD-' . date('Y') . '-' . strtoupper(Str::random(8));

                $subtotal = 0.0;
                $downpaymentRequiredPercent = 0.0;
                $orderItems = [];

                // Calculate enterprise-specific rush fee
                $enterpriseRushFee = $this->pricingEngine->calculateRushFee($rushOption, $enterpriseId);

                $savedServiceIds = [];
                foreach ($items as $it) {
                    if (!empty($it->saved_service_id)) {
                        $savedServiceIds[] = $it->saved_service_id;
                    }
                }

                $enterpriseTempDesignFiles = collect();
                if (!$direct && Schema::hasTable('saved_service_design_files') && !empty($savedServiceIds)) {
                    $enterpriseTempDesignFiles = $tempDesignFiles->whereIn('saved_service_id', $savedServiceIds);
                }

                $allCustomizationIds = collect($items)
                    ->flatMap(function ($savedService) {
                        $raw = $savedService->customizations ?? null;
                        $ids = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);
                        return is_array($ids) ? $ids : [];
                    })
                    ->filter()
                    ->unique()
                    ->values();

                $customizationOptionsById = collect();
                if (Schema::hasTable('customization_options') && $allCustomizationIds->isNotEmpty()) {
                    $customizationOptionsById = DB::table('customization_options')
                        ->whereIn('option_id', $allCustomizationIds)
                        ->get()
                        ->keyBy('option_id');
                }

                foreach ($items as $savedService) {
                    $serviceData = $servicesById->get($savedService->service_id);
                    if (!$serviceData) {
                        continue;
                    }

                    // Enforce rush eligibility
                    if ($rushOption !== 'standard' && Schema::hasColumn('services', 'supports_rush') && empty($serviceData?->supports_rush)) {
                        throw new OrderProcessingException('One or more services in your order do not support rush pickup options for enterprise ' . $enterpriseId, 422);
                    }

                    // Downpayment rules
                    if (Schema::hasColumn('services', 'requires_downpayment') && Schema::hasColumn('services', 'downpayment_percent')) {
                        if (!empty($serviceData?->requires_downpayment)) {
                            $dp = (float) ($serviceData?->downpayment_percent ?? 0);
                            $downpaymentRequiredPercent = max($downpaymentRequiredPercent, $dp);

                            if ($paymentMethod === 'cash') {
                                throw new OrderProcessingException('Cash is not available for services that require a downpayment. Please use GCash.', 422);
                            }
                        }
                    }

                    // Fulfillment constraints
                    if ($fulfillmentMethod && Schema::hasColumn('services', 'fulfillment_type') && !empty($serviceData->fulfillment_type)) {
                        if ($serviceData->fulfillment_type === 'pickup' && $fulfillmentMethod !== 'pickup') {
                            throw new OrderProcessingException('One or more services are pickup-only.', 422);
                        }
                        if ($serviceData->fulfillment_type === 'delivery' && $fulfillmentMethod !== 'delivery') {
                            throw new OrderProcessingException('One or more services are delivery-only.', 422);
                        }
                    }

                    // Allowed payment methods per service
                    if ($paymentMethod && Schema::hasColumn('services', 'allowed_payment_methods') && !empty($serviceData->allowed_payment_methods)) {
                        $decoded = json_decode($serviceData->allowed_payment_methods, true);
                        if (is_array($decoded) && !empty($decoded) && !in_array($paymentMethod, $decoded, true)) {
                            throw new OrderProcessingException('Selected payment method is not accepted by one or more services.', 422);
                        }
                    }

                    // File upload requirements
                    if (Schema::hasColumn('services', 'requires_file_upload') && !empty($serviceData?->requires_file_upload)) {
                        if ($direct) {
                            if (count($directDesignFiles) === 0) {
                                throw new OrderProcessingException('This service requires design files. Please upload the required files before checkout.', 422);
                            }
                        } else {
                            if (Schema::hasTable('saved_service_design_files') && $enterpriseTempDesignFiles->where('saved_service_id', $savedService->saved_service_id)->count() === 0) {
                                throw new OrderProcessingException('One or more services require design files. Please upload the required files before checkout.', 422);
                            }
                        }
                    }

                    // Pricing: use PricingEngine to re-calc item totals for consistency
                    $customizationIds = $savedService->customizations;
                    $customizationIds = is_array($customizationIds) ? $customizationIds : (json_decode((string) $customizationIds, true) ?: []);

                    $pricing = $this->pricingEngine->calculatePrice(
                        $savedService->service_id,
                        $customizationIds,
                        (int) $savedService->quantity,
                        $enterpriseId
                    );

                    $itemTotal = (float) ($pricing['total'] ?? 0);
                    $unitPrice = (float) ($pricing['quantity'] ? ($itemTotal / (int) $pricing['quantity']) : 0);

                    $subtotal += $itemTotal;

                    $itemId = (string) Str::uuid();
                    $itemInsert = [
                        'item_id' => $itemId,
                        'purchase_order_id' => $orderId,
                        'service_id' => $savedService->service_id,
                        'item_description' => $serviceData->service_name,
                        'quantity' => (int) $savedService->quantity,
                        'unit_price' => $unitPrice,
                        'total_cost' => $itemTotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (Schema::hasColumn('order_items', 'custom_fields')) {
                        $customFieldsValue = $savedService->custom_fields ?? null;
                        if (is_array($customFieldsValue)) {
                            $customFieldsValue = empty($customFieldsValue) ? null : json_encode($customFieldsValue);
                        }
                        $itemInsert['custom_fields'] = $customFieldsValue;
                    }

                    $orderItems[] = [
                        'item' => $itemInsert,
                        'customization_ids' => $customizationIds,
                    ];
                }

                $pickupDate = $this->calculatePickupDate($rushOption);
                $deliveryDate = $pickupDate ? $pickupDate->toDateString() : now()->toDateString();

                if ($requestedFulfillmentDate) {
                    if ($requestedFulfillmentDate < $deliveryDate) {
                        throw new OrderProcessingException('Your requested date is too soon for the selected timeline. Please choose a later date.', 422);
                    }
                    $deliveryDate = $requestedFulfillmentDate;
                }

                $tax = $this->pricingEngine->calculateTax($subtotal + $enterpriseRushFee);
                $total = $subtotal + $enterpriseRushFee + $tax;

                [$downpaymentRequiredAmount, $downpaymentDueAt] = $this->calculateDownpayment($total, $downpaymentRequiredPercent);

                $orderData = [
                    'purchase_order_id' => $orderId,
                    'customer_id' => $userId,
                    'enterprise_id' => $enterpriseId,
                    'status_id' => $pendingStatus?->status_id,
                    'order_no' => $orderNo,
                    'purpose' => $notes ?? 'Online order via UniPrint',
                    'date_requested' => now()->toDateString(),
                    'delivery_date' => $deliveryDate,
                    'pickup_date' => $pickupDate,
                    'shipping_fee' => 0,
                    'rush_fee' => $enterpriseRushFee,
                    'rush_option' => $rushOption,
                    'discount' => 0,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                    'contact_name' => $contactName,
                    'contact_phone' => $contactPhone,
                    'contact_email' => $contactEmail,
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('customer_orders', 'downpayment_required_percent')) {
                    $orderData['downpayment_required_percent'] = $downpaymentRequiredPercent;
                }
                if (Schema::hasColumn('customer_orders', 'downpayment_required_amount')) {
                    $orderData['downpayment_required_amount'] = $downpaymentRequiredAmount;
                }
                if (Schema::hasColumn('customer_orders', 'downpayment_due_at')) {
                    $orderData['downpayment_due_at'] = $downpaymentDueAt;
                }
                if (Schema::hasColumn('customer_orders', 'fulfillment_method')) {
                    $orderData['fulfillment_method'] = $fulfillmentMethod;
                }
                if (Schema::hasColumn('customer_orders', 'requested_fulfillment_date')) {
                    $orderData['requested_fulfillment_date'] = $requestedFulfillmentDate;
                }

                DB::table('customer_orders')->insert($orderData);

                foreach ($orderItems as $orderItem) {
                    DB::table('order_items')->insert($orderItem['item']);

                    if (Schema::hasTable('order_item_customizations')) {
                        foreach ((array) $orderItem['customization_ids'] as $optionId) {
                            $opt = $customizationOptionsById->get($optionId);
                            if (!$opt) {
                                continue;
                            }

                            DB::table('order_item_customizations')->insert([
                                'id' => (string) Str::uuid(),
                                'order_item_id' => $orderItem['item']['item_id'],
                                'option_id' => $optionId,
                                'price_snapshot' => $opt->price_modifier,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }

                DB::table('order_status_history')->insert([
                    'approval_id' => (string) Str::uuid(),
                    'purchase_order_id' => $orderId,
                    'user_id' => $userId,
                    'status_id' => $pendingStatus?->status_id,
                    'remarks' => 'Order placed via checkout',
                    'timestamp' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (Schema::hasTable('payments')) {
                    try {
                        DB::table('payments')->insert([
                            'payment_id' => (string) Str::uuid(),
                            'purchase_order_id' => $orderId,
                            'payment_method' => $paymentMethod,
                            'amount_paid' => 0,
                            'amount_due' => $total,
                            'payment_date_time' => now(),
                            'is_verified' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Failed to insert payment record; aborting checkout', [
                            'purchase_order_id' => $orderId,
                            'error' => $e->getMessage(),
                        ]);

                        throw new OrderProcessingException('Failed to initialize payment record. Please try again.', 500);
                    }
                }

                $this->attachDesignFiles($orderId, $userId, $direct, $directDesignFiles, $enterpriseTempDesignFiles, $savedServiceIds);

                $createdOrders[] = [
                    'order_no' => $orderNo,
                    'total' => $total,
                    'payment_method' => $paymentMethod,
                    'purchase_order_id' => $orderId,
                ];
            }

            DB::commit();

            foreach ($createdOrders as $co) {
                if (!empty($co['purchase_order_id'])) {
                    NotifyBusinessOwnerOfNewOrder::dispatch((string) $co['purchase_order_id']);
                }
            }

            if ($direct) {
                // Controller clears session
            } else {
                // Clear saved services
                if (is_array($selectedIds) && !empty($selectedIds)) {
                    SavedService::where('user_id', $userId)->whereIn('saved_service_id', $selectedIds)->delete();
                } else {
                    SavedService::clearServices($userId);
                }
            }

            return [
                'success' => true,
                'status' => 200,
                'message' => 'Order placed successfully!',
                'orders' => array_values(array_map(function ($o) {
                    unset($o['purchase_order_id']);
                    return $o;
                }, $createdOrders)),
            ];
        } catch (OrderProcessingException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'status' => $e->status(),
                'message' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('OrderProcessingService failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Failed to place order: ' . $e->getMessage(),
            ];
        }
    }

    private function calculatePickupDate(string $rushOption)
    {
        $now = now();

        return match ($rushOption) {
            'same_day' => $now->addHours(3),
            'rush' => $now->addHours(6),
            'express' => $this->nextBusinessDayAtFive($now->addDay()),
            default => $this->plusBusinessDaysAtFive($now->copy(), 2),
        };
    }

    private function nextBusinessDayAtFive($dt)
    {
        $dt->setTime(17, 0, 0);
        while ($dt->isWeekend()) {
            $dt->addDay();
        }
        return $dt;
    }

    private function plusBusinessDaysAtFive($dt, int $days)
    {
        $businessDays = 0;
        while ($businessDays < $days) {
            $dt->addDay();
            if (!$dt->isWeekend()) {
                $businessDays++;
            }
        }
        $dt->setTime(17, 0, 0);
        return $dt;
    }

    private function calculateDownpayment(float $total, float $downpaymentRequiredPercent): array
    {
        $downpaymentRequiredAmount = null;
        $downpaymentDueAt = null;

        if (
            Schema::hasColumn('customer_orders', 'downpayment_required_percent')
            && Schema::hasColumn('customer_orders', 'downpayment_required_amount')
            && Schema::hasColumn('customer_orders', 'downpayment_due_at')
            && $downpaymentRequiredPercent > 0
        ) {
            $downpaymentRequiredAmount = $total * ($downpaymentRequiredPercent / 100);

            $dueHours = 24;
            if (Schema::hasTable('system_settings')) {
                $raw = DB::table('system_settings')->where('key', 'order_downpayment_due_hours')->value('value');
                if (is_numeric($raw)) {
                    $dueHours = (int) $raw;
                }
            }
            if ($dueHours < 1) {
                $dueHours = 24;
            }

            $downpaymentDueAt = now()->addHours($dueHours);
        }

        return [$downpaymentRequiredAmount, $downpaymentDueAt];
    }

    private function attachDesignFiles(string $orderId, string $userId, bool $direct, array $directDesignFiles, $enterpriseTempDesignFiles, array $savedServiceIds): void
    {
        $disk = config('filesystems.default', 'public');

        if (!Schema::hasTable('order_design_files')) {
            return;
        }

        if ($direct) {
            $version = DB::table('order_design_files')->where('purchase_order_id', $orderId)->count();
            foreach ($directDesignFiles as $tmp) {
                $version++;
                $targetName = $version . '_' . basename((string) ($tmp['file_name'] ?? 'file'));
                $targetPath = 'design_files/' . $orderId . '/' . $targetName;

                $srcPath = $tmp['file_path'] ?? null;
                if ($srcPath) {
                    try {
                        if (Storage::disk($disk)->exists($srcPath)) {
                            Storage::disk($disk)->makeDirectory('design_files/' . $orderId);
                            Storage::disk($disk)->move($srcPath, $targetPath);
                        } elseif (Storage::disk('public')->exists($srcPath)) {
                            Storage::disk('public')->makeDirectory('design_files/' . $orderId);
                            Storage::disk('public')->move($srcPath, $targetPath);
                        }
                    } catch (\Throwable $e) {
                    }
                }

                DB::table('order_design_files')->insert([
                    'file_id' => (string) Str::uuid(),
                    'purchase_order_id' => $orderId,
                    'uploaded_by' => $userId,
                    'file_name' => $targetName,
                    'file_path' => $targetPath,
                    'file_type' => $tmp['file_type'] ?? null,
                    'file_size' => $tmp['file_size'] ?? null,
                    'design_notes' => $tmp['design_notes'] ?? null,
                    'version' => $version,
                    'is_approved' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            return;
        }

        if (!Schema::hasTable('saved_service_design_files') || empty($savedServiceIds)) {
            return;
        }

        $version = DB::table('order_design_files')->where('purchase_order_id', $orderId)->count();
        foreach ($enterpriseTempDesignFiles as $tmp) {
            $version++;
            $targetName = $version . '_' . basename((string) ($tmp->file_name ?? 'file'));
            $targetPath = 'design_files/' . $orderId . '/' . $targetName;

            if (!empty($tmp->file_path)) {
                try {
                    if (Storage::disk($disk)->exists($tmp->file_path)) {
                        Storage::disk($disk)->makeDirectory('design_files/' . $orderId);
                        Storage::disk($disk)->move($tmp->file_path, $targetPath);
                    } elseif (Storage::disk('public')->exists($tmp->file_path)) {
                        Storage::disk('public')->makeDirectory('design_files/' . $orderId);
                        Storage::disk('public')->move($tmp->file_path, $targetPath);
                    }
                } catch (\Throwable $e) {
                }
            }

            DB::table('order_design_files')->insert([
                'file_id' => (string) Str::uuid(),
                'purchase_order_id' => $orderId,
                'uploaded_by' => $userId,
                'file_name' => $targetName,
                'file_path' => $targetPath,
                'file_type' => $tmp->file_type ?? null,
                'file_size' => $tmp->file_size ?? null,
                'design_notes' => $tmp->design_notes ?? null,
                'version' => $version,
                'is_approved' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('saved_service_design_files')
            ->where('user_id', $userId)
            ->whereIn('saved_service_id', $savedServiceIds)
            ->delete();
    }
}
