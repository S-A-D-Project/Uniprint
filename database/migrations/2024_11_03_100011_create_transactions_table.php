<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('transaction_id')->primary();
            $table->uuid('purchase_order_id');
            $table->string('payment_method', 50);
            $table->string('transaction_ref', 255)->unique();
            $table->decimal('amount', 10, 2);
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamps();
            
            $table->foreign('purchase_order_id')->references('purchase_order_id')->on('customer_orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
