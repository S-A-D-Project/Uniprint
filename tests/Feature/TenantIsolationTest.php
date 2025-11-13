<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Enterprise;
use App\Models\Product;
use App\Models\CustomerOrder;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tenant Isolation Test
 * 
 * Tests multi-enterprise data isolation and security
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $business1;
    protected User $business2;
    protected User $customer1;
    protected User $customer2;
    protected Enterprise $enterprise1;
    protected Enterprise $enterprise2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create enterprises
        $this->enterprise1 = Enterprise::factory()->create(['enterprise_name' => 'Shop 1']);
        $this->enterprise2 = Enterprise::factory()->create(['enterprise_name' => 'Shop 2']);

        // Create users
        $this->admin = User::factory()->create(['role_type' => 'admin']);
        
        $this->business1 = User::factory()->create(['role_type' => 'business_user']);
        Staff::factory()->create([
            'user_id' => $this->business1->user_id,
            'enterprise_id' => $this->enterprise1->enterprise_id,
        ]);

        $this->business2 = User::factory()->create(['role_type' => 'business_user']);
        Staff::factory()->create([
            'user_id' => $this->business2->user_id,
            'enterprise_id' => $this->enterprise2->enterprise_id,
        ]);

        $this->customer1 = User::factory()->create(['role_type' => 'customer']);
        $this->customer2 = User::factory()->create(['role_type' => 'customer']);
    }

    /** @test */
    public function admin_can_see_all_products()
    {
        Product::factory()->create(['enterprise_id' => $this->enterprise1->enterprise_id]);
        Product::factory()->create(['enterprise_id' => $this->enterprise2->enterprise_id]);

        $this->actingAs($this->admin);
        
        $products = Product::all();
        
        $this->assertCount(2, $products);
    }

    /** @test */
    public function business_user_can_only_see_own_enterprise_products()
    {
        Product::factory()->create(['enterprise_id' => $this->enterprise1->enterprise_id]);
        Product::factory()->create(['enterprise_id' => $this->enterprise2->enterprise_id]);

        $this->actingAs($this->business1);
        
        $products = Product::all();
        
        $this->assertCount(1, $products);
        $this->assertEquals($this->enterprise1->enterprise_id, $products->first()->enterprise_id);
    }

    /** @test */
    public function business_user_cannot_access_other_enterprise_products()
    {
        $product = Product::factory()->create(['enterprise_id' => $this->enterprise2->enterprise_id]);

        $this->actingAs($this->business1);
        
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        
        Product::findOrFail($product->product_id);
    }

    /** @test */
    public function customer_can_only_see_own_orders()
    {
        CustomerOrder::factory()->create([
            'customer_account_id' => $this->customer1->user_id,
            'enterprise_id' => $this->enterprise1->enterprise_id,
        ]);
        
        CustomerOrder::factory()->create([
            'customer_account_id' => $this->customer2->user_id,
            'enterprise_id' => $this->enterprise1->enterprise_id,
        ]);

        $this->actingAs($this->customer1);
        
        $orders = CustomerOrder::all();
        
        $this->assertCount(1, $orders);
        $this->assertEquals($this->customer1->user_id, $orders->first()->customer_account_id);
    }

    /** @test */
    public function business_user_can_see_enterprise_orders_only()
    {
        CustomerOrder::factory()->create([
            'customer_account_id' => $this->customer1->user_id,
            'enterprise_id' => $this->enterprise1->enterprise_id,
        ]);
        
        CustomerOrder::factory()->create([
            'customer_account_id' => $this->customer2->user_id,
            'enterprise_id' => $this->enterprise2->enterprise_id,
        ]);

        $this->actingAs($this->business1);
        
        $orders = CustomerOrder::all();
        
        $this->assertCount(1, $orders);
        $this->assertEquals($this->enterprise1->enterprise_id, $orders->first()->enterprise_id);
    }

    /** @test */
    public function customer_cannot_view_other_customer_orders()
    {
        $order = CustomerOrder::factory()->create([
            'customer_account_id' => $this->customer2->user_id,
            'enterprise_id' => $this->enterprise1->enterprise_id,
        ]);

        $response = $this->actingAs($this->customer1)
            ->get(route('customer.order.details', $order->order_id));

        $response->assertStatus(403);
    }

    /** @test */
    public function business_user_cannot_view_other_enterprise_orders()
    {
        $order = CustomerOrder::factory()->create([
            'customer_account_id' => $this->customer1->user_id,
            'enterprise_id' => $this->enterprise2->enterprise_id,
        ]);

        $response = $this->actingAs($this->business1)
            ->get(route('business.order.details', $order->order_id));

        $response->assertStatus(403);
    }

    /** @test */
    public function scopes_can_be_bypassed_with_explicit_method()
    {
        Product::factory()->create(['enterprise_id' => $this->enterprise1->enterprise_id]);
        Product::factory()->create(['enterprise_id' => $this->enterprise2->enterprise_id]);

        $this->actingAs($this->business1);
        
        // With scope
        $scopedProducts = Product::all();
        $this->assertCount(1, $scopedProducts);
        
        // Without scope
        $allProducts = Product::withoutEnterpriseTenant()->get();
        $this->assertCount(2, $allProducts);
    }

    /** @test */
    public function middleware_prevents_cross_tenant_access()
    {
        $product = Product::factory()->create(['enterprise_id' => $this->enterprise2->enterprise_id]);

        $response = $this->actingAs($this->business1)
            ->get("/business/products/{$product->product_id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function policies_enforce_tenant_isolation()
    {
        $product = Product::factory()->create(['enterprise_id' => $this->enterprise2->enterprise_id]);

        $this->actingAs($this->business1);
        
        $this->assertFalse($this->business1->can('view', $product));
        $this->assertFalse($this->business1->can('update', $product));
        $this->assertFalse($this->business1->can('delete', $product));
    }

    /** @test */
    public function admin_bypasses_all_tenant_restrictions()
    {
        $product = Product::factory()->create(['enterprise_id' => $this->enterprise2->enterprise_id]);

        $this->actingAs($this->admin);
        
        $this->assertTrue($this->admin->can('view', $product));
        $this->assertTrue($this->admin->can('update', $product));
        $this->assertTrue($this->admin->can('delete', $product));
    }
}
