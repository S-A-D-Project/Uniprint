<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('users', function (Blueprint $table) use ($isSqlite) {
            if (!Schema::hasColumn('users', 'phone')) {
                $col = $table->string('phone', 20)->nullable();
                if (!$isSqlite) {
                    $col->after('email');
                }
            }

            if (!Schema::hasColumn('users', 'two_factor_totp_enabled')) {
                $col = $table->boolean('two_factor_totp_enabled')->default(false);
                if (!$isSqlite) {
                    $col->after('two_factor_enabled_at');
                }
            }

            if (!Schema::hasColumn('users', 'two_factor_email_enabled')) {
                $col = $table->boolean('two_factor_email_enabled')->default(false);
                if (!$isSqlite) {
                    $col->after('two_factor_totp_enabled');
                }
            }

            if (!Schema::hasColumn('users', 'two_factor_sms_enabled')) {
                $col = $table->boolean('two_factor_sms_enabled')->default(false);
                if (!$isSqlite) {
                    $col->after('two_factor_email_enabled');
                }
            }
        });

        // Backfill: If legacy 2FA was enabled, mark TOTP enabled.
        if (Schema::hasColumn('users', 'two_factor_enabled_at') && Schema::hasColumn('users', 'two_factor_totp_enabled')) {
            DB::table('users')
                ->whereNotNull('two_factor_enabled_at')
                ->update(['two_factor_totp_enabled' => true]);
        }

        // Default-on: enable Email 2FA for existing non-admin users.
        // This matches Option B (enabled by default, but user can disable in Security Settings).
        if (Schema::hasColumn('users', 'two_factor_email_enabled')) {
            try {
                DB::table('users')
                    ->where(function ($q) {
                        $q->whereNull('two_factor_email_enabled')
                            ->orWhere('two_factor_email_enabled', false);
                    })
                    ->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('roles')
                            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                            ->whereColumn('roles.user_id', 'users.user_id')
                            ->whereIn('role_types.user_role_type', ['business_user', 'customer']);
                    })
                    ->update(['two_factor_email_enabled' => true]);
            } catch (\Throwable $e) {
                // Best-effort backfill; schema or role tables may not exist in some environments.
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            foreach (['two_factor_totp_enabled', 'two_factor_email_enabled', 'two_factor_sms_enabled', 'phone'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
