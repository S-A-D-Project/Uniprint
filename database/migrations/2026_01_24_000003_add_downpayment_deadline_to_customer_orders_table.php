<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_orders')) {
            return;
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('customer_orders', function (Blueprint $table) use ($isSqlite) {
            if (! Schema::hasColumn('customer_orders', 'downpayment_required_percent')) {
                $col = $table->decimal('downpayment_required_percent', 5, 2)->default(0);
                if (! $isSqlite && Schema::hasColumn('customer_orders', 'payment_status')) {
                    $col->after('payment_status');
                }
            }

            if (! Schema::hasColumn('customer_orders', 'downpayment_required_amount')) {
                $table->decimal('downpayment_required_amount', 10, 2)->nullable();
            }

            if (! Schema::hasColumn('customer_orders', 'downpayment_due_at')) {
                $table->timestamp('downpayment_due_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_orders')) {
            return;
        }

        Schema::table('customer_orders', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('customer_orders', 'downpayment_due_at')) {
                $cols[] = 'downpayment_due_at';
            }
            if (Schema::hasColumn('customer_orders', 'downpayment_required_amount')) {
                $cols[] = 'downpayment_required_amount';
            }
            if (Schema::hasColumn('customer_orders', 'downpayment_required_percent')) {
                $cols[] = 'downpayment_required_percent';
            }
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
