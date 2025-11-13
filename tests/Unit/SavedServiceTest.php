<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\SavedService;
use App\Models\SavedServiceCollection;
use App\Models\Product;
use App\Models\Enterprise;
use App\Models\CustomizationOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class SavedServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $enterprise;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create();
        $this->enterprise = Enterprise::factory()->create();
        $this->product = Product::factory()->create([
            'enterprise_id' => $this->enterprise->enterprise_id,
            'base_price' => 100.00
        ]);
    }

    /** @test */
    public function it_can_save_a_service_for_user()
    {
        $savedService = SavedService::saveService(
            $this->user->user_id,
            $this->product->product_id,
            2,
            [],
            'Test instructions'
        );

        $this->assertInstanceOf(SavedService::class, $savedService);
        $this->assertEquals($this->user->user_id, $savedService->user_id);
        $this->assertEquals($this->product->product_id, $savedService->product_id);
        $this->assertEquals(2, $savedService->quantity);
        $this->assertEquals(200.00, $savedService->total_price);
        $this->assertEquals('Test instructions', $savedService->special_instructions);
    }

    /** @test */
    public function it_can_save_service_with_customizations()
    {
        $customization = CustomizationOption::factory()->create([
            'product_id' => $this->product->product_id,
            'price_modifier' => 50.00
        ]);

        $savedService = SavedService::saveService(
            $this->user->user_id,
            $this->product->product_id,
            1,
            [$customization->option_id]
        );

        $this->assertEquals(150.00, $savedService->total_price);
        $this->assertTrue($savedService->customizationOptions->contains($customization));
    }

    /** @test */
    public function it_updates_quantity_of_existing_service()
    {
        // Save initial service
        SavedService::saveService(
            $this->user->user_id,
            $this->product->product_id,
            1
        );

        // Save same service again
        $updatedService = SavedService::saveService(
            $this->user->user_id,
            $this->product->product_id,
            2
        );

        $this->assertEquals(3, $updatedService->quantity);
        $this->assertEquals(300.00, $updatedService->total_price);
    }

    /** @test */
    public function it_can_remove_saved_service()
    {
        $savedService = SavedService::saveService(
            $this->user->user_id,
            $this->product->product_id
        );

        $result = SavedService::removeService(
            $this->user->user_id,
            $savedService->saved_service_id
        );

        $this->assertTrue($result);
        $this->assertDatabaseMissing('saved_services', [
            'saved_service_id' => $savedService->saved_service_id
        ]);
    }

    /** @test */
    public function it_can_update_service_quantity()
    {
        $savedService = SavedService::saveService(
            $this->user->user_id,
            $this->product->product_id,
            2
        );

        $updatedService = SavedService::updateServiceQuantity(
            $this->user->user_id,
            $savedService->saved_service_id,
            5
        );

        $this->assertInstanceOf(SavedService::class, $updatedService);
        $this->assertEquals(5, $updatedService->quantity);
        $this->assertEquals(500.00, $updatedService->total_price);
    }

    /** @test */
    public function it_removes_service_when_quantity_is_zero()
    {
        $savedService = SavedService::saveService(
            $this->user->user_id,
            $this->product->product_id,
            2
        );

        $result = SavedService::updateServiceQuantity(
            $this->user->user_id,
            $savedService->saved_service_id,
            0
        );

        $this->assertTrue($result);
        $this->assertDatabaseMissing('saved_services', [
            'saved_service_id' => $savedService->saved_service_id
        ]);
    }

    /** @test */
    public function it_can_get_services_count()
    {
        SavedService::saveService($this->user->user_id, $this->product->product_id, 2);
        SavedService::saveService($this->user->user_id, $this->product->product_id, 3);

        $count = SavedService::getServicesCount($this->user->user_id);
        $this->assertEquals(5, $count); // Sum of quantities
    }

    /** @test */
    public function it_can_get_total_amount()
    {
        SavedService::saveService($this->user->user_id, $this->product->product_id, 2);
        SavedService::saveService($this->user->user_id, $this->product->product_id, 3);

        $total = SavedService::getTotalAmount($this->user->user_id);
        $this->assertEquals(500.00, $total);
    }

    /** @test */
    public function it_can_clear_all_services()
    {
        SavedService::saveService($this->user->user_id, $this->product->product_id, 2);
        SavedService::saveService($this->user->user_id, $this->product->product_id, 3);

        $result = SavedService::clearServices($this->user->user_id);
        
        $this->assertTrue($result);
        $this->assertEquals(0, SavedService::getServicesCount($this->user->user_id));
    }

    /** @test */
    public function saved_service_collection_works_correctly()
    {
        // Add some saved services
        SavedService::saveService($this->user->user_id, $this->product->product_id, 2);
        SavedService::saveService($this->user->user_id, $this->product->product_id, 3);

        $collection = new SavedServiceCollection($this->user->user_id);

        $this->assertEquals(5, $collection->total_items);
        $this->assertEquals(500.00, $collection->total_amount);
        $this->assertFalse($collection->isEmpty());
        $this->assertEquals(2, $collection->count());
    }

    /** @test */
    public function saved_service_collection_can_add_items()
    {
        $collection = new SavedServiceCollection($this->user->user_id);

        $service = $collection->addItem($this->product->product_id, 2);

        $this->assertInstanceOf(SavedService::class, $service);
        $this->assertEquals(2, $collection->total_items);
    }

    /** @test */
    public function saved_service_collection_can_remove_items()
    {
        $service = SavedService::saveService($this->user->user_id, $this->product->product_id, 2);
        $collection = new SavedServiceCollection($this->user->user_id);

        $result = $collection->removeItem($service->saved_service_id);

        $this->assertTrue($result);
        $this->assertTrue($collection->isEmpty());
    }

    /** @test */
    public function it_handles_edge_cases_gracefully()
    {
        // Test with invalid user ID
        $this->expectException(\InvalidArgumentException::class);
        SavedService::getOrCreateCart(null);
    }

    /** @test */
    public function it_prevents_unauthorized_access()
    {
        $otherUser = User::factory()->create();
        $savedService = SavedService::saveService(
            $this->user->user_id,
            $this->product->product_id
        );

        // Try to remove service with different user ID
        $result = SavedService::removeService(
            $otherUser->user_id,
            $savedService->saved_service_id
        );

        $this->assertFalse($result);
        $this->assertDatabaseHas('saved_services', [
            'saved_service_id' => $savedService->saved_service_id
        ]);
    }

    /** @test */
    public function it_maintains_relationships_correctly()
    {
        $savedService = SavedService::saveService(
            $this->user->user_id,
            $this->product->product_id
        );

        // Test relationships
        $this->assertInstanceOf(User::class, $savedService->user);
        $this->assertEquals($this->user->user_id, $savedService->user->user_id);

        $this->assertInstanceOf(Product::class, $savedService->product);
        $this->assertEquals($this->product->product_id, $savedService->product->product_id);
    }

    /** @test */
    public function it_handles_large_quantities_correctly()
    {
        $savedService = SavedService::saveService(
            $this->user->user_id,
            $this->product->product_id,
            100
        );

        $this->assertEquals(100, $savedService->quantity);
        $this->assertEquals(10000.00, $savedService->total_price);
    }

    /** @test */
    public function it_formats_prices_correctly()
    {
        $savedService = SavedService::saveService(
            $this->user->user_id,
            $this->product->product_id,
            1
        );

        $this->assertEquals('₱100.00', $savedService->formatted_unit_price);
        $this->assertEquals('₱100.00', $savedService->formatted_total_price);
    }

    /** @test */
    public function it_scopes_services_correctly()
    {
        // Create services for different users
        $otherUser = User::factory()->create();
        
        SavedService::saveService($this->user->user_id, $this->product->product_id);
        SavedService::saveService($otherUser->user_id, $this->product->product_id);

        // Test user scope
        $userServices = SavedService::forUser($this->user->user_id)->get();
        $this->assertEquals(1, $userServices->count());

        // Test recent scope
        $recentServices = SavedService::recent()->get();
        $this->assertGreaterThan(0, $recentServices->count());
    }

    /** @test */
    public function it_handles_database_errors_gracefully()
    {
        // Test with non-existent product
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        SavedService::saveService(
            $this->user->user_id,
            'non-existent-product-id'
        );
    }

    /** @test */
    public function it_maintains_data_integrity()
    {
        $customization = CustomizationOption::factory()->create([
            'product_id' => $this->product->product_id
        ]);

        $savedService = SavedService::saveService(
            $this->user->user_id,
            $this->product->product_id,
            1,
            [$customization->option_id]
        );

        // Verify pivot table data
        $this->assertDatabaseHas('saved_service_customizations', [
            'saved_service_id' => $savedService->saved_service_id,
            'option_id' => $customization->option_id
        ]);
    }
}
