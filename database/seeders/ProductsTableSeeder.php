<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Enterprise;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing products
        Product::query()->delete();

        $enterprises = Enterprise::all();

        // PrintMaster Solutions products
        Product::create([
            'enterprise_id' => $enterprises[0]->enterprise_id,
            'product_name' => 'Business Cards',
            'description_text' => 'Professional business cards with premium finish options',
            'base_price' => 25.00,
            'is_available' => true,
        ]);

        Product::create([
            'enterprise_id' => $enterprises[0]->enterprise_id,
            'product_name' => 'Flyers',
            'description_text' => 'High-quality flyers for your marketing needs',
            'base_price' => 35.00,
            'is_available' => true,
        ]);

        Product::create([
            'enterprise_id' => $enterprises[0]->enterprise_id,
            'product_name' => 'Brochures',
            'description_text' => 'Multi-page brochures with various folding options',
            'base_price' => 50.00,
            'is_available' => true,
        ]);

        // T-Shirt Pro products
        Product::create([
            'enterprise_id' => $enterprises[1]->enterprise_id,
            'product_name' => 'Custom T-Shirt',
            'description_text' => 'Premium cotton t-shirts with custom printing',
            'base_price' => 15.00,
            'is_available' => true,
        ]);

        Product::create([
            'enterprise_id' => $enterprises[1]->enterprise_id,
            'product_name' => 'Hoodies',
            'description_text' => 'Comfortable hoodies with custom designs',
            'base_price' => 35.00,
            'is_available' => true,
        ]);

        Product::create([
            'enterprise_id' => $enterprises[1]->enterprise_id,
            'product_name' => 'Tank Tops',
            'description_text' => 'Breathable tank tops perfect for summer',
            'base_price' => 12.00,
            'is_available' => true,
        ]);

        // Poster Hub products
        Product::create([
            'enterprise_id' => $enterprises[2]->enterprise_id,
            'product_name' => 'Large Format Poster',
            'description_text' => 'Eye-catching posters in various sizes',
            'base_price' => 45.00,
            'is_available' => true,
        ]);

        Product::create([
            'enterprise_id' => $enterprises[2]->enterprise_id,
            'product_name' => 'Canvas Print',
            'description_text' => 'Gallery-quality canvas prints',
            'base_price' => 75.00,
            'is_available' => true,
        ]);

        Product::create([
            'enterprise_id' => $enterprises[2]->enterprise_id,
            'product_name' => 'Photo Enlargements',
            'description_text' => 'Professional photo enlargements',
            'base_price' => 30.00,
            'is_available' => true,
        ]);

        // Business Card Express products
        Product::create([
            'enterprise_id' => $enterprises[3]->enterprise_id,
            'product_name' => 'Premium Business Cards',
            'description_text' => 'Luxury business cards with special finishes',
            'base_price' => 40.00,
            'is_available' => true,
        ]);

        Product::create([
            'enterprise_id' => $enterprises[3]->enterprise_id,
            'product_name' => 'Letterheads',
            'description_text' => 'Professional letterheads for your business',
            'base_price' => 30.00,
            'is_available' => true,
        ]);

        // Banner World products
        Product::create([
            'enterprise_id' => $enterprises[4]->enterprise_id,
            'product_name' => 'Vinyl Banner',
            'description_text' => 'Durable outdoor vinyl banners',
            'base_price' => 55.00,
            'is_available' => true,
        ]);

        Product::create([
            'enterprise_id' => $enterprises[4]->enterprise_id,
            'product_name' => 'Retractable Banner Stand',
            'description_text' => 'Portable banner stand for events',
            'base_price' => 125.00,
            'is_available' => true,
        ]);
    }
}
