<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\CustomizationGroup;
use App\Models\CustomizationOption;
use Illuminate\Database\Seeder;

class CustomizationsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing customizations
        CustomizationOption::query()->delete();
        CustomizationGroup::query()->delete();

        $services = Service::all();

        // Business Cards customizations
        $businessCard = $services->where('service_name', 'Business Cards')->first();
        if ($businessCard) {
            $paperType = CustomizationGroup::create([
                'service_id' => $businessCard->service_id,
                'group_name' => 'Paper Type',
                'group_type' => 'Single Select',
                'is_required' => true,
                'display_order' => 1,
            ]);

            CustomizationOption::create([
                'group_id' => $paperType->group_id,
                'option_name' => 'Standard (14pt)',
                'price_modifier' => 0.00,
            ]);

            CustomizationOption::create([
                'group_id' => $paperType->group_id,
                'option_name' => 'Premium (16pt)',
                'price_modifier' => 5.00,
            ]);

            CustomizationOption::create([
                'group_id' => $paperType->group_id,
                'option_name' => 'Luxury (18pt)',
                'price_modifier' => 10.00,
            ]);

            $finish = CustomizationGroup::create([
                'service_id' => $businessCard->service_id,
                'group_name' => 'Finish',
                'group_type' => 'Single Select',
                'is_required' => true,
                'display_order' => 2,
            ]);

            CustomizationOption::create([
                'group_id' => $finish->group_id,
                'option_name' => 'Matte',
                'price_modifier' => 0.00,
            ]);

            CustomizationOption::create([
                'group_id' => $finish->group_id,
                'option_name' => 'Glossy',
                'price_modifier' => 3.00,
            ]);

            CustomizationOption::create([
                'group_id' => $finish->group_id,
                'option_name' => 'UV Coating',
                'price_modifier' => 8.00,
            ]);

            $quantity = CustomizationGroup::create([
                'service_id' => $businessCard->service_id,
                'group_name' => 'Quantity',
                'group_type' => 'Single Select',
                'is_required' => true,
                'display_order' => 3,
            ]);

            CustomizationOption::create([
                'group_id' => $quantity->group_id,
                'option_name' => '100 cards',
                'price_modifier' => 0.00,
            ]);

            CustomizationOption::create([
                'group_id' => $quantity->group_id,
                'option_name' => '250 cards',
                'price_modifier' => 15.00,
            ]);

            CustomizationOption::create([
                'group_id' => $quantity->group_id,
                'option_name' => '500 cards',
                'price_modifier' => 25.00,
            ]);
        }

        // Custom T-Shirt customizations
        $tshirt = $services->where('service_name', 'Custom T-Shirt')->first();
        if ($tshirt) {
            $size = CustomizationGroup::create([
                'service_id' => $tshirt->service_id,
                'group_name' => 'Size',
                'group_type' => 'Single Select',
                'is_required' => true,
                'display_order' => 1,
            ]);

            foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $sizeOption) {
                CustomizationOption::create([
                    'group_id' => $size->group_id,
                    'option_name' => $sizeOption,
                    'price_modifier' => $sizeOption === 'XXL' ? 3.00 : 0.00,
                ]);
            }

            $color = CustomizationGroup::create([
                'service_id' => $tshirt->service_id,
                'group_name' => 'Color',
                'group_type' => 'Single Select',
                'is_required' => true,
                'display_order' => 2,
            ]);

            $colors = ['White', 'Black', 'Navy', 'Red', 'Royal Blue', 'Forest Green', 'Gray'];
            foreach ($colors as $colorOption) {
                CustomizationOption::create([
                    'group_id' => $color->group_id,
                    'option_name' => $colorOption,
                    'price_modifier' => 0.00,
                ]);
            }

            $printLocation = CustomizationGroup::create([
                'service_id' => $tshirt->service_id,
                'group_name' => 'Print Location',
                'group_type' => 'Multi Select',
                'is_required' => true,
                'display_order' => 3,
            ]);

            CustomizationOption::create([
                'group_id' => $printLocation->group_id,
                'option_name' => 'Front',
                'price_modifier' => 0.00,
            ]);

            CustomizationOption::create([
                'group_id' => $printLocation->group_id,
                'option_name' => 'Back',
                'price_modifier' => 5.00,
            ]);

            CustomizationOption::create([
                'group_id' => $printLocation->group_id,
                'option_name' => 'Sleeve',
                'price_modifier' => 3.00,
            ]);
        }

        // Large Format Poster customizations
        $poster = $services->where('service_name', 'Large Format Poster')->first();
        if ($poster) {
            $posterSize = CustomizationGroup::create([
                'service_id' => $poster->service_id,
                'group_name' => 'Size',
                'group_type' => 'Single Select',
                'is_required' => true,
                'display_order' => 1,
            ]);

            CustomizationOption::create([
                'group_id' => $posterSize->group_id,
                'option_name' => '18x24 inches',
                'price_modifier' => 0.00,
            ]);

            CustomizationOption::create([
                'group_id' => $posterSize->group_id,
                'option_name' => '24x36 inches',
                'price_modifier' => 15.00,
            ]);

            CustomizationOption::create([
                'group_id' => $posterSize->group_id,
                'option_name' => '36x48 inches',
                'price_modifier' => 35.00,
            ]);

            $paperQuality = CustomizationGroup::create([
                'service_id' => $poster->service_id,
                'group_name' => 'Paper Quality',
                'group_type' => 'Single Select',
                'is_required' => true,
                'display_order' => 2,
            ]);

            CustomizationOption::create([
                'group_id' => $paperQuality->group_id,
                'option_name' => 'Standard',
                'price_modifier' => 0.00,
            ]);

            CustomizationOption::create([
                'group_id' => $paperQuality->group_id,
                'option_name' => 'Premium Gloss',
                'price_modifier' => 10.00,
            ]);

            CustomizationOption::create([
                'group_id' => $paperQuality->group_id,
                'option_name' => 'Photo Paper',
                'price_modifier' => 20.00,
            ]);
        }

        // Vinyl Banner customizations
        $banner = $services->where('service_name', 'Vinyl Banner')->first();
        if ($banner) {
            $bannerSize = CustomizationGroup::create([
                'service_id' => $banner->service_id,
                'group_name' => 'Size',
                'group_type' => 'Single Select',
                'is_required' => true,
                'display_order' => 1,
            ]);

            CustomizationOption::create([
                'group_id' => $bannerSize->group_id,
                'option_name' => '3x6 feet',
                'price_modifier' => 0.00,
            ]);

            CustomizationOption::create([
                'group_id' => $bannerSize->group_id,
                'option_name' => '4x8 feet',
                'price_modifier' => 25.00,
            ]);

            CustomizationOption::create([
                'group_id' => $bannerSize->group_id,
                'option_name' => '5x10 feet',
                'price_modifier' => 50.00,
            ]);

            $grommets = CustomizationGroup::create([
                'service_id' => $banner->service_id,
                'group_name' => 'Grommets',
                'group_type' => 'Single Select',
                'is_required' => false,
                'display_order' => 2,
            ]);

            CustomizationOption::create([
                'group_id' => $grommets->group_id,
                'option_name' => 'No Grommets',
                'price_modifier' => 0.00,
            ]);

            CustomizationOption::create([
                'group_id' => $grommets->group_id,
                'option_name' => 'Standard Grommets',
                'price_modifier' => 10.00,
            ]);

            CustomizationOption::create([
                'group_id' => $grommets->group_id,
                'option_name' => 'Reinforced Grommets',
                'price_modifier' => 15.00,
            ]);
        }
    }
}
