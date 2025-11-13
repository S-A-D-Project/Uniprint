<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Conversation;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $business;
    protected $conversation;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->customer = User::factory()->create([
            'role_type' => 'customer',
            'username' => 'testcustomer',
            'email' => 'customer@test.com'
        ]);

        $this->business = User::factory()->create([
            'role_type' => 'business_user',
            'username' => 'testbusiness',
            'email' => 'business@test.com'
        ]);

        // Create test conversation
        $this->conversation = Conversation::create([
            'customer_id' => $this->customer->user_id,
            'business_id' => $this->business->user_id,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function test_role_type_validation_works_correctly()
    {
        // Test customer-business conversation creation
        Auth::login($this->customer);

        $response = $this->postJson('/api/chat/conversations', [
            'participant_id' => $this->business->user_id
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function test_invalid_role_combination_rejected()
    {
        // Create another customer
        $customer2 = User::factory()->create([
            'role_type' => 'customer'
        ]);

        Auth::login($this->customer);

        $response = $this->postJson('/api/chat/conversations', [
            'participant_id' => $customer2->user_id
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid participants for chat']);
    }

    /** @test */
    public function test_message_input_validation()
    {
        Auth::login($this->customer);

        // Test missing message text
        $response = $this->postJson('/api/chat/messages', [
            'conversation_id' => $this->conversation->conversation_id,
            'message_text' => ''
        ]);

        $response->assertStatus(422); // Validation error

        // Test message too long
        $response = $this->postJson('/api/chat/messages', [
            'conversation_id' => $this->conversation->conversation_id,
            'message_text' => str_repeat('a', 5001) // Exceeds 5000 char limit
        ]);

        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function test_get_messages_input_validation()
    {
        Auth::login($this->customer);

        // Test invalid limit
        $response = $this->getJson("/api/chat/conversations/{$this->conversation->conversation_id}/messages?limit=200");
        $response->assertStatus(422);

        // Test negative offset
        $response = $this->getJson("/api/chat/conversations/{$this->conversation->conversation_id}/messages?offset=-1");
        $response->assertStatus(422);

        // Test invalid conversation ID format
        $response = $this->getJson("/api/chat/conversations/invalid-id/messages");
        $response->assertStatus(400);
    }

    /** @test */
    public function test_unauthorized_access_prevention()
    {
        // Create unrelated user
        $unrelated = User::factory()->create([
            'role_type' => 'customer'
        ]);

        Auth::login($unrelated);

        // Try to access conversation they're not part of
        $response = $this->getJson("/api/chat/conversations/{$this->conversation->conversation_id}/messages");
        $response->assertStatus(403);
        $response->assertJson(['error' => 'Unauthorized access to conversation']);
    }

    /** @test */
    public function test_message_sending_works_correctly()
    {
        Auth::login($this->customer);

        $response = $this->postJson('/api/chat/messages', [
            'conversation_id' => $this->conversation->conversation_id,
            'message_text' => 'Hello, this is a test message!',
            'message_type' => 'text'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify message was created
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $this->conversation->conversation_id,
            'sender_id' => $this->customer->user_id,
            'message_text' => 'Hello, this is a test message!'
        ]);
    }

    /** @test */
    public function test_conversation_participant_identification()
    {
        Auth::login($this->business);

        $response = $this->getJson("/api/chat/conversations/{$this->conversation->conversation_id}");
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'conversation' => [
                'conversation_id' => $this->conversation->conversation_id,
                'customer_id' => $this->customer->user_id,
                'business_id' => $this->business->user_id
            ]
        ]);
    }

    /** @test */
    public function test_message_pagination_limits()
    {
        Auth::login($this->customer);

        // Create multiple messages
        for ($i = 0; $i < 10; $i++) {
            ChatMessage::create([
                'conversation_id' => $this->conversation->conversation_id,
                'sender_id' => $this->customer->user_id,
                'message_text' => "Test message {$i}",
                'message_type' => 'text'
            ]);
        }

        // Test limit is enforced
        $response = $this->getJson("/api/chat/conversations/{$this->conversation->conversation_id}/messages?limit=5");
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(5, $data['messages']);
    }
}
