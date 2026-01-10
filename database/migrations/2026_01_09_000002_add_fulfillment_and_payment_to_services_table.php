<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('services')) {
            return;
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('services', function (Blueprint $table) use ($isSqlite) {
            if (! Schema::hasColumn('services', 'fulfillment_type')) {
                $col = $isSqlite
                    ? $table->string('fulfillment_type')
                    : $table->enum('fulfillment_type', ['pickup', 'delivery', 'both']);
                $col->default('pickup');

                if (! $isSqlite && Schema::hasColumn('services', 'category')) {
                    $col->after('category');
                }
            }

            if (! Schema::hasColumn('services', 'allowed_payment_methods')) {
                $col = $table->text('allowed_payment_methods')->nullable();
                if (! $isSqlite) {
                    $col->after('fulfillment_type');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'allowed_payment_methods')) {
                $table->dropColumn('allowed_payment_methods');
            }
            if (Schema::hasColumn('services', 'fulfillment_type')) {
                $table->dropColumn('fulfillment_type');
            }
        });
    }
};
