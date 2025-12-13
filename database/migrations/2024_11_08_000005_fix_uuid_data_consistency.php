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
        
        // Fix services table
        $services = DB::table('services')->whereRaw("service_id::text ~ '^[0-9]+$'")->get();
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
        $options = DB::table('customization_options')->whereRaw("option_id::text ~ '^[0-9]+$'")->get();
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
        $users = DB::table('users')->whereRaw("user_id::text ~ '^[0-9]+$'")->get();
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
        $enterprises = DB::table('enterprises')->whereRaw("enterprise_id::text ~ '^[0-9]+$'")->get();
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
