<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_extension_requests')) {
            return;
        }

        Schema::create('order_extension_requests', function (Blueprint $table) {
            $table->uuid('request_id')->primary();
            $table->uuid('purchase_order_id');
            $table->uuid('enterprise_id');
            $table->uuid('customer_id');
            $table->uuid('requested_by')->nullable();

            $table->integer('requested_days')->default(1);
            $table->date('proposed_due_date')->nullable();
            $table->string('message', 500)->nullable();

            $table->string('status', 20)->default('pending');
            $table->uuid('responded_by')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();

            $table->index('purchase_order_id');
            $table->index(['customer_id', 'status']);
            $table->index(['enterprise_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_extension_requests');
    }
};
