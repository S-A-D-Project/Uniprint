<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations using raw SQL for reliability
     */
    public function up(): void
    {
        $indexes = [
            // Order items
            ['table' => 'order_items', 'name' => 'idx_order_items_order', 'columns' => 'purchase_order_id'],
            ['table' => 'order_items', 'name' => 'idx_order_items_service', 'columns' => 'service_id'],
            
            // Payments
            ['table' => 'payments', 'name' => 'idx_payments_order', 'columns' => 'purchase_order_id'],
            ['table' => 'payments', 'name' => 'idx_payments_verified', 'columns' => 'is_verified'],
            
            // Order notifications - critical for dashboard
            ['table' => 'order_notifications', 'name' => 'idx_order_notifications_recipient_read', 'columns' => 'recipient_id, is_read'],
            ['table' => 'order_notifications', 'name' => 'idx_order_notifications_order', 'columns' => 'purchase_order_id'],
            
            // Saved services - cart queries
            ['table' => 'saved_services', 'name' => 'idx_saved_services_user_date', 'columns' => 'user_id, saved_at'],
            ['table' => 'saved_services', 'name' => 'idx_saved_services_service', 'columns' => 'service_id'],
            
            // Customization options
            ['table' => 'customization_options', 'name' => 'idx_customization_options_service', 'columns' => 'service_id'],
            
            // Order item customizations
            ['table' => 'order_item_customizations', 'name' => 'idx_order_item_customizations_item', 'columns' => 'order_item_id'],
            
            // Order design files
            ['table' => 'order_design_files', 'name' => 'idx_order_design_files_order', 'columns' => 'purchase_order_id'],
            
            // Roles
            ['table' => 'roles', 'name' => 'idx_roles_user', 'columns' => 'user_id'],
            ['table' => 'roles', 'name' => 'idx_roles_type', 'columns' => 'role_type_id'],
            
            // Staff
            ['table' => 'staff', 'name' => 'idx_staff_enterprise', 'columns' => 'enterprise_id'],
            ['table' => 'staff', 'name' => 'idx_staff_user', 'columns' => 'user_id'],
            
            // Enterprises
            ['table' => 'enterprises', 'name' => 'idx_enterprises_owner', 'columns' => 'owner_user_id'],
            
            // Statuses
            ['table' => 'statuses', 'name' => 'idx_statuses_name', 'columns' => 'status_name'],
            
            // Transactions
            ['table' => 'transactions', 'name' => 'idx_transactions_order', 'columns' => 'purchase_order_id'],
            
            // Login
            ['table' => 'login', 'name' => 'idx_login_user', 'columns' => 'user_id'],
        ];

        foreach ($indexes as $index) {
            try {
                // Check if index exists
                $exists = DB::selectOne(
                    "SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                    [$index['table'], $index['name']]
                );
                
                if (!$exists) {
                    DB::statement(
                        "CREATE INDEX {$index['name']} ON {$index['table']} ({$index['columns']})"
                    );
                }
            } catch (\Throwable $e) {
                // Continue on error
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = [
            'idx_order_items_order', 'idx_order_items_service',
            'idx_payments_order', 'idx_payments_verified',
            'idx_order_notifications_recipient_read', 'idx_order_notifications_order',
            'idx_saved_services_user_date', 'idx_saved_services_service',
            'idx_customization_options_service',
            'idx_order_item_customizations_item',
            'idx_order_design_files_order',
            'idx_roles_user', 'idx_roles_type',
            'idx_staff_enterprise', 'idx_staff_user',
            'idx_enterprises_owner',
            'idx_statuses_name',
            'idx_transactions_order',
            'idx_login_user',
        ];

        foreach ($indexes as $index) {
            try {
                DB::statement("DROP INDEX IF EXISTS {$index}");
            } catch (\Throwable $e) {}
        }
    }
};
