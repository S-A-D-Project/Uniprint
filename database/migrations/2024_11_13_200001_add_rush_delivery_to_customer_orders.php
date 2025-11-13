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
        Schema::table('customer_orders', function (Blueprint $table) {
            // Add rush delivery columns
            $table->decimal('rush_fee', 10, 2)->default(0)->after('shipping_fee');
            $table->enum('rush_option', ['standard', 'express', 'rush', 'same_day'])->default('standard')->after('rush_fee');
            $table->timestamp('pickup_date')->nullable()->after('delivery_date');
            
            // Add contact information columns
            $table->string('contact_name', 255)->nullable()->after('rush_option');
            $table->string('contact_phone', 20)->nullable()->after('contact_name');
            $table->string('contact_email', 255)->nullable()->after('contact_phone');
            
            // Add payment information
            $table->enum('payment_method', ['gcash', 'cash'])->default('cash')->after('contact_email');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending')->after('payment_method');
            
            // Add tax column
            $table->decimal('tax', 10, 2)->default(0)->after('subtotal');
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
