<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('statuses') || ! Schema::hasTable('customer_orders')) {
            return;
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        // Seed default statuses if they don't exist first
        DB::table('statuses')->insertOrIgnore([
            [
                'status_id' => '550e8400-e29b-41d4-a716-446655440001',
                'status_name' => 'Pending',
                'description' => 'Order is pending approval'
            ],
            [
                'status_id' => '550e8400-e29b-41d4-a716-446655440002', 
                'status_name' => 'Processing',
                'description' => 'Order is being processed'
            ],
            [
                'status_id' => '550e8400-e29b-41d4-a716-446655440003',
                'status_name' => 'Ready for Pickup',
                'description' => 'Order is ready for pickup'
            ],
            [
                'status_id' => '550e8400-e29b-41d4-a716-446655440004',
                'status_name' => 'Delivered',
                'description' => 'Order has been delivered'
            ],
            [
                'status_id' => '550e8400-e29b-41d4-a716-446655440005',
                'status_name' => 'Cancelled',
                'description' => 'Order has been cancelled'
            ]
        ]);

        // Add status_id to customer_orders table for direct status tracking (without foreign key for now)
        Schema::table('customer_orders', function (Blueprint $table) use ($isSqlite) {
            if (! Schema::hasColumn('customer_orders', 'status_id')) {
                $col = $table->uuid('status_id')->nullable();
                if (! $isSqlite) {
                    $col->after('enterprise_id');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove status_id from customer_orders
        Schema::table('customer_orders', function (Blueprint $table) {
            if (Schema::hasColumn('customer_orders', 'status_id')) {
                $table->dropColumn('status_id');
            }
        });

        // Remove seeded statuses (be careful with this in production)
        DB::table('statuses')->whereIn('status_name', ['Pending', 'Processing', 'Ready for Pickup', 'Delivered', 'Cancelled'])->delete();
    }
};
