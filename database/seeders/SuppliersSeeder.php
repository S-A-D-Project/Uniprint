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
                'name' => 'Baguio Office Supplies Trading',
                'address' => 'Magsaysay Ave, Baguio City, Benguet, Philippines',
                'vat_type' => 'VAT',
                'contact_person' => 'Maria Santos',
                'contact_number' => '+63 917 123 4567',
                'tin_no' => '123-456-789-000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_id' => Str::uuid(),
                'name' => 'Baguio Tech Solutions',
                'address' => 'Upper Session Rd, Baguio City, Benguet, Philippines',
                'vat_type' => 'VAT',
                'contact_person' => 'Juan dela Cruz',
                'contact_number' => '+63 918 987 6543',
                'tin_no' => '987-654-321-000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_id' => Str::uuid(),
                'name' => 'Baguio Packaging & Materials',
                'address' => 'Harrison Rd, Baguio City, Benguet, Philippines',
                'vat_type' => 'Non_VAT',
                'contact_person' => 'Pedro Reyes',
                'contact_number' => '+63 905 555 1234',
                'tin_no' => '456-789-123-000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('suppliers')->insertOrIgnore($suppliers);
    }
}
