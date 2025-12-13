<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add is_active column to enterprises table
        Schema::table('enterprises', function (Blueprint $table) {
            if (!Schema::hasColumn('enterprises', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('address_text');
            }
            if (!Schema::hasColumn('enterprises', 'category')) {
                $table->string('category')->default('General')->after('enterprise_name');
            }
        });

        // Add category column to services table if it doesn't exist
        if (!Schema::hasColumn('services', 'category')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('category')->default('General')->after('service_name');
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
