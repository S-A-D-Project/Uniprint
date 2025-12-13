<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create rating and review system tables.
     */
    public function up(): void
    {
        // Ratings table
        Schema::create('ratings', function (Blueprint $table) {
            $table->uuid('rating_id')->primary();
            $table->uuid('user_id');
            $table->uuid('enterprise_id');
            $table->uuid('service_id')->nullable(); // Can rate enterprise or specific service
            $table->uuid('order_id')->nullable(); // Link to order for verification
            $table->integer('rating')->unsigned()->comment('Rating from 1 to 5 stars');
            $table->text('review_text')->nullable();
            $table->boolean('is_verified')->default(false); // Only customers who ordered can rate
            $table->boolean('is_approved')->default(true); // For moderation
            $table->json('helpful_votes')->nullable(); // Track helpful votes {"helpful": 5, "not_helpful": 2}
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['enterprise_id']);
            $table->index(['service_id']);
            $table->index(['user_id']);
            $table->index(['rating']);
            $table->index(['is_approved']);
            
            // Foreign key constraints
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('enterprise_id')->references('enterprise_id')->on('enterprises')->onDelete('cascade');
            $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
            $table->foreign('order_id')->references('purchase_order_id')->on('customer_orders')->onDelete('set null');
            
            // Unique constraint - one rating per user per service/enterprise
            $table->unique(['user_id', 'enterprise_id', 'service_id'], 'unique_user_rating');
        });

        // Rating summaries table for performance
        Schema::create('rating_summaries', function (Blueprint $table) {
            $table->uuid('summary_id')->primary();
            $table->uuid('enterprise_id');
            $table->uuid('service_id')->nullable();
            $table->decimal('average_rating', 3, 2)->default(0); // e.g., 4.25
            $table->integer('total_ratings')->default(0);
            $table->json('rating_distribution')->nullable(); // {"1": 5, "2": 10, "3": 20, "4": 30, "5": 35}
            $table->timestamp('last_calculated')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['enterprise_id']);
            $table->index(['service_id']);
            $table->index(['average_rating']);
            
            // Foreign key constraints
            $table->foreign('enterprise_id')->references('enterprise_id')->on('enterprises')->onDelete('cascade');
            $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
            
            // Unique constraint
            $table->unique(['enterprise_id', 'service_id'], 'unique_rating_summary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_summaries');
        Schema::dropIfExists('ratings');
    }
};
