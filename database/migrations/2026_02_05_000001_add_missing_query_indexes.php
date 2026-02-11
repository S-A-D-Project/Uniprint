<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'pgsql') {
                return DB::table('pg_indexes')
                    ->where('tablename', $table)
                    ->where('indexname', $indexName)
                    ->exists();
            }

            if ($driver === 'mysql' || $driver === 'mariadb') {
                return DB::table('information_schema.statistics')
                    ->where('table_schema', DB::raw('DATABASE()'))
                    ->where('table_name', $table)
                    ->where('index_name', $indexName)
                    ->exists();
            }

            if ($driver === 'sqlite') {
                $rows = DB::select("PRAGMA index_list('" . str_replace("'", "''", $table) . "')");
                foreach ($rows as $row) {
                    if (($row->name ?? null) === $indexName) {
                        return true;
                    }
                }
                return false;
            }

            return false;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function addIndex(string $table, string $indexName, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $col) {
            if (!Schema::hasColumn($table, $col)) {
                return;
            }
        }

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
            $t->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if (!$this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($indexName) {
            $t->dropIndex($indexName);
        });
    }

    public function up(): void
    {
        $this->addIndex('customer_orders', 'idx_orders_enterprise_date', ['enterprise_id', 'created_at']);
        $this->addIndex('customer_orders', 'idx_orders_customer', ['customer_id']);
        $this->addIndex('customer_orders', 'idx_orders_enterprise', ['enterprise_id']);

        $this->addIndex('order_items', 'idx_order_items_order', ['purchase_order_id']);
        $this->addIndex('order_items', 'idx_order_items_service', ['service_id']);

        $this->addIndex('order_item_customizations', 'idx_oic_item', ['order_item_id']);
        $this->addIndex('order_item_customizations', 'idx_oic_option', ['option_id']);

        $this->addIndex('order_notifications', 'idx_order_notif_rec', ['recipient_id']);
        $this->addIndex('order_notifications', 'idx_order_notif_rec_read', ['recipient_id', 'is_read', 'created_at']);

        $this->addIndex('services', 'idx_services_active', ['is_active']);
        $this->addIndex('services', 'idx_services_ent_active', ['enterprise_id', 'is_active']);

        $this->addIndex('enterprises', 'idx_enterprises_active', ['is_active']);
        $this->addIndex('enterprises', 'idx_enterprises_active_verified', ['is_active', 'is_verified']);
        $this->addIndex('enterprises', 'idx_enterprises_category', ['category']);

        $this->addIndex('roles', 'idx_roles_user', ['user_id']);
        $this->addIndex('roles', 'idx_roles_role_type', ['role_type_id']);

        $this->addIndex('payments', 'idx_payments_order', ['purchase_order_id']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('payments', 'idx_payments_order');

        $this->dropIndexIfExists('roles', 'idx_roles_role_type');
        $this->dropIndexIfExists('roles', 'idx_roles_user');

        $this->dropIndexIfExists('enterprises', 'idx_enterprises_category');
        $this->dropIndexIfExists('enterprises', 'idx_enterprises_active_verified');
        $this->dropIndexIfExists('enterprises', 'idx_enterprises_active');

        $this->dropIndexIfExists('services', 'idx_services_ent_active');
        $this->dropIndexIfExists('services', 'idx_services_active');

        $this->dropIndexIfExists('order_notifications', 'idx_order_notif_rec_read');
        $this->dropIndexIfExists('order_notifications', 'idx_order_notif_rec');

        $this->dropIndexIfExists('order_item_customizations', 'idx_oic_option');
        $this->dropIndexIfExists('order_item_customizations', 'idx_oic_item');

        $this->dropIndexIfExists('order_items', 'idx_order_items_service');
        $this->dropIndexIfExists('order_items', 'idx_order_items_order');

        $this->dropIndexIfExists('customer_orders', 'idx_orders_enterprise');
        $this->dropIndexIfExists('customer_orders', 'idx_orders_customer');
        $this->dropIndexIfExists('customer_orders', 'idx_orders_enterprise_date');
    }
};
