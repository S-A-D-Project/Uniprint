<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('services')) {
            return;
        }

        Schema::create('services', function (Blueprint $table) {
            $table->uuid('service_id')->primary();
            $table->uuid('enterprise_id')->index();

            $table->string('service_name', 255);
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->index('created_at');

            $table->foreign('enterprise_id')
                ->references('enterprise_id')
                ->on('enterprises')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
