<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PurchaseOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $requestor = DB::table('users')->where('email', 'requestor@company.com')->first();
        $supplier = DB::table('suppliers')->where('name', 'ABC Office Supplies Inc.')->first();
        $statusPending = DB::table('statuses')->where('status_name', 'Pending')->first();
        $preparedBy = DB::table('users')->where('email', 'requestor@company.com')->first();

        // Purchase Order 1
        $poId1 = Str::uuid();
        DB::table('purchase_orders')->insert([
            'purchase_order_id' => $poId1,
            'requestor_id' => $requestor->user_id,
            'supplier_id' => $supplier->supplier_id,
            'purpose' => 'Office Supplies for Q4 2024',
            'purchase_order_no' => 'PO-2024-001',
            'official_receipt_no' => null,
            'date_requested' => Carbon::now()->subDays(5),
            'delivery_date' => Carbon::now()->addDays(10),
            'shipping_fee' => 500.00,
            'discount' => 100.00,
            'subtotal' => 15000.00,
            'total' => 15400.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Items for PO 1
        DB::table('items')->insert([
            [
                'item_id' => Str::uuid(),
                'purchase_order_id' => $poId1,
                'item_description' => 'Bond Paper A4 (500 sheets/ream) - 20 reams',
                'quantity' => 20,
                'unit_price' => 250.00,
                'total_cost' => 5000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_id' => Str::uuid(),
                'purchase_order_id' => $poId1,
                'item_description' => 'Ballpoint Pens (Blue) - 100 boxes',
                'quantity' => 100,
                'unit_price' => 50.00,
                'total_cost' => 5000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_id' => Str::uuid(),
                'purchase_order_id' => $poId1,
                'item_description' => 'Stapler Heavy Duty - 50 pieces',
                'quantity' => 50,
                'unit_price' => 100.00,
                'total_cost' => 5000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Approval for PO 1
        DB::table('approvals')->insert([
            'approval_id' => Str::uuid(),
            'purchase_order_id' => $poId1,
            'prepared_by_id' => $preparedBy->user_id,
            'prepared_at' => Carbon::now()->subDays(5),
            'verified_by_id' => null,
            'verified_at' => null,
            'approved_by_id' => null,
            'approved_at' => null,
            'received_by_id' => null,
            'received_at' => null,
            'status_id' => $statusPending->status_id,
            'remarks' => 'Awaiting finance verification',
        ]);

        // Purchase Order 2 (Approved)
        $supplier2 = DB::table('suppliers')->where('name', 'Tech Solutions Corp.')->first();
        $statusApproved = DB::table('statuses')->where('status_name', 'Approved')->first();
        $verifiedBy = DB::table('users')->where('email', 'finance@company.com')->first();
        $approvedBy = DB::table('users')->where('email', 'depthead@company.com')->first();

        $poId2 = Str::uuid();
        DB::table('purchase_orders')->insert([
            'purchase_order_id' => $poId2,
            'requestor_id' => $requestor->user_id,
            'supplier_id' => $supplier2->supplier_id,
            'purpose' => 'Computer Equipment for IT Department',
            'purchase_order_no' => 'PO-2024-002',
            'official_receipt_no' => 'OR-2024-456',
            'date_requested' => Carbon::now()->subDays(15),
            'delivery_date' => Carbon::now()->addDays(5),
            'shipping_fee' => 1000.00,
            'discount' => 2000.00,
            'subtotal' => 80000.00,
            'total' => 79000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Items for PO 2
        DB::table('items')->insert([
            [
                'item_id' => Str::uuid(),
                'purchase_order_id' => $poId2,
                'item_description' => 'Desktop Computer (Core i5, 8GB RAM, 512GB SSD) - 5 units',
                'quantity' => 5,
                'unit_price' => 35000.00,
                'total_cost' => 35000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_id' => Str::uuid(),
                'purchase_order_id' => $poId2,
                'item_description' => 'Monitor 24-inch LED - 5 units',
                'quantity' => 5,
                'unit_price' => 8000.00,
                'total_cost' => 40000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_id' => Str::uuid(),
                'purchase_order_id' => $poId2,
                'item_description' => 'Wireless Keyboard and Mouse Set - 5 sets',
                'quantity' => 5,
                'unit_price' => 1000.00,
                'total_cost' => 5000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Approval for PO 2
        DB::table('approvals')->insert([
            'approval_id' => Str::uuid(),
            'purchase_order_id' => $poId2,
            'prepared_by_id' => $preparedBy->user_id,
            'prepared_at' => Carbon::now()->subDays(15),
            'verified_by_id' => $verifiedBy->user_id,
            'verified_at' => Carbon::now()->subDays(12),
            'approved_by_id' => $approvedBy->user_id,
            'approved_at' => Carbon::now()->subDays(10),
            'received_by_id' => null,
            'received_at' => null,
            'status_id' => $statusApproved->status_id,
            'remarks' => 'Approved for delivery',
        ]);
    }
}
