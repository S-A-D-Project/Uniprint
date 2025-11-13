<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $enterprises = DB::table('enterprises')->get();
        $printMaster = $enterprises->where('name', 'PrintMaster Solutions')->first();
        $tshirtPro = $enterprises->where('name', 'T-Shirt Pro Baguio')->first();
        $posterHub = $enterprises->where('name', 'Poster Hub')->first();

        // PrintMaster Products
        $bcProduct = Str::uuid();
        DB::table('products')->insert([
            [
                'product_id' => $bcProduct,
                'enterprise_id' => $printMaster->enterprise_id,
                'product_name' => 'Business Cards',
                'base_price' => 500.00,
                'description' => 'Professional business cards printed on premium cardstock',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => Str::uuid(),
                'enterprise_id' => $printMaster->enterprise_id,
                'product_name' => 'Flyers A4',
                'base_price' => 300.00,
                'description' => 'Full-color flyers on glossy or matte paper',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Business Card Customizations
        DB::table('customization_options')->insert([
            [
                'option_id' => Str::uuid(),
                'product_id' => $bcProduct,
                'option_name' => 'Standard (300gsm)',
                'option_type' => 'Paper Type',
                'price_modifier' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_id' => Str::uuid(),
                'product_id' => $bcProduct,
                'option_name' => 'Premium (350gsm)',
                'option_type' => 'Paper Type',
                'price_modifier' => 150.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_id' => Str::uuid(),
                'product_id' => $bcProduct,
                'option_name' => 'Matte Finish',
                'option_type' => 'Finish',
                'price_modifier' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_id' => Str::uuid(),
                'product_id' => $bcProduct,
                'option_name' => 'Glossy Finish',
                'option_type' => 'Finish',
                'price_modifier' => 50.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_id' => Str::uuid(),
                'product_id' => $bcProduct,
                'option_name' => 'Rounded Corners',
                'option_type' => 'Edge',
                'price_modifier' => 50.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // T-Shirt Pro Products
        $tshirtProduct = Str::uuid();
        DB::table('products')->insert([
            [
                'product_id' => $tshirtProduct,
                'enterprise_id' => $tshirtPro->enterprise_id,
                'product_name' => 'Custom T-Shirts',
                'base_price' => 350.00,
                'description' => 'High-quality custom printed t-shirts',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // T-Shirt Customizations
        DB::table('customization_options')->insert([
            [
                'option_id' => Str::uuid(),
                'product_id' => $tshirtProduct,
                'option_name' => 'Small',
                'option_type' => 'Size',
                'price_modifier' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_id' => Str::uuid(),
                'product_id' => $tshirtProduct,
                'option_name' => 'Medium',
                'option_type' => 'Size',
                'price_modifier' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_id' => Str::uuid(),
                'product_id' => $tshirtProduct,
                'option_name' => 'Large',
                'option_type' => 'Size',
                'price_modifier' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_id' => Str::uuid(),
                'product_id' => $tshirtProduct,
                'option_name' => 'White',
                'option_type' => 'Color',
                'price_modifier' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_id' => Str::uuid(),
                'product_id' => $tshirtProduct,
                'option_name' => 'Black',
                'option_type' => 'Color',
                'price_modifier' => 20.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Poster Hub Products
        DB::table('products')->insert([
            [
                'product_id' => Str::uuid(),
                'enterprise_id' => $posterHub->enterprise_id,
                'product_name' => 'Large Format Posters',
                'base_price' => 800.00,
                'description' => 'High-resolution large format posters',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
