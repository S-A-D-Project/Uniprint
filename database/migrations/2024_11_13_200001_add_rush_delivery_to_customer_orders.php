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
        if (! Schema::hasTable('customer_orders')) {
            return;
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('customer_orders', function (Blueprint $table) use ($isSqlite) {
            // Add rush delivery columns
            if (! Schema::hasColumn('customer_orders', 'rush_fee')) {
                $col = $table->decimal('rush_fee', 10, 2)->default(0);
                if (! $isSqlite) {
                    $col->after('shipping_fee');
                }
            }

            if (! Schema::hasColumn('customer_orders', 'rush_option')) {
                $col = $isSqlite
                    ? $table->string('rush_option')
                    : $table->enum('rush_option', ['standard', 'express', 'rush', 'same_day']);
                $col->default('standard');
                if (! $isSqlite) {
                    $col->after('rush_fee');
                }
            }

            if (! Schema::hasColumn('customer_orders', 'pickup_date')) {
                $col = $table->timestamp('pickup_date')->nullable();
                if (! $isSqlite) {
                    $col->after('delivery_date');
                }
            }
            
            // Add contact information columns
            if (! Schema::hasColumn('customer_orders', 'contact_name')) {
                $col = $table->string('contact_name', 255)->nullable();
                if (! $isSqlite) {
                    $col->after('rush_option');
                }
            }

            if (! Schema::hasColumn('customer_orders', 'contact_phone')) {
                $col = $table->string('contact_phone', 20)->nullable();
                if (! $isSqlite) {
                    $col->after('contact_name');
                }
            }

            if (! Schema::hasColumn('customer_orders', 'contact_email')) {
                $col = $table->string('contact_email', 255)->nullable();
                if (! $isSqlite) {
                    $col->after('contact_phone');
                }
            }
            
            // Add payment information
            if (! Schema::hasColumn('customer_orders', 'payment_method')) {
                $col = $isSqlite
                    ? $table->string('payment_method')
                    : $table->enum('payment_method', ['gcash', 'cash']);
                $col->default('cash');
                if (! $isSqlite) {
                    $col->after('contact_email');
                }
            }

            if (! Schema::hasColumn('customer_orders', 'payment_status')) {
                $col = $isSqlite
                    ? $table->string('payment_status')
                    : $table->enum('payment_status', ['pending', 'paid', 'failed']);
                $col->default('pending');
                if (! $isSqlite) {
                    $col->after('payment_method');
                }
            }
            
            // Add tax column
            if (! Schema::hasColumn('customer_orders', 'tax')) {
                $col = $table->decimal('tax', 10, 2)->default(0);
                if (! $isSqlite) {
                    $col->after('subtotal');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropColumn([
                'rush_fee',
                'rush_option',
                'pickup_date',
                'contact_name',
                'contact_phone',
                'contact_email',
                'payment_method',
                'payment_status',
                'tax'
            ]);
        });
    }
};
