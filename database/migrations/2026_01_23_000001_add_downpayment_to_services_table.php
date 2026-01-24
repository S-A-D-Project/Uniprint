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
            if (! Schema::hasColumn('services', 'requires_downpayment')) {
                $col = $table->boolean('requires_downpayment')->default(false);
                if (! $isSqlite) {
                    $col->after('allowed_payment_methods');
                }
            }

            if (! Schema::hasColumn('services', 'downpayment_percent')) {
                $col = $table->decimal('downpayment_percent', 5, 2)->default(0);
                if (! $isSqlite) {
                    $col->after('requires_downpayment');
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
            if (Schema::hasColumn('services', 'downpayment_percent')) {
                $table->dropColumn('downpayment_percent');
            }
            if (Schema::hasColumn('services', 'requires_downpayment')) {
                $table->dropColumn('requires_downpayment');
            }
        });
    }
};
