<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnterprisesSeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = [
            [
                'enterprise_id' => Str::uuid(),
                'name' => 'PrintMaster Solutions',
                'address' => '123 Main Street, Downtown, Baguio City',
                'contact_person' => 'Maria Santos',
                'contact_number' => '+63-74-123-4567',
                'tin_no' => '123-456-789-000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'enterprise_id' => Str::uuid(),
                'name' => 'T-Shirt Pro Baguio',
                'address' => '456 Fashion Avenue, Session Road, Baguio City',
                'contact_person' => 'Juan dela Cruz',
                'contact_number' => '+63-74-987-6543',
                'tin_no' => '987-654-321-000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'enterprise_id' => Str::uuid(),
                'name' => 'Poster Hub',
                'address' => '789 Creative Lane, Arts District, Baguio City',
                'contact_person' => 'Pedro Reyes',
                'contact_number' => '+63-74-555-1234',
                'tin_no' => '456-789-123-000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'enterprise_id' => Str::uuid(),
                'name' => 'Business Card Express',
                'address' => '321 Corporate Blvd, Business Park, Baguio City',
                'contact_person' => 'Anna Martinez',
                'contact_number' => '+63-74-444-5678',
                'tin_no' => '111-222-333-000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'enterprise_id' => Str::uuid(),
                'name' => 'Banner World',
                'address' => '654 Marketing Drive, Industrial Zone, Baguio City',
                'contact_person' => 'Rico Gonzales',
                'contact_number' => '+63-74-777-8899',
                'tin_no' => '555-666-777-000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('enterprises')->insert($enterprises);
    }
}
