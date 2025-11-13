<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enterprises', function (Blueprint $table) {
            $table->uuid('enterprise_id')->primary();
            $table->string('name', 255);
            $table->text('address')->nullable();
            $table->uuid('vat_type_id');
            $table->string('contact_person', 100)->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('tin_no', 20)->nullable();
            $table->timestamps();
            
            $table->foreign('vat_type_id')->references('vat_type_id')->on('vat_types')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprises');
    }
};
