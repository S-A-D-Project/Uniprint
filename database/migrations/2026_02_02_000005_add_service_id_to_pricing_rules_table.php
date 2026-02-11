<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pricing_rules')) {
            return;
        }

        if (! Schema::hasColumn('pricing_rules', 'service_id')) {
            Schema::table('pricing_rules', function (Blueprint $table) {
                $table->uuid('service_id')->nullable()->after('enterprise_id');
                $table->index(['enterprise_id', 'service_id'], 'idx_pricing_rules_enterprise_service');
            });

            // Best-effort FK (skip errors on sqlite / existing constraints)
            try {
                Schema::table('pricing_rules', function (Blueprint $table) {
                    $table->foreign('service_id')->references('service_id')->on('services')->nullOnDelete();
                });
            } catch (Throwable $e) {
                // no-op
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pricing_rules')) {
            return;
        }

        if (Schema::hasColumn('pricing_rules', 'service_id')) {
            try {
                Schema::table('pricing_rules', function (Blueprint $table) {
                    try { $table->dropForeign(['service_id']); } catch (Throwable $e) {}
                });
            } catch (Throwable $e) {
                // no-op
            }

            Schema::table('pricing_rules', function (Blueprint $table) {
                try { $table->dropIndex('idx_pricing_rules_enterprise_service'); } catch (Throwable $e) {}
                $table->dropColumn('service_id');
            });
        }
    }
};
