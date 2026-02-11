<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('services')) {
            return;
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('services', function (Blueprint $table) use ($isSqlite) {
            if (!Schema::hasColumn('services', 'supports_custom_size')) {
                $col = $table->boolean('supports_custom_size')->default(false);
                if (!$isSqlite) {
                    $col->after('is_active');
                }
            }

            if (!Schema::hasColumn('services', 'custom_size_unit')) {
                $col = $table->string('custom_size_unit', 20)->nullable();
                if (!$isSqlite) {
                    $col->after('supports_custom_size');
                }
            }

            if (!Schema::hasColumn('services', 'custom_size_min_width')) {
                $col = $table->decimal('custom_size_min_width', 10, 2)->nullable();
                if (!$isSqlite) {
                    $col->after('custom_size_unit');
                }
            }
            if (!Schema::hasColumn('services', 'custom_size_max_width')) {
                $col = $table->decimal('custom_size_max_width', 10, 2)->nullable();
                if (!$isSqlite) {
                    $col->after('custom_size_min_width');
                }
            }
            if (!Schema::hasColumn('services', 'custom_size_min_height')) {
                $col = $table->decimal('custom_size_min_height', 10, 2)->nullable();
                if (!$isSqlite) {
                    $col->after('custom_size_max_width');
                }
            }
            if (!Schema::hasColumn('services', 'custom_size_max_height')) {
                $col = $table->decimal('custom_size_max_height', 10, 2)->nullable();
                if (!$isSqlite) {
                    $col->after('custom_size_min_height');
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('services')) {
            return;
        }

        Schema::table('services', function (Blueprint $table) {
            $cols = [
                'supports_custom_size',
                'custom_size_unit',
                'custom_size_min_width',
                'custom_size_max_width',
                'custom_size_min_height',
                'custom_size_max_height',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('services', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
