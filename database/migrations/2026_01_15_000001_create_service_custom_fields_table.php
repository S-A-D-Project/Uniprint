<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_custom_fields', function (Blueprint $table) {
            $table->uuid('field_id')->primary();
            $table->uuid('service_id');
            $table->string('field_label', 150);
            $table->string('placeholder', 255)->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
            $table->index(['service_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_custom_fields');
    }
};
