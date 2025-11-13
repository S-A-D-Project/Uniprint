<?php

namespace Database\Seeders;

use App\Models\Enterprise;
use Illuminate\Database\Seeder;

class EnterprisesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing enterprises
        Enterprise::query()->delete();

        Enterprise::create([
            'enterprise_name' => 'PrintMaster Solutions',
            'category' => 'Print Shop',
            'address_text' => '123 Main Street, Downtown, City 12345',
            'contact_email' => 'info@printmaster.com',
            'is_active' => true,
        ]);

        Enterprise::create([
            'enterprise_name' => 'T-Shirt Pro',
            'category' => 'Apparel Printing',
            'address_text' => '456 Fashion Avenue, Mall District, City 12345',
            'contact_email' => 'orders@tshirtpro.com',
            'is_active' => true,
        ]);

        Enterprise::create([
            'enterprise_name' => 'Poster Hub',
            'category' => 'Large Format Printing',
            'address_text' => '789 Creative Lane, Arts Quarter, City 12345',
            'contact_email' => 'hello@posterhub.com',
            'is_active' => true,
        ]);

        Enterprise::create([
            'enterprise_name' => 'Business Card Express',
            'category' => 'Commercial Printing',
            'address_text' => '321 Corporate Blvd, Business Park, City 12345',
            'contact_email' => 'support@bcexpress.com',
            'is_active' => true,
        ]);

        Enterprise::create([
            'enterprise_name' => 'Banner World',
            'category' => 'Signage & Banners',
            'address_text' => '654 Marketing Drive, Industrial Zone, City 12345',
            'contact_email' => 'contact@bannerworld.com',
            'is_active' => true,
        ]);
    }
}
