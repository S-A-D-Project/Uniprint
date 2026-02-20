<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TursoMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'turso:migrate';
    protected $description = 'Create tables in Turso database';

    private string $baseUrl;
    private string $authToken;

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = str_replace('libsql://', 'https://', env('TURSO_URL', 'libsql://uniprint-bragas002.aws-us-east-1.turso.io'));
        $this->authToken = env('TURSO_AUTH_TOKEN', '');
    }

    public function handle()
    {
        $this->info('Creating tables in Turso database...');

        $tables = [
            'users' => "CREATE TABLE IF NOT EXISTS users (
                user_id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                phone TEXT,
                avatar TEXT,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'enterprises' => "CREATE TABLE IF NOT EXISTS enterprises (
                enterprise_id INTEGER PRIMARY KEY AUTOINCREMENT,
                owner_user_id INTEGER,
                name TEXT NOT NULL,
                category TEXT,
                address TEXT,
                is_active INTEGER DEFAULT 1,
                is_verified INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'services' => "CREATE TABLE IF NOT EXISTS services (
                service_id INTEGER PRIMARY KEY AUTOINCREMENT,
                enterprise_id INTEGER NOT NULL,
                service_name TEXT NOT NULL,
                description TEXT,
                base_price REAL DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'role_types' => "CREATE TABLE IF NOT EXISTS role_types (
                role_type_id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_role_type TEXT UNIQUE NOT NULL,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'roles' => "CREATE TABLE IF NOT EXISTS roles (
                role_id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                role_type_id INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'staff' => "CREATE TABLE IF NOT EXISTS staff (
                staff_id INTEGER PRIMARY KEY AUTOINCREMENT,
                enterprise_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                position TEXT,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'customer_orders' => "CREATE TABLE IF NOT EXISTS customer_orders (
                purchase_order_id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id INTEGER NOT NULL,
                enterprise_id INTEGER NOT NULL,
                order_no TEXT UNIQUE NOT NULL,
                total REAL DEFAULT 0,
                payment_status TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'order_items' => "CREATE TABLE IF NOT EXISTS order_items (
                item_id INTEGER PRIMARY KEY AUTOINCREMENT,
                purchase_order_id INTEGER NOT NULL,
                service_id INTEGER NOT NULL,
                quantity INTEGER DEFAULT 1,
                item_subtotal REAL DEFAULT 0
            )",
            'statuses' => "CREATE TABLE IF NOT EXISTS statuses (
                status_id INTEGER PRIMARY KEY AUTOINCREMENT,
                status_name TEXT UNIQUE NOT NULL,
                description TEXT
            )",
            'notifications' => "CREATE TABLE IF NOT EXISTS notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                type TEXT,
                notifiable_type TEXT,
                notifiable_id INTEGER,
                data TEXT,
                read_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'order_notifications' => "CREATE TABLE IF NOT EXISTS order_notifications (
                notification_id INTEGER PRIMARY KEY AUTOINCREMENT,
                purchase_order_id INTEGER NOT NULL,
                recipient_id INTEGER NOT NULL,
                title TEXT,
                message TEXT,
                is_read INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'reviews' => "CREATE TABLE IF NOT EXISTS reviews (
                review_id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                enterprise_id INTEGER,
                service_id INTEGER,
                rating INTEGER,
                comment TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'conversations' => "CREATE TABLE IF NOT EXISTS conversations (
                conversation_id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id INTEGER NOT NULL,
                business_id INTEGER NOT NULL,
                is_read INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'chat_messages' => "CREATE TABLE IF NOT EXISTS chat_messages (
                message_id INTEGER PRIMARY KEY AUTOINCREMENT,
                conversation_id INTEGER NOT NULL,
                sender_id INTEGER NOT NULL,
                content TEXT,
                is_read INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'saved_services' => "CREATE TABLE IF NOT EXISTS saved_services (
                saved_service_id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                service_id INTEGER NOT NULL,
                quantity INTEGER DEFAULT 1
            )",
            'system_settings' => "CREATE TABLE IF NOT EXISTS system_settings (
                setting_id INTEGER PRIMARY KEY AUTOINCREMENT,
                key TEXT UNIQUE NOT NULL,
                value TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            'migrations' => "CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT NOT NULL,
                batch INTEGER NOT NULL
            )",
        ];

        foreach ($tables as $name => $sql) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->authToken,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl . '/v2/pipeline', [
                    'requests' => [
                        [
                            'type' => 'execute',
                            'stmt' => [
                                'sql' => $sql,
                                'args' => []
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $this->info("Created table: {$name}");
                } else {
                    $this->error("Failed to create table {$name}: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("Error creating table {$name}: " . $e->getMessage());
            }
        }

        // Insert default role types
        $this->info('Inserting default role types...');
        foreach (['admin', 'business_user', 'customer'] as $roleType) {
            try {
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->authToken,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl . '/v2/pipeline', [
                    'requests' => [
                        [
                            'type' => 'execute',
                            'stmt' => [
                                'sql' => 'INSERT OR IGNORE INTO role_types (user_role_type, description) VALUES (?, ?)',
                                'args' => [$roleType, ucfirst(str_replace('_', ' ', $roleType))]
                            ]
                        ]
                    ]
                ]);
            } catch (\Exception $e) {
                $this->warn("Could not insert role type {$roleType}");
            }
        }

        // Insert default statuses
        $this->info('Inserting default statuses...');
        foreach (['Pending', 'Confirmed', 'In Production', 'Ready for Pickup', 'Completed', 'Cancelled'] as $status) {
            try {
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->authToken,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl . '/v2/pipeline', [
                    'requests' => [
                        [
                            'type' => 'execute',
                            'stmt' => [
                                'sql' => 'INSERT OR IGNORE INTO statuses (status_name, description) VALUES (?, ?)',
                                'args' => [$status, $status]
                            ]
                        ]
                    ]
                ]);
            } catch (\Exception $e) {
                $this->warn("Could not insert status {$status}");
            }
        }

        $this->info('Migration completed!');
        return 0;
    }
}
