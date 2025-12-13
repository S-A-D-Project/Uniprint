<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->uuid('approval_id')->primary(); // Kept original name for compatibility
            $table->uuid('purchase_order_id');
            $table->uuid('user_id')->nullable();
            $table->uuid('status_id');
            $table->text('remarks')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();
            
            $table->foreign('purchase_order_id')->references('purchase_order_id')->on('customer_orders')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('status_id')->references('status_id')->on('statuses')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
    }
};
