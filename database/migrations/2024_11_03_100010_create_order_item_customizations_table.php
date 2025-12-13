<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_customizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_item_id');
            $table->uuid('option_id');
            $table->decimal('price_snapshot', 10, 2);
            $table->timestamps();
            
            $table->foreign('order_item_id')->references('item_id')->on('order_items')->onDelete('cascade');
            $table->foreign('option_id')->references('option_id')->on('customization_options')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_customizations');
    }
};
