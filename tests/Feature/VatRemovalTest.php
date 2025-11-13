<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use App\Models\Enterprise;
use App\Models\Product;

class VatRemovalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that VAT-related tables and columns have been removed
     */
    public function test_vat_tables_and_columns_removed()
    {
        // Test that vat_types table does not exist
        $this->assertFalse(
            Schema::hasTable('vat_types'),
            'vat_types table should be removed'
        );

        // Test that enterprises table does not have vat_type_id column
        $this->assertFalse(
            Schema::hasColumn('enterprises', 'vat_type_id'),
            'enterprises.vat_type_id column should be removed'
        );

        // Test other potential VAT columns
        $vatColumns = [
            'transactions' => ['vat_amount', 'vat_rate'],
            'customer_orders' => ['vat_amount', 'vat_rate'],
            'order_items' => ['vat_amount', 'vat_rate'],
            'products' => ['vat_inclusive', 'vat_rate'],
            'pricing_rules' => ['apply_vat', 'vat_rate'],
        ];

        foreach ($vatColumns as $table => $columns) {
            if (Schema::hasTable($table)) {
                foreach ($columns as $column) {
                    $this->assertFalse(
                        Schema::hasColumn($table, $column),
                        "{$table}.{$column} column should be removed"
                    );
                }
            }
        }
    }

    /**
     * Test that Enterprise model works without VAT fields
     */
    public function test_enterprise_model_works_without_vat()
    {
        $enterprise = Enterprise::create([
            'name' => 'Test Print Shop',
            'address' => 'Test Address, Baguio City',
            'contact_person' => 'Test Person',
            'contact_number' => '+63-74-123-4567',
            'tin_no' => '123-456-789-000',
        ]);

        $this->assertInstanceOf(Enterprise::class, $enterprise);
        $this->assertEquals('Test Print Shop', $enterprise->name);
        $this->assertNotNull($enterprise->enterprise_id);

        // Test that VAT-related accessors still work for backward compatibility
        $this->assertTrue($enterprise->is_active);
        $this->assertEquals('Printing Services', $enterprise->category);
        $this->assertNull($enterprise->email);
    }

    /**
     * Test that pricing calculations work without VAT
     */
    public function test_pricing_works_without_vat()
    {
        // Create test enterprise
        $enterprise = Enterprise::create([
            'name' => 'Test Print Shop',
            'address' => 'Test Address, Baguio City',
            'contact_person' => 'Test Person',
            'contact_number' => '+63-74-123-4567',
            'tin_no' => '123-456-789-000',
        ]);

        // Create test product
        $product = Product::create([
            'enterprise_id' => $enterprise->enterprise_id,
            'product_name' => 'Test Business Cards',
            'description' => 'Test description',
            'base_price' => 500.00,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(500.00, $product->base_price);
        $this->assertTrue($product->is_active);

        // Test relationship
        $this->assertEquals($enterprise->enterprise_id, $product->enterprise_id);
        $this->assertInstanceOf(Enterprise::class, $product->enterprise);
    }

    /**
     * Test that seeders work without VAT references
     */
    public function test_seeders_work_without_vat()
    {
        // Run the enterprises seeder
        $this->artisan('db:seed', ['--class' => 'EnterprisesSeeder']);

        // Verify enterprises were created
        $enterpriseCount = Enterprise::count();
        $this->assertGreaterThan(0, $enterpriseCount, 'Enterprises should be seeded');

        // Verify no VAT-related data exists
        $enterprises = Enterprise::all();
        foreach ($enterprises as $enterprise) {
            $this->assertNotNull($enterprise->name);
            $this->assertNotNull($enterprise->address);
            // Verify no vat_type_id is set (should not exist in model)
            $this->assertArrayNotHasKey('vat_type_id', $enterprise->getAttributes());
        }
    }

    /**
     * Test that debug endpoints work without VAT data
     */
    public function test_debug_endpoints_work_without_vat()
    {
        // Seed some test data
        $this->artisan('db:seed', ['--class' => 'EnterprisesSeeder']);

        // Test enterprises debug endpoint
        $response = $this->get('/debug/enterprises');
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('enterprises', $data);
        $this->assertGreaterThan(0, $data['count']);

        // Verify no VAT data in response
        if (!empty($data['enterprises'])) {
            foreach ($data['enterprises'] as $enterprise) {
                $this->assertArrayNotHasKey('vat_type_id', $enterprise);
                $this->assertArrayHasKey('name', $enterprise);
                $this->assertArrayHasKey('address', $enterprise);
            }
        }
    }

    /**
     * Test that enterprise pages load without VAT references
     */
    public function test_enterprise_pages_load_without_vat()
    {
        // Seed test data
        $this->artisan('db:seed', ['--class' => 'EnterprisesSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProductsSeeder']);

        // Test enterprises index page
        $response = $this->get('/enterprises');
        $response->assertStatus(200);

        // Get first enterprise for detail page test
        $enterprise = Enterprise::first();
        if ($enterprise) {
            $response = $this->get("/enterprises/{$enterprise->enterprise_id}");
            $response->assertStatus(200);
        }
    }

    /**
     * Test that no VAT-related validation errors occur
     */
    public function test_no_vat_validation_errors()
    {
        // Test creating enterprise without VAT fields
        $enterpriseData = [
            'name' => 'New Print Shop',
            'address' => 'New Address, Baguio City',
            'contact_person' => 'New Person',
            'contact_number' => '+63-74-999-9999',
            'tin_no' => '999-999-999-000',
        ];

        $enterprise = new Enterprise($enterpriseData);
        $this->assertTrue($enterprise->save());

        // Verify saved data
        $this->assertEquals('New Print Shop', $enterprise->name);
        $this->assertNotNull($enterprise->enterprise_id);
    }
}
