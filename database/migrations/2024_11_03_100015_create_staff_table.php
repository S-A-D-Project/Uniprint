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
        Schema::create('staff', function (Blueprint $table) {
            $table->uuid('staff_id')->primary();
            $table->uuid('user_id');
            $table->uuid('enterprise_id');
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('enterprise_id')->references('enterprise_id')->on('enterprises')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('enterprise_id');
            $table->unique(['user_id', 'enterprise_id'], 'unique_user_enterprise');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
