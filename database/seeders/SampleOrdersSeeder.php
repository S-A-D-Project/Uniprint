<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SampleOrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Get sample data
        $customers = DB::table('users')
            ->join('roles', 'users.user_id', '=', 'roles.user_id')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('role_types.user_role_type', 'customer')
            ->select('users.user_id')
            ->get();

        $enterprises = DB::table('enterprises')->get();
        $products = DB::table('services')->get();
        $statusPending = DB::table('statuses')->where('status_name', 'Pending')->first();
        $statusConfirmed = DB::table('statuses')->where('status_name', 'Confirmed')->first();
        $statusInProgress = DB::table('statuses')->where('status_name', 'In Progress')->first();
        $statusDelivered = DB::table('statuses')->where('status_name', 'Delivered')->first();

        if ($customers->isEmpty() || $enterprises->isEmpty() || $products->isEmpty()) {
            $this->command->info('Skipping order seeder - missing required data');
            return;
        }

        $customer = $customers->first();

        // Create 5 sample orders
        for ($i = 1; $i <= 5; $i++) {
            $enterprise = $enterprises->random();
            $orderId = Str::uuid();
            $orderNo = 'ORD-' . date('Y') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT);
            
            // Determine status based on order age
            $status = match($i) {
                1 => $statusDelivered,
                2 => $statusInProgress,
                3, 4 => $statusConfirmed,
                default => $statusPending,
            };

            $createdAt = now()->subDays(10 - $i * 2);
            
            // Get 1-3 random products from this enterprise
            $enterpriseProducts = DB::table('services')
                ->where('enterprise_id', $enterprise->enterprise_id)
                ->inRandomOrder()
                ->limit(rand(1, 3))
                ->get();

            if ($enterpriseProducts->isEmpty()) {
                continue;
            }

            $subtotal = 0;
            $orderItems = [];

            foreach ($enterpriseProducts as $product) {
                $quantity = rand(1, 5);
                $unitPrice = $product->base_price;
                
                // Get customization options for this product
                $customizations = DB::table('customization_options')
                    ->where('service_id', $product->service_id)
                    ->inRandomOrder()
                    ->limit(rand(0, 2))
                    ->get();

                // Calculate price with customizations
                $customizationTotal = 0;
                foreach ($customizations as $custom) {
                    $customizationTotal += $custom->price_modifier;
                }

                $itemTotal = ($unitPrice + $customizationTotal) * $quantity;
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'item_id' => Str::uuid(),
                    'purchase_order_id' => $orderId,
                    'service_id' => $product->service_id,
                    'item_description' => $product->service_name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice + $customizationTotal,
                    'total_cost' => $itemTotal,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'customizations' => $customizations,
                ];
            }

            $shippingFee = rand(0, 1) ? 100 : 0;
            $discount = 0;
            $total = $subtotal + $shippingFee - $discount;

            // Create order
            DB::table('customer_orders')->insert([
                'purchase_order_id' => $orderId,
                'customer_id' => $customer->user_id,
                'enterprise_id' => $enterprise->enterprise_id,
                'order_no' => $orderNo,
                'purpose' => 'Sample order for ' . $enterprise->name,
                'date_requested' => $createdAt,
                'delivery_date' => $createdAt->copy()->addDays(rand(3, 7)),
                'shipping_fee' => $shippingFee,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'total' => $total,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Create order items and customizations
            foreach ($orderItems as $item) {
                $customizations = $item['customizations'];
                unset($item['customizations']);
                
                DB::table('order_items')->insert($item);

                // Add customizations
                foreach ($customizations as $custom) {
                    DB::table('order_item_customizations')->insert([
                        'id' => Str::uuid(),
                        'order_item_id' => $item['item_id'],
                        'option_id' => $custom->option_id,
                        'price_snapshot' => $custom->price_modifier,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }

            // Create status history
            DB::table('order_status_history')->insert([
                'approval_id' => Str::uuid(),
                'purchase_order_id' => $orderId,
                'user_id' => $customer->user_id,
                'status_id' => $status->status_id,
                'remarks' => 'Order ' . $status->status_name,
                'timestamp' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // If delivered, create transaction
            if ($status->status_id === $statusDelivered->status_id) {
                DB::table('transactions')->insert([
                    'transaction_id' => Str::uuid(),
                    'purchase_order_id' => $orderId,
                    'payment_method' => ['Cash', 'GCash', 'Bank Transfer'][rand(0, 2)],
                    'transaction_ref' => 'TXN-' . strtoupper(Str::random(10)),
                    'amount' => $total,
                    'transaction_date' => $createdAt->copy()->addDays(rand(1, 3)),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $this->command->info("Created order: {$orderNo}");
        }
    }
}
