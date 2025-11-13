<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user and login record
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
            'password' => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_sql_injection_prevention()
    {
        // Test SQL injection attempt
        $maliciousInputs = [
            "admin' OR '1'='1 --",
            "admin'; DROP TABLE users; --",
            "admin' UNION SELECT * FROM users --",
            "admin' AND 1=1 --",
        ];

        foreach ($maliciousInputs as $maliciousInput) {
            $response = $this->post('/login', [
                'username' => $maliciousInput,
                'password' => 'password123',
            ]);

            $response->assertSessionHasErrors('username');
            $this->assertGuest();
        }
    }

    public function test_input_validation()
    {
        // Test invalid username formats
        $invalidUsernames = [
            '', // Empty
            'ab', // Too short
            str_repeat('a', 101), // Too long
            'user@name', // Invalid characters
            'user name', // Spaces
            'user/name', // Slash
        ];

        foreach ($invalidUsernames as $invalidUsername) {
            $response = $this->post('/login', [
                'username' => $invalidUsername,
                'password' => 'password123',
            ]);

            $response->assertSessionHasErrors('username');
        }

        // Test invalid password formats
        $invalidPasswords = [
            '', // Empty
            'short', // Too short
            str_repeat('a', 256), // Too long
        ];

        foreach ($invalidPasswords as $invalidPassword) {
            $response = $this->post('/login', [
                'username' => 'testuser',
                'password' => $invalidPassword,
            ]);

            $response->assertSessionHasErrors('password');
        }
    }

    public function test_session_security()
    {
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        
        // Check that session contains security data
        $this->assertSessionHas('user_id');
        $this->assertSessionHas('ip_address');
        $this->assertSessionHas('user_agent');
        $this->assertSessionHas('last_activity');
        $this->assertSessionHas('_token'); // CSRF token
    }

    public function test_rate_limiting()
    {
        // Test rate limiting on login attempts
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'username' => 'testuser',
                'password' => 'wrongpassword',
            ]);
        }

        // Should be rate limited after 5 attempts
        $response->assertStatus(429);
    }

    public function test_csrf_protection()
    {
        // Test that CSRF protection is active
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
            '_token' => 'invalid-token',
        ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    public function test_user_property_access()
    {
        // Test that user properties are accessed correctly
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        
        // Verify session contains correct user data
        $this->assertEquals('Test User', session('user_name'));
        $this->assertEquals('test@example.com', session('user_email'));
    }

    public function test_error_handling()
    {
        // Test error handling with invalid database connection
        config(['database.default' => 'invalid_connection']);
        
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }
}
