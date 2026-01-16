<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix UUID data consistency issues.
     */
    public function up(): void
    {
        // This migration fixes any existing data with integer IDs instead of UUIDs
        $driver = DB::connection()->getDriverName();
        
        // Fix services table
        $servicesQuery = $driver === 'sqlite' 
            ? "CAST(service_id AS TEXT) LIKE '%0%' OR CAST(service_id AS TEXT) LIKE '%1%' OR CAST(service_id AS TEXT) LIKE '%2%' OR CAST(service_id AS TEXT) LIKE '%3%' OR CAST(service_id AS TEXT) LIKE '%4%' OR CAST(service_id AS TEXT) LIKE '%5%' OR CAST(service_id AS TEXT) LIKE '%6%' OR CAST(service_id AS TEXT) LIKE '%7%' OR CAST(service_id AS TEXT) LIKE '%8%' OR CAST(service_id AS TEXT) LIKE '%9%'"
            : "service_id::text ~ '^[0-9]+$'";
        $services = DB::table('services')->whereRaw($servicesQuery)->get();
        foreach ($services as $service) {
            $newUuid = Str::uuid();
            
            // Update related tables first
            DB::table('cart_items')->where('service_id', $service->service_id)->update([
                'service_id' => $newUuid
            ]);
            
            DB::table('order_items')->where('service_id', $service->service_id)->update([
                'service_id' => $newUuid
            ]);
            
            DB::table('customization_options')->where('service_id', $service->service_id)->update([
                'service_id' => $newUuid
            ]);
            
            // Then update the service itself
            DB::table('services')->where('service_id', $service->service_id)->update([
                'service_id' => $newUuid
            ]);
        }
        
        // Fix customization_options table
        $optionsQuery = $driver === 'sqlite'
            ? "CAST(option_id AS TEXT) LIKE '%0%' OR CAST(option_id AS TEXT) LIKE '%1%' OR CAST(option_id AS TEXT) LIKE '%2%' OR CAST(option_id AS TEXT) LIKE '%3%' OR CAST(option_id AS TEXT) LIKE '%4%' OR CAST(option_id AS TEXT) LIKE '%5%' OR CAST(option_id AS TEXT) LIKE '%6%' OR CAST(option_id AS TEXT) LIKE '%7%' OR CAST(option_id AS TEXT) LIKE '%8%' OR CAST(option_id AS TEXT) LIKE '%9%'"
            : "option_id::text ~ '^[0-9]+$'";
        $options = DB::table('customization_options')->whereRaw($optionsQuery)->get();
        foreach ($options as $option) {
            $newUuid = Str::uuid();
            
            // Update related tables first
            DB::table('order_item_customizations')->where('option_id', $option->option_id)->update([
                'option_id' => $newUuid
            ]);
            
            // Update cart items with JSON customizations
            $cartItems = DB::table('cart_items')->whereJsonContains('customizations', $option->option_id)->get();
            foreach ($cartItems as $item) {
                $customizations = json_decode($item->customizations, true);
                $customizations = array_map(function ($id) use ($option, $newUuid) {
                    return $id == $option->option_id ? $newUuid : $id;
                }, $customizations);
                
                DB::table('cart_items')->where('item_id', $item->item_id)->update([
                    'customizations' => json_encode($customizations)
                ]);
            }
            
            // Then update the option itself
            DB::table('customization_options')->where('option_id', $option->option_id)->update([
                'option_id' => $newUuid
            ]);
        }
        
        // Fix users table
        $usersQuery = $driver === 'sqlite'
            ? "CAST(user_id AS TEXT) LIKE '%0%' OR CAST(user_id AS TEXT) LIKE '%1%' OR CAST(user_id AS TEXT) LIKE '%2%' OR CAST(user_id AS TEXT) LIKE '%3%' OR CAST(user_id AS TEXT) LIKE '%4%' OR CAST(user_id AS TEXT) LIKE '%5%' OR CAST(user_id AS TEXT) LIKE '%6%' OR CAST(user_id AS TEXT) LIKE '%7%' OR CAST(user_id AS TEXT) LIKE '%8%' OR CAST(user_id AS TEXT) LIKE '%9%'"
            : "user_id::text ~ '^[0-9]+$'";
        $users = DB::table('users')->whereRaw($usersQuery)->get();
        foreach ($users as $user) {
            $newUuid = Str::uuid();
            
            // Update related tables first
            DB::table('shopping_carts')->where('user_id', $user->user_id)->update([
                'user_id' => $newUuid
            ]);
            
            DB::table('customer_orders')->where('customer_id', $user->user_id)->update([
                'customer_id' => $newUuid
            ]);
            
            // Then update the user itself
            DB::table('users')->where('user_id', $user->user_id)->update([
                'user_id' => $newUuid
            ]);
        }
        
        // Fix enterprises table
        $enterprisesQuery = $driver === 'sqlite'
            ? "CAST(enterprise_id AS TEXT) LIKE '%0%' OR CAST(enterprise_id AS TEXT) LIKE '%1%' OR CAST(enterprise_id AS TEXT) LIKE '%2%' OR CAST(enterprise_id AS TEXT) LIKE '%3%' OR CAST(enterprise_id AS TEXT) LIKE '%4%' OR CAST(enterprise_id AS TEXT) LIKE '%5%' OR CAST(enterprise_id AS TEXT) LIKE '%6%' OR CAST(enterprise_id AS TEXT) LIKE '%7%' OR CAST(enterprise_id AS TEXT) LIKE '%8%' OR CAST(enterprise_id AS TEXT) LIKE '%9%'"
            : "enterprise_id::text ~ '^[0-9]+$'";
        $enterprises = DB::table('enterprises')->whereRaw($enterprisesQuery)->get();
        foreach ($enterprises as $enterprise) {
            $newUuid = Str::uuid();
            
            // Update related tables first
            DB::table('products')->where('enterprise_id', $enterprise->enterprise_id)->update([
                'enterprise_id' => $newUuid
            ]);
            
            DB::table('customer_orders')->where('enterprise_id', $enterprise->enterprise_id)->update([
                'enterprise_id' => $newUuid
            ]);
            
            // Then update the enterprise itself
            DB::table('enterprises')->where('enterprise_id', $enterprise->enterprise_id)->update([
                'enterprise_id' => $newUuid
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it converts data
        // The down method is intentionally left empty
    }
};
