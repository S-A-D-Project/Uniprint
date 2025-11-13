<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Enterprise;
use App\Models\CustomizationGroup;
use App\Models\CustomizationOption;
use App\Models\CustomizationRule;
use App\Services\CustomizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Customization System Test
 * 
 * Tests product customization features and validation
 */
class CustomizationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected CustomizationService $service;
    protected Product $product;
    protected CustomizationGroup $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CustomizationService();
        
        $enterprise = Enterprise::factory()->create();
        $this->product = Product::factory()->create(['enterprise_id' => $enterprise->enterprise_id]);
        
        $this->group = CustomizationGroup::factory()->create([
            'product_id' => $this->product->product_id,
            'group_name' => 'Material',
            'is_required' => true,
        ]);
    }

    /** @test */
    public function can_validate_required_customizations()
    {
        $option = CustomizationOption::factory()->create(['group_id' => $this->group->group_id]);

        // Without required selection
        $result = $this->service->validateCustomizations($this->product->product_id, []);
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);

        // With required selection
        $result = $this->service->validateCustomizations($this->product->product_id, [$option->option_id]);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function can_validate_customization_rules()
    {
        $option1 = CustomizationOption::factory()->create([
            'group_id' => $this->group->group_id,
            'option_name' => 'Option 1',
        ]);
        
        $option2 = CustomizationOption::factory()->create([
            'group_id' => $this->group->group_id,
            'option_name' => 'Option 2',
        ]);

        // Create a 'requires' rule
        CustomizationRule::create([
            'customization_group_id' => $this->group->group_id,
            'dependent_option_id' => $option1->option_id,
            'required_option_id' => $option2->option_id,
            'rule_type' => 'requires',
            'is_active' => true,
        ]);

        // Selecting option1 without option2 should fail
        $result = $this->service->validateCustomizations(
            $this->product->product_id,
            [$option1->option_id]
        );
        $this->assertFalse($result['valid']);

        // Selecting both should pass
        $result = $this->service->validateCustomizations(
            $this->product->product_id,
            [$option1->option_id, $option2->option_id]
        );
        $this->assertTrue($result['valid']);
    }

    /** @test */
    public function can_validate_conflicting_customizations()
    {
        $option1 = CustomizationOption::factory()->create(['group_id' => $this->group->group_id]);
        $option2 = CustomizationOption::factory()->create(['group_id' => $this->group->group_id]);

        // Create a 'conflicts' rule
        CustomizationRule::create([
            'customization_group_id' => $this->group->group_id,
            'dependent_option_id' => $option1->option_id,
            'required_option_id' => $option2->option_id,
            'rule_type' => 'conflicts',
            'is_active' => true,
        ]);

        // Selecting both conflicting options should fail
        $result = $this->service->validateCustomizations(
            $this->product->product_id,
            [$option1->option_id, $option2->option_id]
        );
        $this->assertFalse($result['valid']);

        // Selecting only one should pass
        $result = $this->service->validateCustomizations(
            $this->product->product_id,
            [$option1->option_id]
        );
        $this->assertTrue($result['valid']);
    }

    /** @test */
    public function can_get_available_options_based_on_selections()
    {
        $option1 = CustomizationOption::factory()->create(['group_id' => $this->group->group_id]);
        $option2 = CustomizationOption::factory()->create(['group_id' => $this->group->group_id]);

        $availableOptions = $this->service->getAvailableOptions(
            $this->product->product_id,
            [$option1->option_id]
        );

        $this->assertIsArray($availableOptions);
        $this->assertArrayHasKey($this->group->group_id, $availableOptions);
    }

    /** @test */
    public function can_export_customization_configuration()
    {
        CustomizationOption::factory()->count(3)->create(['group_id' => $this->group->group_id]);

        $export = $this->service->exportCustomizations($this->product->product_id);

        $this->assertArrayHasKey('product_id', $export);
        $this->assertArrayHasKey('groups', $export);
        $this->assertCount(1, $export['groups']);
        $this->assertCount(3, $export['groups'][0]['options']);
    }

    /** @test */
    public function can_bulk_import_customizations()
    {
        $configurations = [
            [
                'group_name' => 'Size',
                'is_required' => true,
                'allows_multiple' => false,
                'display_order' => 1,
                'options' => [
                    ['name' => 'Small', 'price' => 0],
                    ['name' => 'Medium', 'price' => 5],
                    ['name' => 'Large', 'price' => 10],
                ],
            ],
            [
                'group_name' => 'Color',
                'is_required' => false,
                'allows_multiple' => true,
                'display_order' => 2,
                'options' => [
                    ['name' => 'Red', 'price' => 2],
                    ['name' => 'Blue', 'price' => 2],
                ],
            ],
        ];

        $result = $this->service->bulkImportCustomizations($this->product->product_id, $configurations);

        $this->assertEquals(2, $result['success']);
        $this->assertEquals(0, $result['failed']);

        $groups = CustomizationGroup::where('product_id', $this->product->product_id)->get();
        $this->assertCount(3, $groups); // Including the one from setUp()
    }
}
