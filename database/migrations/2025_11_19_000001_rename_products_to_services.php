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
        // Check if products table exists (for fresh databases, it won't)
        if (Schema::hasTable('products')) {
            // Rename products table to services
            Schema::rename('products', 'services');

            // Rename columns in services table
            Schema::table('services', function (Blueprint $table) {
                $table->renameColumn('product_id', 'service_id');
                $table->renameColumn('product_name', 'service_name');
            });
        } else {
            // For fresh databases, the products table was never created
            // The services table should already exist with correct column names
            // This migration is a no-op in this case
        }

        // Update customization_options table - rename product_id to service_id (only if it has product_id)
        if (Schema::hasColumn('customization_options', 'product_id')) {
            Schema::table('customization_options', function (Blueprint $table) {
                // Drop the foreign key first
                $table->dropForeign(['product_id']);
            });

            Schema::table('customization_options', function (Blueprint $table) {
                $table->renameColumn('product_id', 'service_id');
                // Re-add the foreign key with new column name
                $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
            });
        }

        // Update order_items table - rename product_id to service_id (only if it has product_id)
        if (Schema::hasColumn('order_items', 'product_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                // Drop the foreign key first
                $table->dropForeign(['product_id']);
            });

            Schema::table('order_items', function (Blueprint $table) {
                $table->renameColumn('product_id', 'service_id');
                // Re-add the foreign key with new column name
                $table->foreign('service_id')->references('service_id')->on('services')->onDelete('restrict');
            });
        }

        // Update pricing_rules table if it has product_id references
        if (Schema::hasColumn('pricing_rules', 'product_id')) {
            Schema::table('pricing_rules', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });

            Schema::table('pricing_rules', function (Blueprint $table) {
                $table->renameColumn('product_id', 'service_id');
                $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
            });
        }

        // Update order_design_files table if it has product_id references
        if (Schema::hasColumn('order_design_files', 'product_id')) {
            Schema::table('order_design_files', function (Blueprint $table) {
                if (DB::getSchemaBuilder()->hasColumn('order_design_files', 'product_id')) {
                    $table->renameColumn('product_id', 'service_id');
                }
            });
        }

        // Update any audit logs or other tables that reference products
        if (Schema::hasTable('audit_logs')) {
            // Update audit log descriptions
            DB::table('audit_logs')
                ->where('description', 'like', '%product%')
                ->update([
                    'description' => DB::raw("REPLACE(description, 'product', 'service')")
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the audit logs update
        if (Schema::hasTable('audit_logs')) {
            DB::table('audit_logs')
                ->where('description', 'like', '%service%')
                ->update([
                    'description' => DB::raw("REPLACE(description, 'service', 'product')")
                ]);
        }

        // Reverse order_design_files if applicable
        if (Schema::hasColumn('order_design_files', 'service_id')) {
            Schema::table('order_design_files', function (Blueprint $table) {
                $table->renameColumn('service_id', 'product_id');
            });
        }

        // Reverse pricing_rules
        if (Schema::hasColumn('pricing_rules', 'service_id')) {
            Schema::table('pricing_rules', function (Blueprint $table) {
                $table->dropForeign(['service_id']);
            });

            Schema::table('pricing_rules', function (Blueprint $table) {
                $table->renameColumn('service_id', 'product_id');
                $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            });
        }

        // Reverse order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->renameColumn('service_id', 'product_id');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('restrict');
        });

        // Reverse customization_options
        Schema::table('customization_options', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        Schema::table('customization_options', function (Blueprint $table) {
            $table->renameColumn('service_id', 'product_id');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });

        // Rename columns in services table back to products
        Schema::table('services', function (Blueprint $table) {
            $table->renameColumn('service_id', 'product_id');
            $table->renameColumn('service_name', 'product_name');
        });

        // Rename services table back to products
        Schema::rename('services', 'products');
    }
};
