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
            if (! Schema::hasColumn('enterprises', 'is_verified')) {
                $col = $table->boolean('is_verified')->default(false);
                if (! $isSqlite && Schema::hasColumn('enterprises', 'is_active')) {
                    $col->after('is_active');
                }
            }

            if (! Schema::hasColumn('enterprises', 'verified_at')) {
                $col = $table->timestamp('verified_at')->nullable();
                if (! $isSqlite) {
                    $col->after('is_verified');
                }
            }

            if (! Schema::hasColumn('enterprises', 'verified_by_user_id')) {
                $col = $table->uuid('verified_by_user_id')->nullable()->index();
                if (! $isSqlite) {
                    $col->after('verified_at');
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
            if (Schema::hasColumn('enterprises', 'verified_by_user_id')) {
                $cols[] = 'verified_by_user_id';
            }
            if (Schema::hasColumn('enterprises', 'verified_at')) {
                $cols[] = 'verified_at';
            }
            if (Schema::hasColumn('enterprises', 'is_verified')) {
                $cols[] = 'is_verified';
            }
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
