<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StatusesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'status_id' => '0473cb60-8297-4e58-a79c-e253fa02cec7',
                'status_name' => 'Pending',
                'description' => 'Order has been placed and is awaiting confirmation'
            ],
            [
                'status_id' => '098968d5-7cc1-4bcc-ab8d-6cee860a3c97',
                'status_name' => 'Confirmed',
                'description' => 'Order has been confirmed by the enterprise'
            ],
            [
                'status_id' => '38232810-0379-4453-b386-01c5c0a7f62e',
                'status_name' => 'In Progress',
                'description' => 'Order is being prepared/printed'
            ],
            [
                'status_id' => 'ff93c00e-7a97-4e8c-89c0-8891d39f6772',
                'status_name' => 'Ready for Pickup',
                'description' => 'Order is ready for customer pickup'
            ],
            [
                'status_id' => 'c4c3aebf-67b3-4fcf-9779-8eac84b7c7bd',
                'status_name' => 'Shipped',
                'description' => 'Order has been shipped for delivery'
            ],
            [
                'status_id' => '97d9426a-3db4-4fa3-bd25-c0f56a55c340',
                'status_name' => 'Delivered',
                'description' => 'Order has been delivered to customer'
            ],
            [
                'status_id' => '9c42d52c-43a6-4d1e-92ec-3c9c9c8a8a43',
                'status_name' => 'Completed',
                'description' => 'Order has been received/confirmed by the customer'
            ],
            [
                'status_id' => '33f26616-fa21-4a68-a746-92d479e4336a',
                'status_name' => 'Cancelled',
                'description' => 'Order has been cancelled'
            ],
        ];

        // Insert or update statuses to avoid duplicate key errors
        foreach ($statuses as $status) {
            DB::table('statuses')->updateOrInsert(
                ['status_name' => $status['status_name']],
                [
                    'status_id' => $status['status_id'],
                    'description' => $status['description']
                ]
            );
        }
    }
}
