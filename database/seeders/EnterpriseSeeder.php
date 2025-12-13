<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EnterpriseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create real Baguio printshops based on actual business data
        $enterprises = [
            [
                'enterprise_id' => Str::uuid(),
                'name' => 'Kebs Enterprise',
                'address' => '36 Lower Bonifacio St, Barangay ABCR, Baguio, 2600 Benguet',
                'contact_person' => 'Manager',
                'contact_number' => '+63 999 888 3955',
                'tin_no' => '123-456-789-001',
                'shop_logo' => null,
                'rating' => 5.0,
                'distance' => 1.9,
                'opening_hours' => 'Monday to Saturday: 8:00 AM–6:00 PM, Sunday: Closed',
                'services_description' => 'Highly-rated print shop and souvenir store. Services include general printing services and specialty items like plaques for awards and recognition.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'enterprise_id' => Str::uuid(),
                'name' => 'Point and Print Printing Services',
                'address' => 'Session Rd, Baguio, Benguet',
                'contact_person' => 'Manager',
                'contact_number' => '+63 907 159 8561',
                'tin_no' => '123-456-789-002',
                'shop_logo' => null,
                'rating' => 5.0,
                'distance' => 2.3,
                'opening_hours' => 'Monday to Saturday: 8:00 AM–8:00 PM, Sunday: 10:00 AM–6:00 PM',
                'services_description' => 'Full-service printing shop offering comprehensive printing solutions with extended hours.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'enterprise_id' => Str::uuid(),
                'name' => 'PRINTOREX Digital Printing Shop',
                'address' => '214, Mabini Shopping center, Baguio, 2600 Benguet',
                'contact_person' => 'Manager',
                'contact_number' => '+63 950 426 5889',
                'tin_no' => '123-456-789-003',
                'shop_logo' => null,
                'rating' => 5.0,
                'distance' => 2.2,
                'opening_hours' => 'Monday to Friday: 9:00 AM–6:30 PM, Saturday: 9:00 AM–7:00 PM, Sunday: Closed',
                'services_description' => 'Digital printing specialist located in Mabini Shopping Center with modern printing equipment.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'enterprise_id' => Str::uuid(),
                'name' => 'Anndreleigh Photocopy Services',
                'address' => '7A purok 1 bal marcoville, PNR, Baguio, 2600 Benguet',
                'contact_person' => 'Manager',
                'contact_number' => '+63 997 108 9173',
                'tin_no' => '123-456-789-004',
                'shop_logo' => null,
                'rating' => 4.8,
                'distance' => 2.7,
                'opening_hours' => 'Monday to Saturday: 9:00 AM–8:00 PM, Sunday: 9:00 AM–7:00 PM',
                'services_description' => 'Photocopy and printing services with excellent customer ratings and convenient hours.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'enterprise_id' => Str::uuid(),
                'name' => 'Printitos Printing Services',
                'address' => '99 Mabini St, Baguio, 2600 Benguet',
                'contact_person' => 'Manager',
                'contact_number' => '+63 992 356 4390',
                'tin_no' => '123-456-789-005',
                'shop_logo' => null,
                'rating' => 4.5,
                'distance' => 2.2,
                'opening_hours' => 'Monday to Friday: 9:30 AM–7:30 PM, Saturday: 12:00–7:30 PM, Sunday: 12:00–7:00 PM',
                'services_description' => 'Professional printing services on Mabini Street with flexible operating hours.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'enterprise_id' => Str::uuid(),
                'name' => 'Higher-UP Printing',
                'address' => '119 Manuel Roxas, Baguio, Benguet',
                'contact_person' => 'Manager',
                'contact_number' => '+63 74 422 5121',
                'tin_no' => '123-456-789-006',
                'shop_logo' => null,
                'rating' => 2.2,
                'distance' => 1.3,
                'opening_hours' => 'Monday to Saturday: 9:00 AM–6:30 PM, Sunday: Closed',
                'services_description' => 'Basic printing services on Manuel Roxas Street.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $now = now();
        $businessUsers = [];
        
        foreach ($enterprises as $enterprise) {
            DB::table('enterprises')->insertOrIgnore($enterprise);
            
            // Create a corresponding business user for each enterprise
            $businessUserEmail = strtolower(str_replace(' ', '', $enterprise['name'])) . '@business.com';
            $businessUserId = Str::uuid();
            
            $businessUsers[] = [
                'user_id' => $businessUserId,
                'name' => $enterprise['name'] . ' Manager',
                'email' => $businessUserEmail,
                'position' => 'Shop Manager',
                'department' => 'Management',
                'created_at' => $now,
                'updated_at' => $now,
                'enterprise_id' => $enterprise['enterprise_id'], // Store for later linking
            ];
            
            // Create sample services for each enterprise
            $services = [
                [
                    'service_id' => Str::uuid(),
                    'enterprise_id' => $enterprise['enterprise_id'],
                    'service_name' => 'Business Cards',
                    'description' => 'Professional business cards printed on premium cardstock',
                    'base_price' => 500.00,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'service_id' => Str::uuid(),
                    'enterprise_id' => $enterprise['enterprise_id'],
                    'service_name' => 'Flyers A5',
                    'description' => 'High-quality promotional flyers in A5 size',
                    'base_price' => 300.00,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'service_id' => Str::uuid(),
                    'enterprise_id' => $enterprise['enterprise_id'],
                    'service_name' => 'Posters A3',
                    'description' => 'Large format posters for advertising and events',
                    'base_price' => 1200.00,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($services as $service) {
                DB::table('services')->insertOrIgnore($service);
                
                // Add customization options for business cards
                if ($service['service_name'] === 'Business Cards') {
                    $customizations = [
                        [
                            'option_id' => Str::uuid(),
                            'service_id' => $service['service_id'],
                            'option_name' => 'Standard (300gsm)',
                            'option_type' => 'Paper Type',
                            'price_modifier' => 0.00,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'option_id' => Str::uuid(),
                            'service_id' => $service['service_id'],
                            'option_name' => 'Premium (350gsm)',
                            'option_type' => 'Paper Type',
                            'price_modifier' => 150.00,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'option_id' => Str::uuid(),
                            'service_id' => $service['service_id'],
                            'option_name' => 'Matte Finish',
                            'option_type' => 'Finish',
                            'price_modifier' => 0.00,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'option_id' => Str::uuid(),
                            'service_id' => $service['service_id'],
                            'option_name' => 'Glossy Finish',
                            'option_type' => 'Finish',
                            'price_modifier' => 50.00,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ];

                    foreach ($customizations as $customization) {
                        DB::table('customization_options')->insertOrIgnore($customization);
                    }
                }
            }
        }
        
        // Insert business users
        $businessUserRole = DB::table('role_types')->where('user_role_type', 'business_user')->first();
        
        foreach ($businessUsers as $user) {
            $userData = $user;
            $enterpriseId = $userData['enterprise_id'];
            unset($userData['enterprise_id']); // Remove the temporary field
            
            DB::table('users')->insertOrIgnore($userData);
            
            // Get the actual user ID (in case it already existed)
            $actualUser = DB::table('users')->where('email', $userData['email'])->first();
            if ($actualUser) {
                $userId = $actualUser->user_id;
                
                // Create login credentials for the business user
                $enterpriseName = $userData['name'];
                $username = strtolower(str_replace(' ', '', $enterpriseName));
                $password = 'business123'; // Default password
                
                DB::table('login')->insertOrIgnore([
                    'login_id' => Str::uuid(),
                    'user_id' => $userId,
                    'username' => $username,
                    'password' => Hash::make($password),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                
                // Assign business_user role to each business user
                if ($businessUserRole) {
                    DB::table('roles')->insertOrIgnore([
                        'role_id' => Str::uuid(),
                        'user_id' => $userId,
                        'role_type_id' => $businessUserRole->role_type_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                
                // Link business user to enterprise as staff
                DB::table('staff')->insertOrIgnore([
                    'staff_id' => Str::uuid(),
                    'user_id' => $userId,
                    'enterprise_id' => $enterpriseId,
                    'position' => 'Manager',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
