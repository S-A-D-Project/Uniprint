<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // NOTE: This is a legacy seeder kept for compatibility. It should be safe to run multiple times
        // and should not delete all users.

        // Admin users
        User::firstOrCreate(
            ['email' => 'admin@uniprint.com'],
            [
                'username' => 'admin',
                'password_hash' => Hash::make('admin123'),
                'role_type' => 'admin',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'juan.admin@uniprint.com'],
            [
                'username' => 'admin_juan',
                'password_hash' => Hash::make('password123'),
                'role_type' => 'admin',
                'is_active' => true,
            ]
        );

        // Business users (Baguio context)
        User::firstOrCreate(
            ['email' => 'owner@baguioprintshop.com'],
            [
                'username' => 'baguioprint_owner',
                'password_hash' => Hash::make('password123'),
                'role_type' => 'business_user',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@sessionroadprints.com'],
            [
                'username' => 'sessionroad_manager',
                'password_hash' => Hash::make('password123'),
                'role_type' => 'business_user',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'staff@mabiniprints.com'],
            [
                'username' => 'mabini_staff',
                'password_hash' => Hash::make('password123'),
                'role_type' => 'business_user',
                'is_active' => true,
            ]
        );

        // Customers (Filipino names)
        $customers = [
            ['username' => 'sarah_delacruz', 'email' => 'sarah.delacruz@email.com'],
            ['username' => 'juan_martinez', 'email' => 'juan.martinez@email.com'],
            ['username' => 'maria_santos', 'email' => 'maria.santos@email.com'],
            ['username' => 'alex_rivera', 'email' => 'alex.rivera@email.com'],
            ['username' => 'paolo_reyes', 'email' => 'paolo.reyes@email.com'],
            ['username' => 'angelica_cruz', 'email' => 'angelica.cruz@email.com'],
            ['username' => 'mark_dizon', 'email' => 'mark.dizon@email.com'],
            ['username' => 'janelle_bautista', 'email' => 'janelle.bautista@email.com'],
            ['username' => 'carlo_garcia', 'email' => 'carlo.garcia@email.com'],
            ['username' => 'kate_mendoza', 'email' => 'kate.mendoza@email.com'],
        ];

        foreach ($customers as $customer) {
            User::firstOrCreate(
                ['email' => $customer['email']],
                [
                    'username' => $customer['username'],
                    'password_hash' => Hash::make('customer123'),
                    'role_type' => 'customer',
                    'is_active' => true,
                ]
            );
        }
    }
}
