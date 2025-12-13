<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_logins', function (Blueprint $table) {
            $table->uuid('social_login_id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('provider', 50); // 'google', 'facebook'
            $table->string('provider_id', 255)->unique(); // OAuth provider's user ID
            $table->string('email', 255)->nullable();
            $table->string('name', 200)->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->index(['provider', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_logins');
    }
};
