<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestCustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Create test customer user (Filipino / Baguio testing)
        $existing = DB::table('users')->where('email', 'test@uniprint.com')->first();
        $userId = $existing?->user_id ?: (string) Str::uuid();

        DB::table('users')->insertOrIgnore([
            'user_id' => $userId,
            'name' => 'Test Customer - Juan Dela Cruz',
            'email' => 'test@uniprint.com',
            'position' => 'Customer',
            'department' => 'External',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create login record
        DB::table('login')->insertOrIgnore([
            'login_id' => (string) Str::uuid(),
            'user_id' => $userId,
            'username' => 'testcustomer',
            'password' => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign customer role
        $customerRole = DB::table('role_types')->where('user_role_type', 'customer')->first();
        if ($customerRole) {
            DB::table('roles')->insertOrIgnore([
                'role_id' => (string) Str::uuid(),
                'user_id' => $userId,
                'role_type_id' => $customerRole->role_type_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Test customer created successfully!');
        $this->command->info('Username: testcustomer');
        $this->command->info('Password: password123');
    }
}
