<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\SavedService;
use App\Models\Enterprise;

class CartRemovalTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $product;
    protected $enterprise;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test enterprise
        $this->enterprise = Enterprise::factory()->create();
        
        // Create test product
        $this->product = Product::factory()->create([
            'enterprise_id' => $this->enterprise->enterprise_id,
            'base_price' => 100.00
        ]);
        
        // Create test user
        $this->user = User::factory()->create([
            'role_type' => 'customer'
        ]);
    }

    /** @test */
    public function cart_models_do_not_exist()
    {
        // Verify cart models are removed
        $this->assertFalse(class_exists('App\\Models\\ShoppingCart'));
        $this->assertFalse(class_exists('App\\Models\\CartItem'));
    }

    /** @test */
    public function cart_controller_does_not_exist()
    {
        // Verify cart controller is removed
        $this->assertFalse(class_exists('App\\Http\\Controllers\\CartController'));
    }

    /** @test */
    public function cart_routes_do_not_exist()
    {
        // Test that cart routes return 404
        $response = $this->get('/cart');
        $response->assertStatus(404);
        
        $response = $this->post('/cart/add');
        $response->assertStatus(404);
        
        $response = $this->delete('/cart/remove/123');
        $response->assertStatus(404);
    }

    /** @test */
    public function saved_services_functionality_works()
    {
        // Test saving a service
        $response = $this->actingAs($this->user)
            ->post('/saved-services/save', [
                'product_id' => $this->product->product_id,
                'quantity' => 2,
                'customizations' => [],
                'special_instructions' => 'Test instructions'
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify service was saved
        $this->assertDatabaseHas('saved_services', [
            'user_id' => $this->user->user_id,
            'product_id' => $this->product->product_id,
            'quantity' => 2
        ]);
    }

    /** @test */
    public function saved_services_count_works()
    {
        // Create saved services
        SavedService::factory()->count(3)->create([
            'user_id' => $this->user->user_id
        ]);

        $response = $this->actingAs($this->user)
            ->get('/saved-services/count');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 3
        ]);
    }

    /** @test */
    public function saved_services_update_works()
    {
        $savedService = SavedService::factory()->create([
            'user_id' => $this->user->user_id,
            'product_id' => $this->product->product_id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/saved-services/{$savedService->saved_service_id}", [
                'quantity' => 5
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify quantity was updated
        $this->assertDatabaseHas('saved_services', [
            'saved_service_id' => $savedService->saved_service_id,
            'quantity' => 5
        ]);
    }

    /** @test */
    public function saved_services_removal_works()
    {
        $savedService = SavedService::factory()->create([
            'user_id' => $this->user->user_id
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/saved-services/{$savedService->saved_service_id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify service was removed
        $this->assertDatabaseMissing('saved_services', [
            'saved_service_id' => $savedService->saved_service_id
        ]);
    }

    /** @test */
    public function checkout_works_with_saved_services()
    {
        // Create saved services
        SavedService::factory()->count(2)->create([
            'user_id' => $this->user->user_id,
            'product_id' => $this->product->product_id
        ]);

        $response = $this->actingAs($this->user)
            ->get('/checkout');

        $response->assertStatus(200);
        $response->assertViewIs('checkout.index');
        $response->assertViewHas('cartItems');
    }

    /** @test */
    public function customer_dashboard_works_without_cart()
    {
        $response = $this->actingAs($this->user)
            ->get('/customer/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('savedServices');
    }

    /** @test */
    public function product_page_uses_saved_services()
    {
        $response = $this->get("/products/{$this->product->product_id}");

        $response->assertStatus(200);
        $response->assertSee('Save Service');
        $response->assertDontSee('Add to Cart');
    }

    /** @test */
    public function no_cart_references_in_views()
    {
        // Test key pages don't contain cart references
        $pages = [
            "/products/{$this->product->product_id}",
            '/saved-services',
            '/customer/dashboard'
        ];

        foreach ($pages as $page) {
            $response = $this->actingAs($this->user)->get($page);
            $content = $response->getContent();
            
            // Check for cart-related terms that shouldn't exist
            $this->assertStringNotContainsString('addToCart', $content);
            $this->assertStringNotContainsString('cart.add', $content);
            $this->assertStringNotContainsString('updateCartCount', $content);
        }
    }

    /** @test */
    public function database_has_no_cart_tables()
    {
        // Verify cart tables don't exist
        $this->assertFalse(\Schema::hasTable('shopping_carts'));
        $this->assertFalse(\Schema::hasTable('cart_items'));
    }

    /** @test */
    public function performance_benchmark_saved_services()
    {
        // Create multiple saved services for performance testing
        SavedService::factory()->count(100)->create([
            'user_id' => $this->user->user_id
        ]);

        $startTime = microtime(true);
        
        $response = $this->actingAs($this->user)
            ->get('/saved-services');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);
        
        // Assert response time is reasonable (less than 500ms)
        $this->assertLessThan(500, $executionTime, 
            "Saved services page took {$executionTime}ms, which exceeds 500ms threshold");
    }
}
