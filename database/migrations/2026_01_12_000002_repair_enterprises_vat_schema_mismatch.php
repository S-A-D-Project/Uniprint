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

        $hasVatTypeId = Schema::hasColumn('enterprises', 'vat_type_id');
        $hasVatTypesTable = Schema::hasTable('vat_types');

        // If VAT has already been removed cleanly, ensure vat_types is also removed.
        if (! $hasVatTypeId) {
            if ($hasVatTypesTable) {
                Schema::dropIfExists('vat_types');
            }
            return;
        }

        // vat_type_id exists but vat_types was dropped -> repair by removing vat_type_id.
        if ($hasVatTypeId && ! $hasVatTypesTable) {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');

                try {
                    $columns = DB::select("PRAGMA table_info('enterprises')");

                    $keptColumns = [];
                    $columnDefinitions = [];

                    foreach ($columns as $col) {
                        $name = $col->name;
                        if ($name === 'vat_type_id') {
                            continue;
                        }

                        $keptColumns[] = $name;

                        $type = $col->type ?: 'TEXT';
                        $definition = '"' . $name . '" ' . $type;

                        if ((int) $col->pk === 1) {
                            $definition .= ' PRIMARY KEY';
                        }

                        if ((int) $col->notnull === 1) {
                            $definition .= ' NOT NULL';
                        }

                        if ($col->dflt_value !== null) {
                            $definition .= ' DEFAULT ' . $col->dflt_value;
                        }

                        $columnDefinitions[] = $definition;
                    }

                    $createSql = 'CREATE TABLE enterprises_new (' . implode(', ', $columnDefinitions) . ')';
                    DB::statement($createSql);

                    $colList = implode(', ', array_map(fn ($c) => '"' . $c . '"', $keptColumns));
                    DB::statement('INSERT INTO enterprises_new (' . $colList . ') SELECT ' . $colList . ' FROM enterprises');

                    DB::statement('DROP TABLE enterprises');
                    DB::statement('ALTER TABLE enterprises_new RENAME TO enterprises');
                } finally {
                    DB::statement('PRAGMA foreign_keys = ON;');
                }

                return;
            }

            try {
                Schema::table('enterprises', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['vat_type_id']);
                    } catch (\Throwable $e) {
                        // ignore
                    }

                    if (Schema::hasColumn('enterprises', 'vat_type_id')) {
                        $table->dropColumn('vat_type_id');
                    }
                });
            } catch (\Throwable $e) {
                // ignore
            }

            return;
        }

        // Both exist: leave as-is.
    }

    public function down(): void
    {
        // no-op
    }
};
