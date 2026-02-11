<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_orders')) {
            return;
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('customer_orders', function (Blueprint $table) use ($isSqlite) {
            if (! Schema::hasColumn('customer_orders', 'delivery_address')) {
                $col = $table->text('delivery_address')->nullable();
                if (! $isSqlite && Schema::hasColumn('customer_orders', 'contact_email')) {
                    $col->after('contact_email');
                }
            }

            if (! Schema::hasColumn('customer_orders', 'delivery_instructions')) {
                $col = $table->text('delivery_instructions')->nullable();
                if (! $isSqlite) {
                    $col->after('delivery_address');
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_orders')) {
            return;
        }

        Schema::table('customer_orders', function (Blueprint $table) {
            $cols = [];
            foreach (['delivery_address', 'delivery_instructions'] as $c) {
                if (Schema::hasColumn('customer_orders', $c)) {
                    $cols[] = $c;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
