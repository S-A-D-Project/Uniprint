<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Enterprise;
use App\Models\Service;
use App\Models\CustomerOrder;
use App\Models\OrderItem;
use App\Models\OrderItemCustomization;
use App\Models\OrderStatusHistory;
use App\Models\CustomizationOption;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrdersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing orders
        OrderItemCustomization::query()->delete();
        OrderStatusHistory::query()->delete();
        OrderItem::query()->delete();
        CustomerOrder::query()->delete();

        $customers = User::where('role_type', 'customer')->get();
        $enterprises = Enterprise::all();
        $services = Service::all();

        // Order 1: Business Cards
        $order1 = CustomerOrder::create([
            'customer_account_id' => $customers[0]->user_id,
            'enterprise_id' => $enterprises[0]->enterprise_id,
            'order_creation_date' => Carbon::now()->subDays(5),
            'total_order_amount' => 38.00,
            'current_status' => 'Complete',
        ]);

        $orderItem1 = OrderItem::create([
            'order_id' => $order1->order_id,
            'service_id' => $services->where('service_name', 'Business Cards')->first()->service_id,
            'quantity' => 1,
            'item_subtotal' => 38.00,
            'notes_to_enterprise' => 'Please use high-quality ink',
        ]);

        // Add customizations
        $premiumPaper = CustomizationOption::whereHas('customizationGroup', function($q) {
            $q->where('group_name', 'Paper Type');
        })->where('option_name', 'Premium (16pt)')->first();

        if ($premiumPaper) {
            OrderItemCustomization::create([
                'order_item_id' => $orderItem1->item_id,
                'option_id' => $premiumPaper->option_id,
                'option_price_snapshot' => 5.00,
            ]);
        }

        // Add status history
        OrderStatusHistory::create([
            'order_id' => $order1->order_id,
            'status_name' => 'Pending',
            'status_timestamp' => Carbon::now()->subDays(5),
            'staff_id' => null,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order1->order_id,
            'status_name' => 'In Progress',
            'status_timestamp' => Carbon::now()->subDays(4),
            'staff_id' => Staff::first()->staff_id,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order1->order_id,
            'status_name' => 'Complete',
            'status_timestamp' => Carbon::now()->subDays(2),
            'staff_id' => Staff::first()->staff_id,
        ]);

        // Order 2: Custom T-Shirts
        $order2 = CustomerOrder::create([
            'customer_account_id' => $customers[1]->user_id,
            'enterprise_id' => $enterprises[1]->enterprise_id,
            'order_creation_date' => Carbon::now()->subDays(3),
            'total_order_amount' => 60.00,
            'current_status' => 'In Progress',
        ]);

        OrderItem::create([
            'order_id' => $order2->order_id,
            'service_id' => $services->where('service_name', 'Custom T-Shirt')->first()->service_id,
            'quantity' => 4,
            'item_subtotal' => 60.00,
            'notes_to_enterprise' => 'Need them by next week',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order2->order_id,
            'status_name' => 'Pending',
            'status_timestamp' => Carbon::now()->subDays(3),
            'staff_id' => null,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order2->order_id,
            'status_name' => 'In Progress',
            'status_timestamp' => Carbon::now()->subDays(2),
            'staff_id' => Staff::skip(1)->first()->staff_id,
        ]);

        // Order 3: Large Format Poster
        $order3 = CustomerOrder::create([
            'customer_account_id' => $customers[2]->user_id,
            'enterprise_id' => $enterprises[2]->enterprise_id,
            'order_creation_date' => Carbon::now()->subDays(7),
            'total_order_amount' => 75.00,
            'current_status' => 'Shipped',
        ]);

        OrderItem::create([
            'order_id' => $order3->order_id,
            'service_id' => $services->where('service_name', 'Large Format Poster')->first()->service_id,
            'quantity' => 1,
            'item_subtotal' => 75.00,
            'notes_to_enterprise' => 'High resolution required',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order3->order_id,
            'status_name' => 'Pending',
            'status_timestamp' => Carbon::now()->subDays(7),
            'staff_id' => null,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order3->order_id,
            'status_name' => 'In Progress',
            'status_timestamp' => Carbon::now()->subDays(6),
            'staff_id' => Staff::skip(2)->first()->staff_id,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order3->order_id,
            'status_name' => 'Shipped',
            'status_timestamp' => Carbon::now()->subDays(1),
            'staff_id' => Staff::skip(2)->first()->staff_id,
        ]);

        // Order 4: Vinyl Banner
        $order4 = CustomerOrder::create([
            'customer_account_id' => $customers[3]->user_id,
            'enterprise_id' => $enterprises[4]->enterprise_id,
            'order_creation_date' => Carbon::now()->subDays(1),
            'total_order_amount' => 55.00,
            'current_status' => 'Pending',
        ]);

        OrderItem::create([
            'order_id' => $order4->order_id,
            'service_id' => $services->where('service_name', 'Vinyl Banner')->first()->service_id,
            'quantity' => 1,
            'item_subtotal' => 55.00,
            'notes_to_enterprise' => 'For outdoor use',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order4->order_id,
            'status_name' => 'Pending',
            'status_timestamp' => Carbon::now()->subDays(1),
            'staff_id' => null,
        ]);

        // Order 5: Hoodies
        $order5 = CustomerOrder::create([
            'customer_account_id' => $customers[4]->user_id,
            'enterprise_id' => $enterprises[1]->enterprise_id,
            'order_creation_date' => Carbon::now()->subHours(12),
            'total_order_amount' => 105.00,
            'current_status' => 'Pending',
        ]);

        OrderItem::create([
            'order_id' => $order5->order_id,
            'service_id' => $services->where('service_name', 'Hoodies')->first()->service_id,
            'quantity' => 3,
            'item_subtotal' => 105.00,
            'notes_to_enterprise' => 'Custom logo on front',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order5->order_id,
            'status_name' => 'Pending',
            'status_timestamp' => Carbon::now()->subHours(12),
            'staff_id' => null,
        ]);

        // Order 6: Brochures
        $order6 = CustomerOrder::create([
            'customer_account_id' => $customers[5]->user_id,
            'enterprise_id' => $enterprises[0]->enterprise_id,
            'order_creation_date' => Carbon::now()->subDays(10),
            'total_order_amount' => 50.00,
            'current_status' => 'Complete',
        ]);

        OrderItem::create([
            'order_id' => $order6->order_id,
            'service_id' => $services->where('service_name', 'Brochures')->first()->service_id,
            'quantity' => 1,
            'item_subtotal' => 50.00,
            'notes_to_enterprise' => 'Tri-fold design',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order6->order_id,
            'status_name' => 'Pending',
            'status_timestamp' => Carbon::now()->subDays(10),
            'staff_id' => null,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order6->order_id,
            'status_name' => 'Complete',
            'status_timestamp' => Carbon::now()->subDays(8),
            'staff_id' => Staff::first()->staff_id,
        ]);
    }
}
