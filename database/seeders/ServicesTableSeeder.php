<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Enterprise;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing services
        Service::query()->delete();

        $enterprises = Enterprise::all();

        // PrintMaster Solutions services
        Service::create([
            'enterprise_id' => $enterprises[0]->enterprise_id,
            'service_name' => 'Business Cards',
            'description' => 'Professional business cards with premium finish options',
            'base_price' => 25.00,
            'is_active' => true,
        ]);

        Service::create([
            'enterprise_id' => $enterprises[0]->enterprise_id,
            'service_name' => 'Flyers',
            'description' => 'High-quality flyers for your marketing needs',
            'base_price' => 35.00,
            'is_active' => true,
        ]);

        Service::create([
            'enterprise_id' => $enterprises[0]->enterprise_id,
            'service_name' => 'Brochures',
            'description' => 'Multi-page brochures with various folding options',
            'base_price' => 50.00,
            'is_active' => true,
        ]);

        // T-Shirt Pro services
        Service::create([
            'enterprise_id' => $enterprises[1]->enterprise_id,
            'service_name' => 'Custom T-Shirt',
            'description' => 'Premium cotton t-shirts with custom printing',
            'base_price' => 15.00,
            'is_active' => true,
        ]);

        Service::create([
            'enterprise_id' => $enterprises[1]->enterprise_id,
            'service_name' => 'Hoodies',
            'description' => 'Comfortable hoodies with custom designs',
            'base_price' => 35.00,
            'is_active' => true,
        ]);

        Service::create([
            'enterprise_id' => $enterprises[1]->enterprise_id,
            'service_name' => 'Tank Tops',
            'description' => 'Breathable tank tops perfect for summer',
            'base_price' => 12.00,
            'is_active' => true,
        ]);

        // Poster Hub services
        Service::create([
            'enterprise_id' => $enterprises[2]->enterprise_id,
            'service_name' => 'Large Format Poster',
            'description' => 'Eye-catching posters in various sizes',
            'base_price' => 45.00,
            'is_active' => true,
        ]);

        Service::create([
            'enterprise_id' => $enterprises[2]->enterprise_id,
            'service_name' => 'Canvas Print',
            'description' => 'Gallery-quality canvas prints',
            'base_price' => 75.00,
            'is_active' => true,
        ]);

        Service::create([
            'enterprise_id' => $enterprises[2]->enterprise_id,
            'service_name' => 'Photo Enlargements',
            'description' => 'Professional photo enlargements',
            'base_price' => 30.00,
            'is_active' => true,
        ]);

        // Business Card Express services
        Service::create([
            'enterprise_id' => $enterprises[3]->enterprise_id,
            'service_name' => 'Premium Business Cards',
            'description' => 'Luxury business cards with special finishes',
            'base_price' => 40.00,
            'is_active' => true,
        ]);

        Service::create([
            'enterprise_id' => $enterprises[3]->enterprise_id,
            'service_name' => 'Letterheads',
            'description' => 'Professional letterheads for your business',
            'base_price' => 30.00,
            'is_active' => true,
        ]);

        // Banner World services
        Service::create([
            'enterprise_id' => $enterprises[4]->enterprise_id,
            'service_name' => 'Vinyl Banner',
            'description' => 'Durable outdoor vinyl banners',
            'base_price' => 55.00,
            'is_active' => true,
        ]);

        Service::create([
            'enterprise_id' => $enterprises[4]->enterprise_id,
            'service_name' => 'Retractable Banner Stand',
            'description' => 'Portable banner stand for events',
            'base_price' => 125.00,
            'is_active' => true,
        ]);
    }
}
