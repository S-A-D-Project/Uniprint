<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for frequently queried columns to improve performance
        
        // Chat system indexes
        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->index(['customer_id', 'business_id'], 'idx_conversations_participants');
                $table->index(['last_message_at'], 'idx_conversations_last_message');
                $table->index(['status'], 'idx_conversations_status');
            });
        }

        if (Schema::hasTable('chat_messages')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->index(['conversation_id', 'created_at'], 'idx_messages_conversation_time');
                $table->index(['sender_id'], 'idx_messages_sender');
                $table->index(['is_read'], 'idx_messages_read_status');
            });
        }

        if (Schema::hasTable('online_users')) {
            Schema::table('online_users', function (Blueprint $table) {
                $table->index(['last_seen_at'], 'idx_online_users_last_seen');
                $table->index(['status'], 'idx_online_users_status');
            });
        }

        // User and authentication indexes
        // Skip - email already has a unique index from the users table migration

        // Order system indexes
        if (Schema::hasTable('customer_orders')) {
            Schema::table('customer_orders', function (Blueprint $table) {
                if (Schema::hasColumn('customer_orders', 'customer_id') && Schema::hasColumn('customer_orders', 'created_at')) {
                    $table->index(['customer_id', 'created_at'], 'idx_orders_customer_date');
                }
                if (Schema::hasColumn('customer_orders', 'enterprise_id')) {
                    $table->index(['enterprise_id'], 'idx_orders_enterprise');
                }
                if (Schema::hasColumn('customer_orders', 'status_id')) {
                    $table->index(['status_id'], 'idx_orders_status');
                }
                if (Schema::hasColumn('customer_orders', 'date_requested')) {
                    $table->index(['date_requested'], 'idx_orders_date_requested');
                }
            });
        }

        // Product system indexes
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'enterprise_id') && Schema::hasColumn('products', 'is_active')) {
                    $table->index(['enterprise_id', 'is_active'], 'idx_products_enterprise_active');
                }
                if (Schema::hasColumn('products', 'base_price')) {
                    $table->index(['base_price'], 'idx_products_price');
                }
            });
        }

        // Enterprise indexes - skip, columns may not exist

        // Customization system indexes - skip, tables may not exist

        // Pricing system indexes - skip, tables may not exist

        

        // Order status tracking indexes
        if (Schema::hasTable('order_status_history')) {
            Schema::table('order_status_history', function (Blueprint $table) {
                if (Schema::hasColumn('order_status_history', 'purchase_order_id') && Schema::hasColumn('order_status_history', 'timestamp')) {
                    $table->index(['purchase_order_id', 'timestamp'], 'idx_status_history_order_time');
                }
                if (Schema::hasColumn('order_status_history', 'status_id')) {
                    $table->index(['status_id'], 'idx_status_history_status');
                }
                if (Schema::hasColumn('order_status_history', 'user_id')) {
                    $table->index(['user_id'], 'idx_status_history_user');
                }
            });
        }

        // Design assets indexes - skip, table may not exist

        // Shopping cart indexes - skip, table may not exist

        // Audit logs indexes for security monitoring
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('audit_logs', 'user_id') && Schema::hasColumn('audit_logs', 'created_at')) {
                    $table->index(['user_id', 'created_at'], 'idx_audit_logs_user_date');
                }
                if (Schema::hasColumn('audit_logs', 'action')) {
                    $table->index(['action'], 'idx_audit_logs_action');
                }
                if (Schema::hasColumn('audit_logs', 'entity_type')) {
                    $table->index(['entity_type'], 'idx_audit_logs_entity_type');
                }
                if (Schema::hasColumn('audit_logs', 'ip_address')) {
                    $table->index(['ip_address'], 'idx_audit_logs_ip');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all performance indexes
        
        // Chat system indexes
        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                try { $table->dropIndex('idx_conversations_participants'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_conversations_last_message'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_conversations_status'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('chat_messages')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                try { $table->dropIndex('idx_messages_conversation_time'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_messages_sender'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_messages_read_status'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('online_users')) {
            Schema::table('online_users', function (Blueprint $table) {
                try { $table->dropIndex('idx_online_users_last_seen'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_online_users_status'); } catch (\Throwable $e) {}
            });
        }

        // User indexes
        // Skip - no indexes were added for users table

        // Order system indexes
        if (Schema::hasTable('customer_orders')) {
            Schema::table('customer_orders', function (Blueprint $table) {
                try { $table->dropIndex('idx_orders_customer_date'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_orders_enterprise'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_orders_status'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_orders_date_requested'); } catch (\Throwable $e) {}
            });
        }

        // Product system indexes
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                try { $table->dropIndex('idx_products_enterprise_active'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_products_price'); } catch (\Exception $e) {}
            });
        }

        // Enterprise indexes - skip

        // Customization system indexes - skip

        // Pricing system indexes - skip

        

        // Order status tracking indexes
        if (Schema::hasTable('order_status_history')) {
            Schema::table('order_status_history', function (Blueprint $table) {
                try { $table->dropIndex('idx_status_history_order_time'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_status_history_status'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_status_history_user'); } catch (\Exception $e) {}
            });
        }

        // Design assets indexes - skip

        // Shopping cart indexes - skip

        // Audit logs indexes
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                try { $table->dropIndex('idx_audit_logs_user_date'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_audit_logs_action'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_audit_logs_entity_type'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_audit_logs_ip'); } catch (\Exception $e) {}
            });
        }
    }
};
