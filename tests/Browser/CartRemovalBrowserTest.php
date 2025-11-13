<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Enterprise;
use App\Models\SavedService;

class CartRemovalBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $user;
    protected $product;
    protected $enterprise;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->enterprise = Enterprise::factory()->create();
        $this->product = Product::factory()->create([
            'enterprise_id' => $this->enterprise->enterprise_id,
            'product_name' => 'Test Product',
            'base_price' => 100.00
        ]);
        $this->user = User::factory()->create([
            'role_type' => 'customer',
            'email' => 'test@example.com'
        ]);
    }

    /** @test */
    public function user_can_save_service_from_product_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit("/products/{$this->product->product_id}")
                    ->waitFor('#saveServiceBtn')
                    ->assertSee('Save Service')
                    ->assertDontSee('Add to Cart')
                    ->click('#saveServiceBtn')
                    ->waitFor('.toast-notification', 5)
                    ->assertSee('Service saved successfully');
        });

        // Verify service was saved in database
        $this->assertDatabaseHas('saved_services', [
            'user_id' => $this->user->user_id,
            'product_id' => $this->product->product_id
        ]);
    }

    /** @test */
    public function user_can_view_saved_services()
    {
        // Create saved service
        SavedService::factory()->create([
            'user_id' => $this->user->user_id,
            'product_id' => $this->product->product_id
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/saved-services')
                    ->assertSee('Saved Services')
                    ->assertSee($this->product->product_name)
                    ->assertSee('Order Summary')
                    ->assertSee('Proceed to Checkout');
        });
    }

    /** @test */
    public function user_can_update_service_quantity()
    {
        $savedService = SavedService::factory()->create([
            'user_id' => $this->user->user_id,
            'product_id' => $this->product->product_id,
            'quantity' => 1
        ]);

        $this->browse(function (Browser $browser) use ($savedService) {
            $browser->loginAs($this->user)
                    ->visit('/saved-services')
                    ->waitFor('.quantity-input')
                    ->clear('.quantity-input')
                    ->type('.quantity-input', '3')
                    ->keys('.quantity-input', '{tab}') // Trigger change event
                    ->pause(2000) // Wait for AJAX
                    ->refresh()
                    ->assertInputValue('.quantity-input', '3');
        });
    }

    /** @test */
    public function user_can_remove_saved_service()
    {
        $savedService = SavedService::factory()->create([
            'user_id' => $this->user->user_id,
            'product_id' => $this->product->product_id
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/saved-services')
                    ->waitFor('.remove-item-form button')
                    ->click('.remove-item-form button')
                    ->pause(1000) // Wait for removal
                    ->assertSee('No saved services found');
        });
    }

    /** @test */
    public function saved_services_count_updates_in_header()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit("/products/{$this->product->product_id}")
                    ->waitFor('#saveServiceBtn')
                    ->click('#saveServiceBtn')
                    ->pause(2000) // Wait for AJAX
                    ->waitFor('.saved-services-count')
                    ->assertSeeIn('.saved-services-count', '1');
        });
    }

    /** @test */
    public function checkout_process_works_with_saved_services()
    {
        // Create saved service
        SavedService::factory()->create([
            'user_id' => $this->user->user_id,
            'product_id' => $this->product->product_id,
            'quantity' => 2,
            'total_price' => 200.00
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/saved-services')
                    ->click('a[href*="checkout"]')
                    ->waitForLocation('/checkout')
                    ->assertSee('Checkout')
                    ->assertSee($this->product->product_name)
                    ->assertSee('₱200.00');
        });
    }

    /** @test */
    public function customer_dashboard_shows_saved_services()
    {
        SavedService::factory()->count(3)->create([
            'user_id' => $this->user->user_id
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/customer/dashboard')
                    ->assertSee('Customer Dashboard')
                    ->assertSee('3'); // Should show count of saved services
        });
    }

    /** @test */
    public function no_cart_elements_visible_on_pages()
    {
        $pages = [
            "/products/{$this->product->product_id}",
            '/saved-services',
            '/customer/dashboard'
        ];

        $this->browse(function (Browser $browser) use ($pages) {
            $browser->loginAs($this->user);
            
            foreach ($pages as $page) {
                $browser->visit($page)
                        ->assertDontSee('Add to Cart')
                        ->assertDontSee('Shopping Cart')
                        ->assertDontSee('cart-count');
            }
        });
    }

    /** @test */
    public function buy_now_functionality_works()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit("/products/{$this->product->product_id}")
                    ->waitFor('button[onclick="buyNow()"]')
                    ->click('button[onclick="buyNow()"]')
                    ->pause(3000) // Wait for save and redirect
                    ->assertPathIs('/saved-services')
                    ->assertSee($this->product->product_name);
        });
    }

    /** @test */
    public function responsive_design_works_on_mobile()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 667) // iPhone size
                    ->loginAs($this->user)
                    ->visit('/saved-services')
                    ->assertSee('Saved Services')
                    ->visit("/products/{$this->product->product_id}")
                    ->assertSee('Save Service')
                    ->assertVisible('#saveServiceBtn');
        });
    }

    /** @test */
    public function error_handling_works_for_invalid_requests()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/saved-services')
                    ->script([
                        'fetch("/saved-services/invalid-id", {
                            method: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").getAttribute("content")
                            }
                        }).then(response => response.json()).then(data => {
                            if (!data.success) {
                                alert("Error handled correctly");
                            }
                        });'
                    ])
                    ->pause(2000);
        });
    }
}
