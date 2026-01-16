<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('enterprises')) {
            return;
        }

        if (! Schema::hasColumn('enterprises', 'owner_user_id')) {
            Schema::table('enterprises', function (Blueprint $table) {
                $table->uuid('owner_user_id')->nullable()->index();
            });
        }

        if (! Schema::hasTable('staff')) {
            return;
        }

        $enterprises = DB::table('enterprises')
            ->select('enterprise_id', 'owner_user_id')
            ->get();

        foreach ($enterprises as $enterprise) {
            if (! empty($enterprise->owner_user_id)) {
                continue;
            }

            $ownerUserId = DB::table('staff')
                ->where('enterprise_id', $enterprise->enterprise_id)
                ->orderByRaw("CASE WHEN position = 'Owner' THEN 0 ELSE 1 END")
                ->value('user_id');

            if ($ownerUserId) {
                DB::table('enterprises')
                    ->where('enterprise_id', $enterprise->enterprise_id)
                    ->update(['owner_user_id' => $ownerUserId]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('enterprises')) {
            return;
        }

        if (Schema::hasColumn('enterprises', 'owner_user_id')) {
            Schema::table('enterprises', function (Blueprint $table) {
                $table->dropColumn('owner_user_id');
            });
        }
    }
};
