<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_refunds')) {
            return;
        }

        Schema::create('order_refunds', function (Blueprint $table) {
            $table->uuid('refund_id')->primary();
            $table->uuid('purchase_order_id');
            $table->string('status', 20)->default('refunded');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('reason', 255)->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index('purchase_order_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_refunds');
    }
};
