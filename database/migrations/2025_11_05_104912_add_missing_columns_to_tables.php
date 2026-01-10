<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add is_active column to enterprises table
        if (Schema::hasTable('enterprises')) {
            $isSqlite = DB::connection()->getDriverName() === 'sqlite';

            Schema::table('enterprises', function (Blueprint $table) use ($isSqlite) {
                if (! Schema::hasColumn('enterprises', 'is_active')) {
                    if (! $isSqlite && Schema::hasColumn('enterprises', 'address')) {
                        $table->boolean('is_active')->default(true)->after('address');
                    } else {
                        $table->boolean('is_active')->default(true);
                    }
                }
                if (! Schema::hasColumn('enterprises', 'category')) {
                    if (! $isSqlite && Schema::hasColumn('enterprises', 'name')) {
                        $table->string('category')->default('General')->after('name');
                    } else {
                        $table->string('category')->default('General');
                    }
                }
                if (! Schema::hasColumn('enterprises', 'email')) {
                    if (! $isSqlite && Schema::hasColumn('enterprises', 'contact_number')) {
                        $table->string('email', 255)->nullable()->after('contact_number');
                    } else {
                        $table->string('email', 255)->nullable();
                    }
                }
            });
        }

        // Add category column to services table if it doesn't exist
        if (Schema::hasTable('services') && ! Schema::hasColumn('services', 'category')) {
            $isSqlite = DB::connection()->getDriverName() === 'sqlite';

            Schema::table('services', function (Blueprint $table) use ($isSqlite) {
                $col = $table->string('category')->default('General');
                if (! $isSqlite) {
                    $col->after('service_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove columns from enterprises table
        Schema::table('enterprises', function (Blueprint $table) {
            if (Schema::hasColumn('enterprises', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('enterprises', 'category')) {
                $table->dropColumn('category');
            }
        });

        // Remove category from services table
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
