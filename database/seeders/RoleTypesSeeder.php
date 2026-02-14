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
            'customer',
            'business_user',
            'admin',
        ];

        foreach ($roleTypes as $type) {
            $exists = DB::table('role_types')->where('user_role_type', $type)->exists();
            if ($exists) {
                continue;
            }

            DB::table('role_types')->insert([
                'role_type_id' => (string) Str::uuid(),
                'user_role_type' => $type,
            ]);
        }
    }
}
