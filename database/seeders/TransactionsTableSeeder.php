<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\CustomerOrder;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing transactions
        Transaction::query()->delete();

        $orders = CustomerOrder::all();

        // Create transactions for completed and shipped orders
        $completedOrders = $orders->whereIn('current_status', ['Complete', 'Shipped']);

        foreach ($completedOrders as $order) {
            Transaction::create([
                'order_id' => $order->order_id,
                'payment_method' => $this->getRandomPaymentMethod(),
                'payment_reference_id' => 'TXN-' . strtoupper(uniqid()),
                'payment_date_time' => $order->order_creation_date->addHours(2),
                'amount_paid' => $order->total_order_amount,
                'is_verified' => true,
            ]);
        }

        // Create a pending transaction
        $pendingOrder = $orders->where('current_status', 'In Progress')->first();
        if ($pendingOrder) {
            Transaction::create([
                'order_id' => $pendingOrder->order_id,
                'payment_method' => 'Credit Card',
                'payment_reference_id' => 'TXN-' . strtoupper(uniqid()),
                'payment_date_time' => $pendingOrder->order_creation_date->addHours(1),
                'amount_paid' => $pendingOrder->total_order_amount,
                'is_verified' => false,
            ]);
        }
    }

    private function getRandomPaymentMethod()
    {
        $methods = ['Credit Card', 'PayPal', 'Bank Transfer', 'Cash on Delivery'];
        return $methods[array_rand($methods)];
    }
}
