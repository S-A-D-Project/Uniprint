<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_notifications', function (Blueprint $table) {
            $table->uuid('notification_id')->primary();
            $table->uuid('purchase_order_id');
            $table->uuid('recipient_id'); // user_id
            $table->string('notification_type', 50); // 'status_change', 'message', 'file_upload', etc.
            $table->string('title', 255);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('purchase_order_id')->on('customer_orders')->onDelete('cascade');
            $table->foreign('recipient_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_notifications');
    }
};
