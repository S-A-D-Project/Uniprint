<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove all VAT-related functionality as enterprises operate exclusively 
     * within Baguio City where VAT does not apply.
     */
    public function up(): void
    {
        if (Schema::hasTable('enterprises') && Schema::hasColumn('enterprises', 'vat_type_id')) {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                // SQLite: drop foreign key using raw PRAGMA
                DB::statement('PRAGMA foreign_keys = OFF;');
                try {
                    // Recreate table without vat_type_id column
                    DB::statement('CREATE TABLE enterprises_new (
                        enterprise_id TEXT PRIMARY KEY,
                        name TEXT NOT NULL,
                        address TEXT NULL,
                        contact_person TEXT NULL,
                        contact_number TEXT NULL,
                        tin_no TEXT NULL,
                        created_at TEXT NULL,
                        updated_at TEXT NULL
                    )');

                    DB::statement('INSERT INTO enterprises_new (enterprise_id, name, address, contact_person, contact_number, tin_no, created_at, updated_at)
                        SELECT enterprise_id, name, address, contact_person, contact_number, tin_no, created_at, updated_at
                        FROM enterprises');

                    DB::statement('DROP TABLE enterprises;');
                    DB::statement('ALTER TABLE enterprises_new RENAME TO enterprises;');
                } finally {
                    DB::statement('PRAGMA foreign_keys = ON;');
                }
            } else {
                Schema::table('enterprises', function (Blueprint $table) {
                    $table->dropForeign(['vat_type_id']);
                    $table->dropColumn('vat_type_id');
                });
            }
        }

        // Drop the vat_types table entirely
        Schema::dropIfExists('vat_types');

        // Remove any VAT-related columns from other tables if they exist
        // Check for VAT columns in transactions table
        if (Schema::hasColumn('transactions', 'vat_amount')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('vat_amount');
            });
        }

        if (Schema::hasColumn('transactions', 'vat_rate')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('vat_rate');
            });
        }

        // Remove VAT columns from customer_orders if they exist
        if (Schema::hasColumn('customer_orders', 'vat_amount')) {
            Schema::table('customer_orders', function (Blueprint $table) {
                $table->dropColumn('vat_amount');
            });
        }

        if (Schema::hasColumn('customer_orders', 'vat_rate')) {
            Schema::table('customer_orders', function (Blueprint $table) {
                $table->dropColumn('vat_rate');
            });
        }

        // Remove VAT columns from order_items if they exist
        if (Schema::hasColumn('order_items', 'vat_amount')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('vat_amount');
            });
        }

        if (Schema::hasColumn('order_items', 'vat_rate')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('vat_rate');
            });
        }

        // Remove VAT columns from products if they exist
        if (Schema::hasColumn('products', 'vat_inclusive')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('vat_inclusive');
            });
        }

        if (Schema::hasColumn('products', 'vat_rate')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('vat_rate');
            });
        }

        // Remove VAT columns from pricing_rules if they exist
        if (Schema::hasColumn('pricing_rules', 'apply_vat')) {
            Schema::table('pricing_rules', function (Blueprint $table) {
                $table->dropColumn('apply_vat');
            });
        }

        if (Schema::hasColumn('pricing_rules', 'vat_rate')) {
            Schema::table('pricing_rules', function (Blueprint $table) {
                $table->dropColumn('vat_rate');
            });
        }
    }

    /**
     * Reverse the migrations.
     * Note: This rollback recreates the basic VAT structure but data will be lost.
     */
    public function down(): void
    {
        // Recreate vat_types table
        Schema::create('vat_types', function (Blueprint $table) {
            $table->uuid('vat_type_id')->primary();
            $table->string('type_name', 50)->unique();
        });

        // Add vat_type_id back to enterprises table
        Schema::table('enterprises', function (Blueprint $table) {
            $table->uuid('vat_type_id')->nullable();
            $table->foreign('vat_type_id')->references('vat_type_id')->on('vat_types')->onDelete('restrict');
        });

        // Note: Other VAT columns are not restored in rollback as they may not have existed originally
        // This migration is designed to be a one-way removal of VAT functionality
    }
};
