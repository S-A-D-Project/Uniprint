<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_images', function (Blueprint $table) {
            $table->uuid('image_id')->primary();
            $table->uuid('service_id');
            $table->string('image_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
            $table->index(['service_id', 'sort_order']);
            $table->index(['service_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_images');
    }
};
