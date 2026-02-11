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
            if (! Schema::hasColumn('services', 'supports_rush')) {
                $col = $table->boolean('supports_rush')->default(true);
                if (! $isSqlite && Schema::hasColumn('services', 'downpayment_percent')) {
                    $col->after('downpayment_percent');
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('services')) {
            return;
        }

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'supports_rush')) {
                $table->dropColumn('supports_rush');
            }
        });
    }
};
