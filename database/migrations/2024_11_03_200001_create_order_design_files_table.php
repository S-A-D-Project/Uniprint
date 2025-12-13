<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_design_files', function (Blueprint $table) {
            $table->uuid('file_id')->primary();
            $table->uuid('purchase_order_id');
            $table->uuid('uploaded_by'); // customer user_id
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 50); // jpg, png, pdf, ai, etc.
            $table->integer('file_size'); // in bytes
            $table->text('design_notes')->nullable();
            $table->integer('version')->default(1);
            $table->boolean('is_approved')->default(false);
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('purchase_order_id')->on('customer_orders')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_design_files');
    }
};
