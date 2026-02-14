<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NewUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Store user IDs for role assignment
        $userIds = [];
        
        $users = [
            // Admin User
            [
                'user_id' => $userIds['admin'] = Str::uuid(),
                'name' => 'Admin User',
                'email' => 'admin@uniprint.com',
                'position' => 'System Administrator',
                'department' => 'IT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Business Users (Printing Shops)
            [
                'user_id' => $userIds['business1'] = Str::uuid(),
                'name' => 'Baguio QuickPrint Services',
                'email' => 'quickprint@business.com',
                'position' => 'Shop Manager',
                'department' => 'Management',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds['business2'] = Str::uuid(),
                'name' => 'Baguio Elite Copy Center',
                'email' => 'elite@business.com',
                'position' => 'Business Owner',
                'department' => 'Management',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds['business3'] = Str::uuid(),
                'name' => 'Baguio Digital Print Hub',
                'email' => 'digitalhub@business.com',
                'position' => 'Customer Service Rep',
                'department' => 'Customer Service',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Customer Users
            [
                'user_id' => $userIds['customer1'] = Str::uuid(),
                'name' => 'Sarah Dela Cruz',
                'email' => 'customer@uniprint.com',
                'position' => 'Customer',
                'department' => 'External',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds['customer2'] = Str::uuid(),
                'name' => 'Juan Martinez',
                'email' => 'juan.martinez@email.com',
                'position' => 'Customer',
                'department' => 'External',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds['customer3'] = Str::uuid(),
                'name' => 'Maria Santos',
                'email' => 'maria.santos@email.com',
                'position' => 'Customer',
                'department' => 'External',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds['customer4'] = Str::uuid(),
                'name' => 'Alex Rivera',
                'email' => 'alex.rivera@email.com',
                'position' => 'Customer',
                'department' => 'External',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insertOrIgnore($users);

        // Refresh user IDs in case some already existed
        $emails = collect($users)->pluck('email');
        $existingIds = DB::table('users')->whereIn('email', $emails)->pluck('user_id', 'email');

        // Map back to our local array
        foreach ($users as $u) {
            $email = $u['email'];
            if (isset($existingIds[$email])) {
                // overwrite generated ID with existing one
                $storedId = $existingIds[$email];
                // also patch in $userIds for later usage
                $key = array_search($u['user_id'], $userIds);
                if ($key !== false) {
                    $userIds[$key] = $storedId;
                }
            }
        }

        // Create login credentials
        $logins = [
            // Admin Login
            [
                'login_id' => Str::uuid(),
                'user_id' => $userIds['admin'],
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Business Logins
            [
                'login_id' => Str::uuid(),
                'user_id' => $userIds['business1'],
                'username' => 'quickprint',
                'password' => Hash::make('business123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'login_id' => Str::uuid(),
                'user_id' => $userIds['business2'],
                'username' => 'elitecopy',
                'password' => Hash::make('business123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'login_id' => Str::uuid(),
                'user_id' => $userIds['business3'],
                'username' => 'digitalhub',
                'password' => Hash::make('business123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Customer Logins
            [
                'login_id' => Str::uuid(),
                'user_id' => $userIds['customer1'],
                'username' => 'customer',
                'password' => Hash::make('customer123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'login_id' => Str::uuid(),
                'user_id' => $userIds['customer2'],
                'username' => 'john',
                'password' => Hash::make('customer123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'login_id' => Str::uuid(),
                'user_id' => $userIds['customer3'],
                'username' => 'maria',
                'password' => Hash::make('customer123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'login_id' => Str::uuid(),
                'user_id' => $userIds['customer4'],
                'username' => 'alex',
                'password' => Hash::make('customer123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('login')->insertOrIgnore($logins);

        // Assign roles
        $roleTypes = DB::table('role_types')->pluck('role_type_id', 'user_role_type');
        
        $roles = [
            // Admin Role
            [
                'role_id' => Str::uuid(),
                'user_id' => $userIds['admin'],
                'role_type_id' => $roleTypes['admin'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Business Roles
            [
                'role_id' => Str::uuid(),
                'user_id' => $userIds['business1'],
                'role_type_id' => $roleTypes['business_user'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => Str::uuid(),
                'user_id' => $userIds['business2'],
                'role_type_id' => $roleTypes['business_user'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => Str::uuid(),
                'user_id' => $userIds['business3'],
                'role_type_id' => $roleTypes['business_user'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Customer Roles
            [
                'role_id' => Str::uuid(),
                'user_id' => $userIds['customer1'],
                'role_type_id' => $roleTypes['customer'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => Str::uuid(),
                'user_id' => $userIds['customer2'],
                'role_type_id' => $roleTypes['customer'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => Str::uuid(),
                'user_id' => $userIds['customer3'],
                'role_type_id' => $roleTypes['customer'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => Str::uuid(),
                'user_id' => $userIds['customer4'],
                'role_type_id' => $roleTypes['customer'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insertOrIgnore($roles);
        
        // Create sample conversations between customers and businesses
        $conversations = [
            [
                'conversation_id' => $conversationId1 = Str::uuid(),
                'customer_id' => $userIds['customer1'],
                'business_id' => $userIds['business1'],
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subHours(1),
            ],
            [
                'conversation_id' => $conversationId2 = Str::uuid(),
                'customer_id' => $userIds['customer2'],
                'business_id' => $userIds['business2'],
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subMinutes(30),
            ],
            [
                'conversation_id' => $conversationId3 = Str::uuid(),
                'customer_id' => $userIds['customer3'],
                'business_id' => $userIds['business3'],
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subMinutes(15),
            ],
            [
                'conversation_id' => $conversationId4 = Str::uuid(),
                'customer_id' => $userIds['customer1'],
                'business_id' => $userIds['business2'],
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subMinutes(5),
            ],
        ];
        
        DB::table('conversations')->insert($conversations);
        
        // Create sample chat messages
        $messages = [
            // Conversation 1: Sarah Dela Cruz & Baguio QuickPrint Services
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId1,
                'sender_id' => $userIds['customer1'],
                'message_text' => 'Hi! I need to print 100 business cards. Do you offer design services?',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId1,
                'sender_id' => $userIds['business1'],
                'message_text' => 'Hello Sarah! Yes, we offer professional design services. We can create custom business cards for you. What style are you looking for?',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subDays(2)->addMinutes(15),
                'updated_at' => now()->subDays(2)->addMinutes(15),
            ],
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId1,
                'sender_id' => $userIds['customer1'],
                'message_text' => 'I\'m looking for something modern and professional. My company is in tech consulting.',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subDays(2)->addMinutes(30),
                'updated_at' => now()->subDays(2)->addMinutes(30),
            ],
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId1,
                'sender_id' => $userIds['business1'],
                'message_text' => 'Perfect! We have several modern templates that would work great for tech companies. The price for 100 business cards with custom design is ₱850. Would you like to see some samples?',
                'message_type' => 'text',
                'is_read' => false,
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
            
            // Conversation 2: Juan Martinez & Baguio Elite Copy Center
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId2,
                'sender_id' => $userIds['customer2'],
                'message_text' => 'Good morning! I need to print wedding invitations. Do you handle large orders?',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId2,
                'sender_id' => $userIds['business2'],
                'message_text' => 'Good morning Juan! Absolutely! We specialize in wedding invitations. How many invitations do you need and do you have a design ready?',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subDays(1)->addMinutes(20),
                'updated_at' => now()->subDays(1)->addMinutes(20),
            ],
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId2,
                'sender_id' => $userIds['customer2'],
                'message_text' => 'I need about 150 invitations. I have a rough design but might need help with the final touches.',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subDays(1)->addHours(2),
                'updated_at' => now()->subDays(1)->addHours(2),
            ],
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId2,
                'sender_id' => $userIds['business2'],
                'message_text' => 'That\'s perfect! We can definitely help you refine the design. For 150 premium wedding invitations with design assistance, the cost would be around ₱2,250. When do you need them ready?',
                'message_type' => 'text',
                'is_read' => false,
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ],
            
            // Conversation 3: Maria Santos & Baguio Digital Print Hub
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId3,
                'sender_id' => $userIds['customer3'],
                'message_text' => 'Hi there! I need urgent printing for a presentation tomorrow. Can you help?',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId3,
                'sender_id' => $userIds['business3'],
                'message_text' => 'Hello Maria! Yes, we offer same-day printing services. What do you need printed and how many copies?',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subHours(6)->addMinutes(10),
                'updated_at' => now()->subHours(6)->addMinutes(10),
            ],
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId3,
                'sender_id' => $userIds['customer3'],
                'message_text' => 'I need 50 copies of a 20-page presentation in color. High quality paper preferred.',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ],
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId3,
                'sender_id' => $userIds['business3'],
                'message_text' => 'Perfect! We can have that ready by 3 PM today. For 50 copies of 20 pages in color on premium paper, the cost is ₱1,200. There\'s a ₱200 rush fee for same-day service. Should I proceed?',
                'message_type' => 'text',
                'is_read' => false,
                'created_at' => now()->subMinutes(15),
                'updated_at' => now()->subMinutes(15),
            ],
            
            // Conversation 4: Sarah Dela Cruz & Baguio Elite Copy Center (second conversation)
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId4,
                'sender_id' => $userIds['customer1'],
                'message_text' => 'Hello! I saw your work on wedding invitations and I\'m interested in your services for corporate brochures.',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId4,
                'sender_id' => $userIds['business2'],
                'message_text' => 'Hi Sarah! Thank you for reaching out. We\'d love to help with your corporate brochures. What\'s the scope of your project?',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subHours(2)->addMinutes(45),
                'updated_at' => now()->subHours(2)->addMinutes(45),
            ],
            [
                'message_id' => Str::uuid(),
                'conversation_id' => $conversationId4,
                'sender_id' => $userIds['customer1'],
                'message_text' => 'I need about 200 tri-fold brochures for our tech consulting company. We have the content but need design help.',
                'message_type' => 'text',
                'is_read' => false,
                'created_at' => now()->subMinutes(5),
                'updated_at' => now()->subMinutes(5),
            ],
        ];
        
        DB::table('chat_messages')->insert($messages);
        
        // Output seeding summary
        echo "\n";
        echo "✅ Chat System Users & Conversations Seeded Successfully!\n";
        echo "========================================================\n";
        echo "Admin Account:\n";
        echo "  Username: admin | Password: admin123\n\n";
        echo "Business Accounts (Printing Shops):\n";
        echo "  Username: quickprint | Password: business123\n";
        echo "  Username: elitecopy   | Password: business123\n";
        echo "  Username: digitalhub  | Password: business123\n\n";
        echo "Customer Accounts:\n";
        echo "  Username: customer | Password: customer123\n";
        echo "  Username: juan     | Password: customer123\n";
        echo "  Username: maria    | Password: customer123\n";
        echo "  Username: alex     | Password: customer123\n\n";
        echo "Sample Conversations Created:\n";
        echo "• Sarah Dela Cruz ↔ Baguio QuickPrint Services (business cards)\n";
        echo "• Juan Martinez ↔ Baguio Elite Copy Center (wedding invitations)\n";
        echo "• Maria Santos ↔ Baguio Digital Print Hub (urgent presentation)\n";
        echo "• Sarah Dela Cruz ↔ Baguio Elite Copy Center (corporate brochures)\n\n";
        echo "Test the chat by:\n";
        echo "1. Login as any customer (customer/juan/maria/alex)\n";
        echo "2. Navigate to customer dashboard\n";
        echo "3. Click on chat or orders section\n";
        echo "4. View existing conversations or start new ones\n";
        echo "========================================================\n";
    }
}
