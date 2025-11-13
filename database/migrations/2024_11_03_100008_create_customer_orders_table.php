<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_orders', function (Blueprint $table) {
            $table->uuid('purchase_order_id')->primary(); // Kept original name for compatibility
            $table->uuid('customer_id');
            $table->uuid('enterprise_id');
            $table->string('purpose', 255);
            $table->string('order_no', 50)->unique();
            $table->string('official_receipt_no', 50)->nullable();
            $table->date('date_requested');
            $table->date('delivery_date');
            $table->decimal('shipping_fee', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('user_id')->on('users')->onDelete('restrict');
            $table->foreign('enterprise_id')->references('enterprise_id')->on('enterprises')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_orders');
    }
};
