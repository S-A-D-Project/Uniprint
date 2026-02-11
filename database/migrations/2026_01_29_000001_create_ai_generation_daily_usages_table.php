<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generation_daily_usages', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->date('usage_date');
            $table->unsignedInteger('generation_count')->default(0);
            $table->timestamps();

            $table->primary(['user_id', 'usage_date']);
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generation_daily_usages');
    }
};
