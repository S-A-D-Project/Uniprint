<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customization_options', function (Blueprint $table) {
            $table->uuid('option_id')->primary();
            $table->uuid('service_id');
            $table->string('option_name', 100);
            $table->string('option_type', 50); // e.g., 'Size', 'Color', 'Paper Type'
            $table->decimal('price_modifier', 10, 2)->default(0.00);
            $table->timestamps();
            
            $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customization_options');
    }
};
