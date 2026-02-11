<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('enterprises')) {
            return;
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('enterprises', function (Blueprint $table) use ($isSqlite) {
            if (! Schema::hasColumn('enterprises', 'checkout_payment_methods')) {
                $col = $table->text('checkout_payment_methods')->nullable();
                if (! $isSqlite) {
                    $col->after('contact_number');
                }
            }

            if (! Schema::hasColumn('enterprises', 'checkout_fulfillment_methods')) {
                $col = $table->text('checkout_fulfillment_methods')->nullable();
                if (! $isSqlite) {
                    $col->after('checkout_payment_methods');
                }
            }

            if (! Schema::hasColumn('enterprises', 'checkout_rush_options')) {
                $col = $table->text('checkout_rush_options')->nullable();
                if (! $isSqlite) {
                    $col->after('checkout_fulfillment_methods');
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('enterprises')) {
            return;
        }

        Schema::table('enterprises', function (Blueprint $table) {
            $cols = [];
            foreach (['checkout_payment_methods', 'checkout_fulfillment_methods', 'checkout_rush_options'] as $c) {
                if (Schema::hasColumn('enterprises', $c)) {
                    $cols[] = $c;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
