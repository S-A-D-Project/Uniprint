<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleTypesSeeder extends Seeder
{
    public function run(): void
    {
        $roleTypes = [
            ['role_type_id' => Str::uuid(), 'user_role_type' => 'customer'],
            ['role_type_id' => Str::uuid(), 'user_role_type' => 'business_user'],
            ['role_type_id' => Str::uuid(), 'user_role_type' => 'admin'],
        ];

        DB::table('role_types')->insert($roleTypes);
    }
}
