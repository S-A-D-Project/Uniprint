<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuppliersSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'supplier_id' => Str::uuid(),
                'name' => 'ABC Office Supplies Inc.',
                'address' => '123 Business Street, Metro Manila, Philippines',
                'vat_type' => 'VAT',
                'contact_person' => 'Maria Santos',
                'contact_number' => '+63-2-1234-5678',
                'tin_no' => '123-456-789-000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_id' => Str::uuid(),
                'name' => 'Tech Solutions Corp.',
                'address' => '456 Technology Avenue, Makati City, Philippines',
                'vat_type' => 'VAT',
                'contact_person' => 'Juan dela Cruz',
                'contact_number' => '+63-2-9876-5432',
                'tin_no' => '987-654-321-000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_id' => Str::uuid(),
                'name' => 'Manila Furniture Mart',
                'address' => '789 Commerce Road, Quezon City, Philippines',
                'vat_type' => 'Non_VAT',
                'contact_person' => 'Pedro Reyes',
                'contact_number' => '+63-2-5555-1234',
                'tin_no' => '456-789-123-000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('suppliers')->insert($suppliers);
    }
}
