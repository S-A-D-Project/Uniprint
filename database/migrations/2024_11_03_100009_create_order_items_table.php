<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('item_id')->primary();
            $table->uuid('purchase_order_id'); // FK to customer_orders
            $table->uuid('service_id');
            $table->text('item_description');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->timestamps();
            
            $table->foreign('purchase_order_id')->references('purchase_order_id')->on('customer_orders')->onDelete('cascade');
            $table->foreign('service_id')->references('service_id')->on('services')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
