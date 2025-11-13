<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_types', function (Blueprint $table) {
            $table->uuid('role_type_id')->primary();
            $table->string('user_role_type', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_types');
    }
};
