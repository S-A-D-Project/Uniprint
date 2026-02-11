<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_reports')) {
            return;
        }

        Schema::create('user_reports', function (Blueprint $table) {
            $table->uuid('report_id')->primary();
            $table->uuid('reporter_id');
            $table->uuid('enterprise_id')->nullable();
            $table->uuid('service_id')->nullable();
            $table->string('reason', 255);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('open');
            $table->uuid('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('reporter_id');
            $table->index('enterprise_id');
            $table->index('service_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
