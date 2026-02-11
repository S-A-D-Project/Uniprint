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
            if (! Schema::hasColumn('enterprises', 'gcash_enabled')) {
                $col = $table->boolean('gcash_enabled')->default(false);
                if (! $isSqlite) {
                    $col->after('checkout_payment_methods');
                }
            }

            if (! Schema::hasColumn('enterprises', 'gcash_instructions')) {
                $col = $table->text('gcash_instructions')->nullable();
                if (! $isSqlite) {
                    $col->after('gcash_enabled');
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
            foreach (['gcash_enabled', 'gcash_instructions'] as $c) {
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
