<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payments')) {
            return;
        }

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('payment_id')->primary();
            $table->uuid('purchase_order_id');
            $table->string('payment_method', 50);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->timestamp('payment_date_time')->useCurrent();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index('purchase_order_id');
            $table->foreign('purchase_order_id')
                ->references('purchase_order_id')
                ->on('customer_orders')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
