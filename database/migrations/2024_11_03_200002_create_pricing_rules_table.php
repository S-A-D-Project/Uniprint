<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->uuid('rule_id')->primary();
            $table->uuid('enterprise_id');
            $table->string('rule_name', 200);
            $table->string('rule_type', 50); // 'volume_discount', 'bulk_pricing', 'rush_fee', etc.
            $table->text('rule_description')->nullable();
            $table->json('conditions'); // JSON: [{field, operator, value}]
            $table->string('calculation_method', 50); // 'percentage', 'fixed_amount', 'formula'
            $table->decimal('value', 10, 2); // percentage or fixed value
            $table->text('formula')->nullable(); // custom calculation formula
            $table->integer('priority')->default(0); // for rule execution order
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('enterprise_id')->references('enterprise_id')->on('enterprises')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
