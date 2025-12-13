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
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('review_id')->primary();
            $table->uuid('service_id');
            $table->uuid('customer_id');
            $table->integer('rating')->check('rating >= 1 AND rating <= 5');
            $table->text('comment')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
            $table->foreign('customer_id')->references('user_id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('service_id');
            $table->index('customer_id');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
