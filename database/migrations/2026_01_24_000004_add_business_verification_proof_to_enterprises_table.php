<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('enterprises')) {
            return;
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('enterprises', function (Blueprint $table) use ($isSqlite) {
            if (! Schema::hasColumn('enterprises', 'verification_document_path')) {
                $col = $table->string('verification_document_path')->nullable();
                if (! $isSqlite) {
                    $col->after('verified_by_user_id');
                }
            }

            if (! Schema::hasColumn('enterprises', 'verification_notes')) {
                $col = $table->text('verification_notes')->nullable();
                if (! $isSqlite) {
                    $col->after('verification_document_path');
                }
            }

            if (! Schema::hasColumn('enterprises', 'verification_submitted_at')) {
                $col = $table->timestamp('verification_submitted_at')->nullable();
                if (! $isSqlite) {
                    $col->after('verification_notes');
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('enterprises')) {
            return;
        }

        Schema::table('enterprises', function (Blueprint $table) {
            $cols = [];

            if (Schema::hasColumn('enterprises', 'verification_submitted_at')) {
                $cols[] = 'verification_submitted_at';
            }
            if (Schema::hasColumn('enterprises', 'verification_notes')) {
                $cols[] = 'verification_notes';
            }
            if (Schema::hasColumn('enterprises', 'verification_document_path')) {
                $cols[] = 'verification_document_path';
            }

            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
