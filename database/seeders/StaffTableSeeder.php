<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
use App\Models\Enterprise;
use Illuminate\Database\Seeder;

class StaffTableSeeder extends Seeder
{
    public function run(): void
    {
        // Get users and enterprises
        $businessUsers = User::where('role_type', 'business_user')->get();
        $enterprises = Enterprise::all();

        if ($businessUsers->count() < 3 || $enterprises->count() < 3) {
            return;
        }

        // Link business users to enterprises
        Staff::create([
            'staff_name' => 'Miguel Santos',
            'position' => 'Owner',
            'department' => 'Management',
            'user_id' => $businessUsers[0]->user_id,
            'enterprise_id' => $enterprises[0]->enterprise_id,
        ]);

        Staff::create([
            'staff_name' => 'Sarah Dela Cruz',
            'position' => 'Store Manager',
            'department' => 'Operations',
            'user_id' => $businessUsers[1]->user_id,
            'enterprise_id' => $enterprises[1]->enterprise_id,
        ]);

        Staff::create([
            'staff_name' => 'David Reyes',
            'position' => 'Production Supervisor',
            'department' => 'Production',
            'user_id' => $businessUsers[2]->user_id,
            'enterprise_id' => $enterprises[2]->enterprise_id,
        ]);

        // Admin staff (no enterprise)
        $adminUsers = User::where('role_type', 'admin')->get();

        if ($adminUsers->isEmpty()) {
            return;
        }
        
        Staff::create([
            'staff_name' => 'Admin User',
            'position' => 'System Administrator',
            'department' => 'IT',
            'user_id' => $adminUsers[0]->user_id,
            'enterprise_id' => null,
        ]);

        if ($adminUsers->count() > 1) {
            Staff::create([
                'staff_name' => 'Juan Admin',
                'position' => 'Platform Manager',
                'department' => 'Management',
                'user_id' => $adminUsers[1]->user_id,
                'enterprise_id' => null,
            ]);
        }
    }
}
