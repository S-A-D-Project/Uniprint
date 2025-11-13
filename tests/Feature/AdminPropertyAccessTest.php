<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class AdminPropertyAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin dashboard loads without undefined property errors
     */
    public function test_admin_dashboard_loads_safely()
    {
        // Create test data to avoid empty collections
        $this->createTestData();
        
        $response = $this->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas(['stats', 'recent_orders', 'recent_users']);
    }

    /**
     * Test admin users page loads without undefined property errors
     */
    public function test_admin_users_loads_safely()
    {
        $this->createTestData();
        
        $response = $this->get('/admin/users');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.users');
        $response->assertViewHas('users');
    }

    /**
     * Test admin orders page loads without undefined property errors
     */
    public function test_admin_orders_loads_safely()
    {
        $this->createTestData();
        
        $response = $this->get('/admin/orders');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.orders');
        $response->assertViewHas('orders');
    }

    /**
     * Test admin products page loads without undefined property errors
     */
    public function test_admin_products_loads_safely()
    {
        $this->createTestData();
        
        $response = $this->get('/admin/products');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.products');
        $response->assertViewHas('products');
    }

    /**
     * Test admin reports page loads without undefined property errors
     */
    public function test_admin_reports_loads_safely()
    {
        $this->createTestData();
        
        $response = $this->get('/admin/reports');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.reports');
        $response->assertViewHas(['revenue_by_month', 'orders_by_status', 'top_enterprises']);
    }

    /**
     * Test admin pages handle empty data gracefully
     */
    public function test_admin_pages_handle_empty_data()
    {
        // Test with no data in database
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);

        $response = $this->get('/admin/users');
        $response->assertStatus(200);

        $response = $this->get('/admin/orders');
        $response->assertStatus(200);

        $response = $this->get('/admin/products');
        $response->assertStatus(200);

        $response = $this->get('/admin/reports');
        $response->assertStatus(200);
    }

    /**
     * Test SafePropertyAccess trait methods
     */
    public function test_safe_property_access_trait()
    {
        $controller = new \App\Http\Controllers\AdminController();
        
        // Test safeGet method
        $object = (object) ['name' => 'Test', 'email' => null];
        
        $this->assertEquals('Test', $controller->safeGet($object, 'name'));
        $this->assertEquals('default', $controller->safeGet($object, 'nonexistent', 'default'));
        $this->assertNull($controller->safeGet($object, 'email'));
        
        // Test safeString method
        $this->assertEquals('Test', $controller->safeString($object, 'name'));
        $this->assertEquals('N/A', $controller->safeString($object, 'nonexistent', 'N/A'));
        
        // Test safeBool method
        $object->is_active = true;
        $object->is_inactive = false;
        $this->assertTrue($controller->safeBool($object, 'is_active'));
        $this->assertFalse($controller->safeBool($object, 'is_inactive'));
        $this->assertFalse($controller->safeBool($object, 'nonexistent'));
        
        // Test safeNumber method
        $object->price = 99.99;
        $object->invalid_price = 'not a number';
        $this->assertEquals('99.99', $controller->safeNumber($object, 'price'));
        $this->assertEquals('0.00', $controller->safeNumber($object, 'invalid_price'));
        $this->assertEquals('0.00', $controller->safeNumber($object, 'nonexistent'));
    }

    /**
     * Test property access with malformed data
     */
    public function test_property_access_with_malformed_data()
    {
        // Insert malformed data to test defensive programming
        DB::table('users')->insert([
            'user_id' => '12345678-1234-1234-1234-123456789012',
            'name' => null,
            'email' => null,
            'created_at' => null,
            'updated_at' => now()
        ]);

        $response = $this->get('/admin/users');
        $response->assertStatus(200);
        
        // Should not throw undefined property errors
        $response->assertSee('Unknown');
    }

    /**
     * Create test data for admin pages
     */
    private function createTestData()
    {
        // Create role types
        DB::table('role_types')->insert([
            ['role_type_id' => '1', 'user_role_type' => 'admin'],
            ['role_type_id' => '2', 'user_role_type' => 'business_user'],
            ['role_type_id' => '3', 'user_role_type' => 'customer'],
        ]);

        // Create test user
        $userId = '12345678-1234-1234-1234-123456789012';
        DB::table('users')->insert([
            'user_id' => $userId,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create role for user
        DB::table('roles')->insert([
            'role_id' => '1',
            'user_id' => $userId,
            'role_type_id' => '1'
        ]);

        // Create test enterprise
        $enterpriseId = '87654321-4321-4321-4321-210987654321';
        DB::table('enterprises')->insert([
            'enterprise_id' => $enterpriseId,
            'name' => 'Test Enterprise',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create test product
        DB::table('products')->insert([
            'product_id' => '11111111-1111-1111-1111-111111111111',
            'enterprise_id' => $enterpriseId,
            'product_name' => 'Test Product',
            'base_price' => 99.99,
            'is_available' => true,
            'description_text' => 'Test description',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create test order
        $orderId = '22222222-2222-2222-2222-222222222222';
        DB::table('customer_orders')->insert([
            'purchase_order_id' => $orderId,
            'customer_id' => $userId,
            'enterprise_id' => $enterpriseId,
            'order_no' => 'ORD-001',
            'total_order_amount' => 199.99,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create status
        DB::table('statuses')->insert([
            'status_id' => '1',
            'status_name' => 'Pending'
        ]);

        // Create order status history
        DB::table('order_status_history')->insert([
            'purchase_order_id' => $orderId,
            'status_id' => '1',
            'timestamp' => now()
        ]);

        // Create transaction
        DB::table('transactions')->insert([
            'transaction_id' => '33333333-3333-3333-3333-333333333333',
            'amount' => 199.99,
            'transaction_date' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
