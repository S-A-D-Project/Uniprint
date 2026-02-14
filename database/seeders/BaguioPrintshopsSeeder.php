<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BaguioPrintshopsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $supportsVatTypeId = Schema::hasTable('enterprises') && Schema::hasColumn('enterprises', 'vat_type_id');
        $vatTypeId = null;
        if ($supportsVatTypeId && Schema::hasTable('vat_types')) {
            $vatTypeId = DB::table('vat_types')->value('vat_type_id');
            if (! $vatTypeId) {
                $vatTypeId = (string) Str::uuid();
                DB::table('vat_types')->insertOrIgnore([
                    'vat_type_id' => $vatTypeId,
                    'type_name' => 'VAT',
                ]);
            }
        }

        $enterprises = [
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Almuril Copy Center',
                'address'         => 'Near SLU main gate / Beside McDo Bonifacio',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => null,
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Anndreleigh Photocopy Services',
                'address'         => '7A purok 1 bal marcoville, PNR, Baguio',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => '+63 997 108 9173',
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Baguio Allied Printers / Allied Printing Press',
                'address'         => '#3 Urbano Street, Palma-Urbano',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => null,
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Bensa Publishing House',
                'address'         => 'Harrison Road, Baguio City',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => null,
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Cjs Printing Services',
                'address'         => 'Gov. Pack Road, beside Genesis Bus Terminal',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => null,
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Cyber Printing Press',
                'address'         => '68 Naguilian Road, Baguio City',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => null,
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Dacanay Printshop',
                'address'         => '19-B Hamada Subd., Baguio City',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => '(074) 444 2796',
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Gold Ink Printing Press',
                'address'         => 'Adivay Bldg., Lower Bonifacio St.',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => null,
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Higher-UP Printing',
                'address'         => '119 Manuel Roxas, Baguio',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => '+63 74 422 5121',
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'IKT Printing Services',
                'address'         => null,
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => null,
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Kebs Enterprise',
                'address'         => '#36 A. Bonifacio Street',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => '(074) 422 8011',
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'LSK Printing Services',
                'address'         => 'Justice Village Marcos Highway',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => '0918 695 4112',
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Point and Print Printing Services',
                'address'         => 'Session Rd, Baguio, Benguet',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => '+63 907 159 8561',
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'PRINTOREX Digital Printing Shop',
                'address'         => '214, Mabini Shopping center, Baguio',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => '+63 950 426 5889',
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Printitos Printing Services',
                'address'         => '99 Mabini St, Baguio',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => '+63 992 356 4390',
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Unique Printing Press',
                'address'         => '27 Legarda Road cor Del Pilar St.',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => '(074) 442 3447',
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'V. Mendoza Printing Press',
                'address'         => '10 Synita Bldg. Upper Mabini St.',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => null,
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
            array_filter([
                'enterprise_id'   => Str::uuid(),
                'name'            => 'Valley Printing Specialist',
                'address'         => '493B Youngland Camp 7',
                'vat_type_id'      => $supportsVatTypeId ? $vatTypeId : null,
                'contact_person'  => null,
                'contact_number'  => null,
                'tin_no'          => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], fn ($v) => $v !== null),
        ];

        // Use upsert-by-name to avoid duplication when reseeding
        $businessUsers = [];
        $enterpriseNames = [];
        foreach ($enterprises as $index => $enterprise) {
            $existingEnterpriseId = DB::table('enterprises')
                ->whereRaw('LOWER(name) = ?', [strtolower((string) $enterprise['name'])])
                ->value('enterprise_id');

            if ($existingEnterpriseId) {
                $enterprise['enterprise_id'] = $existingEnterpriseId;
                $update = [
                    'address' => $enterprise['address'],
                    'contact_person' => $enterprise['contact_person'],
                    'contact_number' => $enterprise['contact_number'],
                    'tin_no' => $enterprise['tin_no'],
                    'updated_at' => $now,
                ];
                if ($supportsVatTypeId) {
                    $update['vat_type_id'] = $enterprise['vat_type_id'];
                }

                $update = array_filter($update, fn ($v) => $v !== null);

                DB::table('enterprises')
                    ->where('enterprise_id', $existingEnterpriseId)
                    ->update($update);
            } else {
                DB::table('enterprises')->insert($enterprise);
            }
            
            // Create a corresponding business user for each enterprise
            $businessUserEmail = strtolower(str_replace(' ', '', $enterprise['name'])) . '@business.com';
            $businessUserId = Str::uuid();
            
            $businessUsers[] = [
                'user_id' => $businessUserId,
                'name' => $enterprise['name'] . ' Manager',
                'email' => $businessUserEmail,
                'position' => 'Owner',
                'department' => 'Management',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            
            // Store enterprise name for later use
            $enterpriseNames[$businessUserEmail] = $enterprise['name'];
        }
        
        // Insert business users and get their actual IDs
        $insertedUserIds = [];
        foreach ($businessUsers as $user) {
            DB::table('users')->insertOrIgnore($user);
            // Get the actual user ID (in case it already existed)
            $actualUser = DB::table('users')->where('email', $user['email'])->first();
            if ($actualUser) {
                $insertedUserIds[] = $actualUser->user_id;
                
                // Create login credentials for the business user
                $enterpriseName = $enterpriseNames[$user['email']] ?? '';
                $username = strtolower(str_replace(' ', '', $enterpriseName));
                $password = 'business123'; // Default password
                
                DB::table('login')->insertOrIgnore([
                    'login_id' => Str::uuid(),
                    'user_id' => $actualUser->user_id,
                    'username' => $username,
                    'password' => Hash::make($password),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
        
        // Assign business_user role to each business user
        $businessUserRole = DB::table('role_types')->where('user_role_type', 'business_user')->first();
        if ($businessUserRole) {
            foreach ($insertedUserIds as $userId) {
                DB::table('roles')->insertOrIgnore([
                    'role_id' => Str::uuid(),
                    'user_id' => $userId,
                    'role_type_id' => $businessUserRole->role_type_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
        
        // Link business users to enterprises
        $enterpriseIndex = 0;
        foreach ($enterprises as $enterprise) {
            if (isset($insertedUserIds[$enterpriseIndex])) {
                // Create a staff record linking the business user to the enterprise (legacy/backward compatible)
                if (Schema::hasTable('staff')) {
                    DB::table('staff')->insertOrIgnore([
                        'staff_id' => Str::uuid(),
                        'user_id' => $insertedUserIds[$enterpriseIndex],
                        'enterprise_id' => $enterprise['enterprise_id'],
                        'position' => 'Owner',
                        'department' => 'Management',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                if (Schema::hasColumn('enterprises', 'owner_user_id')) {
                    DB::table('enterprises')
                        ->where('enterprise_id', $enterprise['enterprise_id'])
                        ->update(['owner_user_id' => $insertedUserIds[$enterpriseIndex]]);
                }
            }
            $enterpriseIndex++;
        }
    }
}
