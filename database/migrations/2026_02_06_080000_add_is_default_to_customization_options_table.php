<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customization_options')) {
            return;
        }

        Schema::table('customization_options', function (Blueprint $table) {
            if (!Schema::hasColumn('customization_options', 'is_default')) {
                $table->boolean('is_default')->default(false);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('customization_options')) {
            return;
        }

        Schema::table('customization_options', function (Blueprint $table) {
            if (Schema::hasColumn('customization_options', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }
};
