<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add shop_logo column to enterprises table
     */
    public function up(): void
    {
        if (! Schema::hasTable('enterprises')) {
            return;
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('enterprises', function (Blueprint $table) use ($isSqlite) {
            if (! Schema::hasColumn('enterprises', 'shop_logo')) {
                $col = $table->string('shop_logo', 255)->nullable();
                if (! $isSqlite) {
                    $col->after('name');
                }
            }
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        if (! Schema::hasTable('enterprises')) {
            return;
        }

        Schema::table('enterprises', function (Blueprint $table) {
            if (Schema::hasColumn('enterprises', 'shop_logo')) {
                $table->dropColumn('shop_logo');
            }
        });
    }
};
