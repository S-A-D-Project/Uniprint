<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing users
        User::query()->delete();

        // Admin users
        User::create([
            'username' => 'admin',
            'password_hash' => Hash::make('admin123'),
            'email' => 'admin@uniprint.com',
            'role_type' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'username' => 'admin_john',
            'password_hash' => Hash::make('password123'),
            'email' => 'john.admin@uniprint.com',
            'role_type' => 'admin',
            'is_active' => true,
        ]);

        // Business users
        User::create([
            'username' => 'printshop_owner',
            'password_hash' => Hash::make('password123'),
            'email' => 'owner@printmaster.com',
            'role_type' => 'business_user',
            'is_active' => true,
        ]);

        User::create([
            'username' => 'tshirt_manager',
            'password_hash' => Hash::make('password123'),
            'email' => 'manager@tshirtpro.com',
            'role_type' => 'business_user',
            'is_active' => true,
        ]);

        User::create([
            'username' => 'poster_staff',
            'password_hash' => Hash::make('password123'),
            'email' => 'staff@posterhub.com',
            'role_type' => 'business_user',
            'is_active' => true,
        ]);

        // Customers
        $customers = [
            ['username' => 'sarah_jones', 'email' => 'sarah.jones@email.com'],
            ['username' => 'mike_wilson', 'email' => 'mike.wilson@email.com'],
            ['username' => 'emma_davis', 'email' => 'emma.davis@email.com'],
            ['username' => 'james_brown', 'email' => 'james.brown@email.com'],
            ['username' => 'lisa_garcia', 'email' => 'lisa.garcia@email.com'],
            ['username' => 'david_martin', 'email' => 'david.martin@email.com'],
            ['username' => 'amy_lee', 'email' => 'amy.lee@email.com'],
            ['username' => 'chris_taylor', 'email' => 'chris.taylor@email.com'],
            ['username' => 'jennifer_white', 'email' => 'jennifer.white@email.com'],
            ['username' => 'robert_anderson', 'email' => 'robert.anderson@email.com'],
        ];

        foreach ($customers as $customer) {
            User::create([
                'username' => $customer['username'],
                'password_hash' => Hash::make('customer123'),
                'email' => $customer['email'],
                'role_type' => 'customer',
                'is_active' => true,
            ]);
        }
    }
}
