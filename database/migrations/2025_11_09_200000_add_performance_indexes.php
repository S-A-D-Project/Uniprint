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
        Schema::table('conversations', function (Blueprint $table) {
            $table->index(['customer_id', 'business_id'], 'idx_conversations_participants');
            $table->index(['last_message_at'], 'idx_conversations_last_message');
            $table->index(['status'], 'idx_conversations_status');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index(['conversation_id', 'created_at'], 'idx_messages_conversation_time');
            $table->index(['sender_id'], 'idx_messages_sender');
            $table->index(['is_read'], 'idx_messages_read_status');
        });

        Schema::table('online_users', function (Blueprint $table) {
            $table->index(['last_seen_at'], 'idx_online_users_last_seen');
            $table->index(['status'], 'idx_online_users_status');
        });

        // User and authentication indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role_type'], 'idx_users_role_type');
            $table->index(['is_active'], 'idx_users_active');
            $table->index(['email'], 'idx_users_email');
            $table->index(['username'], 'idx_users_username');
        });

        // Order system indexes
        if (Schema::hasTable('customer_orders')) {
            Schema::table('customer_orders', function (Blueprint $table) {
                $table->index(['customer_id', 'created_at'], 'idx_orders_customer_date');
                $table->index(['enterprise_id'], 'idx_orders_enterprise');
                $table->index(['current_status'], 'idx_orders_status');
                $table->index(['date_requested'], 'idx_orders_date_requested');
            });
        }

        // Product system indexes
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['enterprise_id', 'is_active'], 'idx_products_enterprise_active');
                $table->index(['category'], 'idx_products_category');
                $table->index(['base_price'], 'idx_products_price');
            });
        }

        // Enterprise indexes
        if (Schema::hasTable('enterprises')) {
            Schema::table('enterprises', function (Blueprint $table) {
                $table->index(['category'], 'idx_enterprises_category');
                $table->index(['is_active'], 'idx_enterprises_active');
            });
        }

        // Customization system indexes
        if (Schema::hasTable('customization_options')) {
            Schema::table('customization_options', function (Blueprint $table) {
                $table->index(['group_id'], 'idx_customization_options_group');
                $table->index(['is_active'], 'idx_customization_options_active');
            });
        }

        if (Schema::hasTable('customization_groups')) {
            Schema::table('customization_groups', function (Blueprint $table) {
                $table->index(['product_id'], 'idx_customization_groups_product');
                $table->index(['is_required'], 'idx_customization_groups_required');
            });
        }

        // Pricing system indexes
        if (Schema::hasTable('pricing_rules')) {
            Schema::table('pricing_rules', function (Blueprint $table) {
                $table->index(['enterprise_id', 'is_active'], 'idx_pricing_rules_enterprise_active');
                $table->index(['product_id'], 'idx_pricing_rules_product');
                $table->index(['rule_type'], 'idx_pricing_rules_type');
                $table->index(['priority'], 'idx_pricing_rules_priority');
            });
        }

        // Inventory system indexes
        if (Schema::hasTable('inventory_materials')) {
            Schema::table('inventory_materials', function (Blueprint $table) {
                $table->index(['enterprise_id'], 'idx_inventory_materials_enterprise');
                $table->index(['current_stock'], 'idx_inventory_materials_stock');
                $table->index(['minimum_stock'], 'idx_inventory_materials_min_stock');
            });
        }

        // Order status tracking indexes
        if (Schema::hasTable('order_status_history')) {
            Schema::table('order_status_history', function (Blueprint $table) {
                $table->index(['purchase_order_id', 'timestamp'], 'idx_status_history_order_time');
                $table->index(['status_id'], 'idx_status_history_status');
                $table->index(['staff_id'], 'idx_status_history_staff');
            });
        }

        // Design assets indexes
        if (Schema::hasTable('design_assets')) {
            Schema::table('design_assets', function (Blueprint $table) {
                $table->index(['user_id'], 'idx_design_assets_user');
                $table->index(['asset_type'], 'idx_design_assets_type');
                $table->index(['is_public'], 'idx_design_assets_public');
                $table->index(['created_at'], 'idx_design_assets_created');
            });
        }

        // Shopping cart indexes
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->index(['user_id'], 'idx_cart_items_user');
                $table->index(['product_id'], 'idx_cart_items_product');
                $table->index(['created_at'], 'idx_cart_items_created');
            });
        }

        // Audit logs indexes for security monitoring
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'idx_audit_logs_user_date');
                $table->index(['action'], 'idx_audit_logs_action');
                $table->index(['entity_type'], 'idx_audit_logs_entity_type');
                $table->index(['ip_address'], 'idx_audit_logs_ip');
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
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('idx_conversations_participants');
            $table->dropIndex('idx_conversations_last_message');
            $table->dropIndex('idx_conversations_status');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('idx_messages_conversation_time');
            $table->dropIndex('idx_messages_sender');
            $table->dropIndex('idx_messages_read_status');
        });

        Schema::table('online_users', function (Blueprint $table) {
            $table->dropIndex('idx_online_users_last_seen');
            $table->dropIndex('idx_online_users_status');
        });

        // User indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_type');
            $table->dropIndex('idx_users_active');
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_username');
        });

        // Order system indexes
        if (Schema::hasTable('customer_orders')) {
            Schema::table('customer_orders', function (Blueprint $table) {
                $table->dropIndex('idx_orders_customer_date');
                $table->dropIndex('idx_orders_enterprise');
                $table->dropIndex('idx_orders_status');
                $table->dropIndex('idx_orders_date_requested');
            });
        }

        // Product system indexes
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('idx_products_enterprise_active');
                $table->dropIndex('idx_products_category');
                $table->dropIndex('idx_products_price');
            });
        }

        // Enterprise indexes
        if (Schema::hasTable('enterprises')) {
            Schema::table('enterprises', function (Blueprint $table) {
                $table->dropIndex('idx_enterprises_category');
                $table->dropIndex('idx_enterprises_active');
            });
        }

        // Customization system indexes
        if (Schema::hasTable('customization_options')) {
            Schema::table('customization_options', function (Blueprint $table) {
                $table->dropIndex('idx_customization_options_group');
                $table->dropIndex('idx_customization_options_active');
            });
        }

        if (Schema::hasTable('customization_groups')) {
            Schema::table('customization_groups', function (Blueprint $table) {
                $table->dropIndex('idx_customization_groups_product');
                $table->dropIndex('idx_customization_groups_required');
            });
        }

        // Pricing system indexes
        if (Schema::hasTable('pricing_rules')) {
            Schema::table('pricing_rules', function (Blueprint $table) {
                $table->dropIndex('idx_pricing_rules_enterprise_active');
                $table->dropIndex('idx_pricing_rules_product');
                $table->dropIndex('idx_pricing_rules_type');
                $table->dropIndex('idx_pricing_rules_priority');
            });
        }

        // Inventory system indexes
        if (Schema::hasTable('inventory_materials')) {
            Schema::table('inventory_materials', function (Blueprint $table) {
                $table->dropIndex('idx_inventory_materials_enterprise');
                $table->dropIndex('idx_inventory_materials_stock');
                $table->dropIndex('idx_inventory_materials_min_stock');
            });
        }

        // Order status tracking indexes
        if (Schema::hasTable('order_status_history')) {
            Schema::table('order_status_history', function (Blueprint $table) {
                $table->dropIndex('idx_status_history_order_time');
                $table->dropIndex('idx_status_history_status');
                $table->dropIndex('idx_status_history_staff');
            });
        }

        // Design assets indexes
        if (Schema::hasTable('design_assets')) {
            Schema::table('design_assets', function (Blueprint $table) {
                $table->dropIndex('idx_design_assets_user');
                $table->dropIndex('idx_design_assets_type');
                $table->dropIndex('idx_design_assets_public');
                $table->dropIndex('idx_design_assets_created');
            });
        }

        // Shopping cart indexes
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropIndex('idx_cart_items_user');
                $table->dropIndex('idx_cart_items_product');
                $table->dropIndex('idx_cart_items_created');
            });
        }

        // Audit logs indexes
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex('idx_audit_logs_user_date');
                $table->dropIndex('idx_audit_logs_action');
                $table->dropIndex('idx_audit_logs_entity_type');
                $table->dropIndex('idx_audit_logs_ip');
            });
        }
    }
};
