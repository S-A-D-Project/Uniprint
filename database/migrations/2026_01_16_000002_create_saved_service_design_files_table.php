<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_service_design_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('saved_service_id')->index();
            $table->uuid('user_id')->index();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 20)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('design_notes')->nullable();
            $table->timestamps();

            $table->foreign('saved_service_id')
                ->references('saved_service_id')
                ->on('saved_services')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_service_design_files');
    }
};
