<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create saved_services table and related tables
     */
    public function up(): void
    {
        // Saved services table
        Schema::create('saved_services', function (Blueprint $table) {
            $table->uuid('saved_service_id')->primary();
            $table->uuid('user_id');
            $table->uuid('service_id');
            $table->integer('quantity')->default(1);
            $table->json('customizations')->nullable();
            $table->text('special_instructions')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamp('saved_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['service_id']);
            $table->index(['saved_at']);
            
            // Foreign keys
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
        });

        // Saved service customizations pivot table
        Schema::create('saved_service_customizations', function (Blueprint $table) {
            $table->uuid('saved_service_id');
            $table->uuid('option_id');
            $table->integer('quantity')->default(1);
            $table->timestamps();
            
            // Composite primary key
            $table->primary(['saved_service_id', 'option_id']);
            
            // Foreign keys
            $table->foreign('saved_service_id')->references('saved_service_id')->on('saved_services')->onDelete('cascade');
            $table->foreign('option_id')->references('option_id')->on('customization_options')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_service_customizations');
        Schema::dropIfExists('saved_services');
    }
};
