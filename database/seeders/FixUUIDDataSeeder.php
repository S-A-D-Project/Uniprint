<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FixUUIDDataSeeder extends Seeder
{
    /**
     * Fix any existing data that has integer IDs instead of UUIDs
     */
    public function run(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'pgsql') {
            // This seeder uses Postgres-specific operators (e.g., ::text and regex matches).
            // Skip on other drivers to avoid breaking migrate:fresh --seed.
            return;
        }

        $this->command->info('Fixing UUID data consistency...');
        
        // Fix services table - convert any integer service_id to UUID
        if (! Schema::hasTable('services')) {
            return;
        }

        $services = DB::table('services')->whereRaw("service_id::text ~ '^[0-9]+$'")->get();
        foreach ($services as $service) {
            $newUuid = Str::uuid();
            DB::table('services')->where('service_id', $service->service_id)->update([
                'service_id' => $newUuid
            ]);
            
            // Update related records
            if (Schema::hasTable('cart_items') && Schema::hasColumn('cart_items', 'product_id')) {
                DB::table('cart_items')->where('product_id', $service->service_id)->update([
                    'product_id' => $newUuid
                ]);
            }
            
            if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'service_id')) {
                DB::table('order_items')->where('service_id', $service->service_id)->update([
                    'service_id' => $newUuid
                ]);
            }
            
            if (Schema::hasTable('customization_options') && Schema::hasColumn('customization_options', 'service_id')) {
                DB::table('customization_options')->where('service_id', $service->service_id)->update([
                    'service_id' => $newUuid
                ]);
            }
            
            $this->command->info("Fixed service ID: {$service->service_id} -> {$newUuid}");
        }
        
        // Fix customization_options table - convert any integer option_id to UUID (service specific)
        if (! Schema::hasTable('customization_options')) {
            return;
        }

        $options = DB::table('customization_options')->whereRaw("option_id::text ~ '^[0-9]+$'")->get();
        foreach ($options as $option) {
            $newUuid = Str::uuid();
            DB::table('customization_options')->where('option_id', $option->option_id)->update([
                'option_id' => $newUuid
            ]);
            
            // Update related records
            if (Schema::hasTable('cart_items') && Schema::hasColumn('cart_items', 'customizations')) {
                DB::table('cart_items')->whereJsonContains('customizations', $option->option_id)->get()->each(function ($item) use ($option, $newUuid) {
                    $customizations = json_decode($item->customizations, true);
                    $customizations = array_map(function ($id) use ($option, $newUuid) {
                        return $id == $option->option_id ? $newUuid : $id;
                    }, $customizations);
                    
                    DB::table('cart_items')->where('item_id', $item->item_id)->update([
                        'customizations' => json_encode($customizations)
                    ]);
                });
            }
            
            if (Schema::hasTable('order_item_customizations') && Schema::hasColumn('order_item_customizations', 'option_id')) {
                DB::table('order_item_customizations')->where('option_id', $option->option_id)->update([
                    'option_id' => $newUuid
                ]);
            }
            
            $this->command->info("Fixed option ID: {$option->option_id} -> {$newUuid}");
        }
        
        // Fix users table - convert any integer user_id to UUID
        if (! Schema::hasTable('users')) {
            return;
        }

        $users = DB::table('users')->whereRaw("user_id::text ~ '^[0-9]+$'")->get();
        foreach ($users as $user) {
            $newUuid = Str::uuid();
            DB::table('users')->where('user_id', $user->user_id)->update([
                'user_id' => $newUuid
            ]);
            
            // Update related records
            if (Schema::hasTable('shopping_carts') && Schema::hasColumn('shopping_carts', 'user_id')) {
                DB::table('shopping_carts')->where('user_id', $user->user_id)->update([
                    'user_id' => $newUuid
                ]);
            }
            
            if (Schema::hasTable('customer_orders') && Schema::hasColumn('customer_orders', 'customer_id')) {
                DB::table('customer_orders')->where('customer_id', $user->user_id)->update([
                    'customer_id' => $newUuid
                ]);
            }
            
            $this->command->info("Fixed user ID: {$user->user_id} -> {$newUuid}");
        }
        
        // Fix enterprises table - convert any integer enterprise_id to UUID
        if (! Schema::hasTable('enterprises')) {
            return;
        }

        $enterprises = DB::table('enterprises')->whereRaw("enterprise_id::text ~ '^[0-9]+$'")->get();
        foreach ($enterprises as $enterprise) {
            $newUuid = Str::uuid();
            DB::table('enterprises')->where('enterprise_id', $enterprise->enterprise_id)->update([
                'enterprise_id' => $newUuid
            ]);
            
            // Update related records
            if (Schema::hasTable('services') && Schema::hasColumn('services', 'enterprise_id')) {
                DB::table('services')->where('enterprise_id', $enterprise->enterprise_id)->update([
                    'enterprise_id' => $newUuid
                ]);
            }
            
            if (Schema::hasTable('customer_orders') && Schema::hasColumn('customer_orders', 'enterprise_id')) {
                DB::table('customer_orders')->where('enterprise_id', $enterprise->enterprise_id)->update([
                    'enterprise_id' => $newUuid
                ]);
            }
            
            $this->command->info("Fixed enterprise ID: {$enterprise->enterprise_id} -> {$newUuid}");
        }
        
        $this->command->info('UUID data consistency fix completed!');
    }
}
