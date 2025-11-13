<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->createTestData();
    }

    private function createTestData()
    {
        // Create test enterprises
        for ($i = 0; $i < 10; $i++) {
            $enterpriseId = Str::uuid();
            DB::table('enterprises')->insert([
                'enterprise_id' => $enterpriseId,
                'name' => "Enterprise {$i}",
                'description' => "Description for enterprise {$i}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create products for each enterprise
            for ($j = 0; $j < 5; $j++) {
                DB::table('products')->insert([
                    'product_id' => Str::uuid(),
                    'enterprise_id' => $enterpriseId,
                    'name' => "Product {$i}-{$j}",
                    'description' => "Description for product {$i}-{$j}",
                    'price' => rand(10, 100),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function test_enterprises_list_performance()
    {
        $startTime = microtime(true);
        
        $response = $this->get('/enterprises');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $response->assertStatus(200);
        
        // Assert performance requirements
        $this->assertLessThan(1.0, $executionTime, 'Enterprises list should load in under 1 second');
        $this->assertLessThan(10, DB::getQueryLog(), 'Enterprises list should use less than 10 queries');
    }

    public function test_database_query_optimization()
    {
        // Enable query logging
        DB::enableQueryLog();
        
        $response = $this->get('/enterprises');
        
        $queryCount = count(DB::getQueryLog());
        
        // Should use optimized queries (less than 5 for enterprises list)
        $this->assertLessThan(5, $queryCount, 'Should use optimized queries');
        
        DB::disableQueryLog();
    }

    public function test_memory_usage()
    {
        $startMemory = memory_get_usage(true);
        
        $response = $this->get('/enterprises');
        
        $endMemory = memory_get_usage(true);
        $memoryUsed = $endMemory - $startMemory;
        
        $response->assertStatus(200);
        
        // Assert memory usage is reasonable (less than 50MB)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Memory usage should be less than 50MB');
    }

    public function test_n_plus_one_query_prevention()
    {
        DB::enableQueryLog();
        
        $response = $this->get('/enterprises');
        
        $queries = DB::getQueryLog();
        
        // Check that we're not doing N+1 queries
        $enterpriseQueries = array_filter($queries, function($query) {
            return str_contains($query['query'], 'enterprises');
        });
        
        $productQueries = array_filter($queries, function($query) {
            return str_contains($query['query'], 'products');
        });
        
        // Should have 1 query for enterprises, not N queries
        $this->assertCount(1, $enterpriseQueries, 'Should have only 1 enterprise query');
        
        // Should not have N queries for products (should be optimized)
        $this->assertLessThan(5, count($productQueries), 'Should not have N+1 product queries');
        
        DB::disableQueryLog();
    }

    public function test_database_connection_performance()
    {
        $startTime = microtime(true);
        
        // Test database connection
        $connection = DB::connection();
        $connection->getPdo();
        
        $endTime = microtime(true);
        $connectionTime = $endTime - $startTime;
        
        // Database connection should be fast
        $this->assertLessThan(0.1, $connectionTime, 'Database connection should be under 100ms');
    }

    public function test_query_performance_with_indexes()
    {
        // Create test user for authenticated routes
        $userId = Str::uuid();
        DB::table('users')->insert([
            'user_id' => $userId,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'position' => 'Customer',
            'department' => 'External',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('login')->insert([
            'login_id' => Str::uuid(),
            'user_id' => $userId,
            'username' => 'testuser',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test login query performance
        $startTime = microtime(true);
        
        $user = DB::table('users')
            ->where('email', 'test@example.com')
            ->first();
        
        $endTime = microtime(true);
        $queryTime = $endTime - $startTime;
        
        $this->assertNotNull($user);
        $this->assertLessThan(0.05, $queryTime, 'User lookup query should be under 50ms');
    }
}
