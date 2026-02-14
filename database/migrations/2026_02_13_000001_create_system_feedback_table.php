<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('system_feedback')) {
            return;
        }

        Schema::create('system_feedback', function (Blueprint $table) {
            $table->uuid('feedback_id')->primary();
            $table->uuid('user_id')->index();

            $table->string('category', 50)->default('general')->index();
            $table->string('rating', 20)->nullable()->index();
            $table->string('subject', 255);
            $table->text('message');

            $table->string('status', 20)->default('new')->index();
            $table->uuid('reviewed_by')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_feedback');
    }
};
