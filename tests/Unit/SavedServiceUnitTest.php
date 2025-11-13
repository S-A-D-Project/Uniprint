<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\SavedService;
use App\Models\Product;
use App\Models\User;
use App\Models\CustomizationOption;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SavedServiceUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function saved_service_can_calculate_total_price()
    {
        $savedService = new SavedService([
            'unit_price' => 50.00,
            'quantity' => 3
        ]);

        $this->assertEquals(150.00, $savedService->calculateTotalPrice());
    }

    /** @test */
    public function saved_service_can_be_created_with_customizations()
    {
        $product = Product::factory()->create(['base_price' => 100.00]);
        $user = User::factory()->create();
        
        $customizations = [
            CustomizationOption::factory()->create(['price_modifier' => 25.00])->option_id,
            CustomizationOption::factory()->create(['price_modifier' => 15.00])->option_id
        ];

        $savedService = SavedService::saveService(
            $user->user_id,
            $product->product_id,
            2,
            $customizations,
            'Test instructions'
        );

        $this->assertInstanceOf(SavedService::class, $savedService);
        $this->assertEquals(2, $savedService->quantity);
        $this->assertEquals(140.00, $savedService->unit_price); // 100 + 25 + 15
        $this->assertEquals(280.00, $savedService->total_price); // 140 * 2
    }

    /** @test */
    public function saved_service_can_get_user_services()
    {
        $user = User::factory()->create();
        SavedService::factory()->count(3)->create(['user_id' => $user->user_id]);
        SavedService::factory()->count(2)->create(); // Different user

        $userServices = SavedService::getUserServices($user->user_id);

        $this->assertCount(3, $userServices);
    }

    /** @test */
    public function saved_service_can_get_services_count()
    {
        $user = User::factory()->create();
        SavedService::factory()->count(5)->create(['user_id' => $user->user_id]);

        $count = SavedService::getServicesCount($user->user_id);

        $this->assertEquals(5, $count);
    }

    /** @test */
    public function saved_service_can_get_total_amount()
    {
        $user = User::factory()->create();
        SavedService::factory()->create([
            'user_id' => $user->user_id,
            'total_price' => 100.00
        ]);
        SavedService::factory()->create([
            'user_id' => $user->user_id,
            'total_price' => 150.00
        ]);

        $totalAmount = SavedService::getTotalAmount($user->user_id);

        $this->assertEquals(250.00, $totalAmount);
    }

    /** @test */
    public function saved_service_can_clear_services()
    {
        $user = User::factory()->create();
        SavedService::factory()->count(3)->create(['user_id' => $user->user_id]);

        $result = SavedService::clearServices($user->user_id);

        $this->assertTrue($result);
        $this->assertEquals(0, SavedService::where('user_id', $user->user_id)->count());
    }

    /** @test */
    public function saved_service_can_remove_specific_service()
    {
        $user = User::factory()->create();
        $savedService = SavedService::factory()->create(['user_id' => $user->user_id]);

        $result = SavedService::removeService($user->user_id, $savedService->saved_service_id);

        $this->assertTrue($result);
        $this->assertNull(SavedService::find($savedService->saved_service_id));
    }

    /** @test */
    public function saved_service_updates_existing_service_with_same_customizations()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['base_price' => 50.00]);
        
        // Create first service
        $firstService = SavedService::saveService(
            $user->user_id,
            $product->product_id,
            2,
            ['custom1'],
            'Same instructions'
        );

        // Add same service again
        $secondService = SavedService::saveService(
            $user->user_id,
            $product->product_id,
            3,
            ['custom1'],
            'Same instructions'
        );

        // Should return the same service with updated quantity
        $this->assertEquals($firstService->saved_service_id, $secondService->saved_service_id);
        $this->assertEquals(5, $secondService->quantity); // 2 + 3
    }

    /** @test */
    public function saved_service_creates_new_service_with_different_customizations()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['base_price' => 50.00]);
        
        // Create first service
        $firstService = SavedService::saveService(
            $user->user_id,
            $product->product_id,
            2,
            ['custom1'],
            'Instructions 1'
        );

        // Add service with different customizations
        $secondService = SavedService::saveService(
            $user->user_id,
            $product->product_id,
            3,
            ['custom2'],
            'Instructions 2'
        );

        // Should create a new service
        $this->assertNotEquals($firstService->saved_service_id, $secondService->saved_service_id);
        $this->assertEquals(2, SavedService::where('user_id', $user->user_id)->count());
    }
}
