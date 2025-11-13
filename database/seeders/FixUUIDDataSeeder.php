<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixUUIDDataSeeder extends Seeder
{
    /**
     * Fix any existing data that has integer IDs instead of UUIDs
     */
    public function run(): void
    {
        $this->command->info('Fixing UUID data consistency...');
        
        // Fix products table - convert any integer product_id to UUID
        $products = DB::table('products')->whereRaw("product_id::text ~ '^[0-9]+$'")->get();
        foreach ($products as $product) {
            $newUuid = Str::uuid();
            DB::table('products')->where('product_id', $product->product_id)->update([
                'product_id' => $newUuid
            ]);
            
            // Update related records
            DB::table('cart_items')->where('product_id', $product->product_id)->update([
                'product_id' => $newUuid
            ]);
            
            DB::table('order_items')->where('product_id', $product->product_id)->update([
                'product_id' => $newUuid
            ]);
            
            DB::table('customization_options')->where('product_id', $product->product_id)->update([
                'product_id' => $newUuid
            ]);
            
            $this->command->info("Fixed product ID: {$product->product_id} -> {$newUuid}");
        }
        
        // Fix customization_options table - convert any integer option_id to UUID
        $options = DB::table('customization_options')->whereRaw("option_id::text ~ '^[0-9]+$'")->get();
        foreach ($options as $option) {
            $newUuid = Str::uuid();
            DB::table('customization_options')->where('option_id', $option->option_id)->update([
                'option_id' => $newUuid
            ]);
            
            // Update related records
            DB::table('cart_items')->whereJsonContains('customizations', $option->option_id)->get()->each(function ($item) use ($option, $newUuid) {
                $customizations = json_decode($item->customizations, true);
                $customizations = array_map(function ($id) use ($option, $newUuid) {
                    return $id == $option->option_id ? $newUuid : $id;
                }, $customizations);
                
                DB::table('cart_items')->where('item_id', $item->item_id)->update([
                    'customizations' => json_encode($customizations)
                ]);
            });
            
            DB::table('order_item_customizations')->where('option_id', $option->option_id)->update([
                'option_id' => $newUuid
            ]);
            
            $this->command->info("Fixed option ID: {$option->option_id} -> {$newUuid}");
        }
        
        // Fix users table - convert any integer user_id to UUID
        $users = DB::table('users')->whereRaw("user_id::text ~ '^[0-9]+$'")->get();
        foreach ($users as $user) {
            $newUuid = Str::uuid();
            DB::table('users')->where('user_id', $user->user_id)->update([
                'user_id' => $newUuid
            ]);
            
            // Update related records
            DB::table('shopping_carts')->where('user_id', $user->user_id)->update([
                'user_id' => $newUuid
            ]);
            
            DB::table('customer_orders')->where('customer_id', $user->user_id)->update([
                'customer_id' => $newUuid
            ]);
            
            $this->command->info("Fixed user ID: {$user->user_id} -> {$newUuid}");
        }
        
        // Fix enterprises table - convert any integer enterprise_id to UUID
        $enterprises = DB::table('enterprises')->whereRaw("enterprise_id::text ~ '^[0-9]+$'")->get();
        foreach ($enterprises as $enterprise) {
            $newUuid = Str::uuid();
            DB::table('enterprises')->where('enterprise_id', $enterprise->enterprise_id)->update([
                'enterprise_id' => $newUuid
            ]);
            
            // Update related records
            DB::table('products')->where('enterprise_id', $enterprise->enterprise_id)->update([
                'enterprise_id' => $newUuid
            ]);
            
            DB::table('customer_orders')->where('enterprise_id', $enterprise->enterprise_id)->update([
                'enterprise_id' => $newUuid
            ]);
            
            $this->command->info("Fixed enterprise ID: {$enterprise->enterprise_id} -> {$newUuid}");
        }
        
        $this->command->info('UUID data consistency fix completed!');
    }
}
