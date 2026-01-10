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
            if (! Schema::hasColumn('customer_orders', 'fulfillment_method')) {
                $col = $isSqlite
                    ? $table->string('fulfillment_method')
                    : $table->enum('fulfillment_method', ['pickup', 'delivery']);
                $col->default('pickup');

                if (! $isSqlite && Schema::hasColumn('customer_orders', 'pickup_date')) {
                    $col->after('pickup_date');
                }
            }

            if (! Schema::hasColumn('customer_orders', 'requested_fulfillment_date')) {
                $col = $table->date('requested_fulfillment_date')->nullable();
                if (! $isSqlite) {
                    $col->after('fulfillment_method');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            if (Schema::hasColumn('customer_orders', 'requested_fulfillment_date')) {
                $table->dropColumn('requested_fulfillment_date');
            }
            if (Schema::hasColumn('customer_orders', 'fulfillment_method')) {
                $table->dropColumn('fulfillment_method');
            }
        });
    }
};
