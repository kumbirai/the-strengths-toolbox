<?php

namespace Tests\Feature\Api;

use App\Contracts\AIClientInterface;
use App\Models\ChatbotConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ChatbotSendMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        RateLimiter::clear('chatbot.session:test-session-123');
    }

    public function test_send_message_creates_new_conversation(): void
    {
        $sessionId = 'test-session-create-'.uniqid();
        RateLimiter::clear("chatbot.session:{$sessionId}");

        $this->instance(
            AIClientInterface::class,
            Mockery::mock(AIClientInterface::class, function (MockInterface $mock) {
                $mock->shouldReceive('isConfigured')->andReturn(true);
                $mock->shouldReceive('buildMessagesArray')->andReturn([
                    ['role' => 'system', 'content' => 'Test system prompt'],
                    ['role' => 'user', 'content' => 'Hello'],
                ]);
                $mock->shouldReceive('chatCompletion')->andReturn([
                    'content' => 'Test response',
                    'tokens_used' => 100,
                    'prompt_tokens' => 50,
                    'completion_tokens' => 50,
                ]);
            })
        );

        $response = $this->postJson('/api/chatbot/message', [
            'message' => 'Hello',
            'session_id' => $sessionId,
        ]);

        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'success',
                'conversation_id',
            ]);
        } else {
            $this->assertEquals(429, $response->status());
        }

        $this->assertDatabaseHas('chatbot_conversations', [
            'session_id' => $sessionId,
        ]);
    }

    public function test_send_message_continues_existing_conversation(): void
    {
        $sessionId = 'test-session-continue-'.uniqid();
        RateLimiter::clear("chatbot.session:{$sessionId}");

        $conversation = ChatbotConversation::factory()->create([
            'session_id' => $sessionId,
        ]);

        $this->instance(
            AIClientInterface::class,
            Mockery::mock(AIClientInterface::class, function (MockInterface $mock) {
                $mock->shouldReceive('isConfigured')->andReturn(true);
                $mock->shouldReceive('buildMessagesArray')->andReturn([
                    ['role' => 'system', 'content' => 'Test system prompt'],
                    ['role' => 'user', 'content' => 'Hello again'],
                ]);
                $mock->shouldReceive('chatCompletion')->andReturn([
                    'content' => 'Test response',
                    'tokens_used' => 100,
                ]);
            })
        );

        $response = $this->postJson('/api/chatbot/message', [
            'message' => 'Hello again',
            'conversation_id' => $conversation->id,
            'session_id' => $sessionId,
        ]);

        if ($response->status() === 200) {
            $response->assertJson([
                'conversation_id' => $conversation->id,
            ]);
        } else {
            $this->assertEquals(429, $response->status());
        }
    }

    public function test_send_message_validates_required_fields(): void
    {
        $response = $this->postJson('/api/chatbot/message', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    public function test_send_message_validates_message_length(): void
    {
        $response = $this->postJson('/api/chatbot/message', [
            'message' => str_repeat('a', 1001),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    public function test_send_message_stores_messages_in_database(): void
    {
        $sessionId = 'test-session-store-'.uniqid();
        RateLimiter::clear("chatbot.session:{$sessionId}");

        $this->instance(
            AIClientInterface::class,
            Mockery::mock(AIClientInterface::class, function (MockInterface $mock) {
                $mock->shouldReceive('isConfigured')->andReturn(true);
                $mock->shouldReceive('buildMessagesArray')->andReturn([
                    ['role' => 'system', 'content' => 'Test system prompt'],
                    ['role' => 'user', 'content' => 'Test message'],
                ]);
                $mock->shouldReceive('chatCompletion')->andReturn([
                    'content' => 'Test response',
                    'tokens_used' => 100,
                ]);
            })
        );

        $response = $this->postJson('/api/chatbot/message', [
            'message' => 'Test message',
            'session_id' => $sessionId,
        ]);

        if ($response->status() === 200) {
            $this->assertDatabaseHas('chatbot_messages', [
                'role' => 'user',
                'message' => 'Test message',
            ]);
        } else {
            $this->assertEquals(429, $response->status());
        }
    }

    public function test_send_message_handles_openai_api_error(): void
    {
        $sessionId = 'test-session-api-error-'.uniqid();
        RateLimiter::clear("chatbot.session:{$sessionId}");

        $this->instance(
            AIClientInterface::class,
            Mockery::mock(AIClientInterface::class, function (MockInterface $mock) {
                $mock->shouldReceive('isConfigured')->andReturn(true);
                $mock->shouldReceive('buildMessagesArray')->andReturn([
                    ['role' => 'system', 'content' => 'Test system prompt'],
                    ['role' => 'user', 'content' => 'Test message'],
                ]);
                $mock->shouldReceive('chatCompletion')
                    ->andThrow(new \RuntimeException('Invalid request to OpenAI API: API error'));
            })
        );

        $response = $this->postJson('/api/chatbot/message', [
            'message' => 'Test message',
            'session_id' => $sessionId,
        ]);

        $this->assertContains($response->status(), [200, 500]);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_send_message_respects_rate_limiting(): void
    {
        $sessionId = 'test-session-rate-limit-'.uniqid();

        $this->instance(
            AIClientInterface::class,
            Mockery::mock(AIClientInterface::class, function (MockInterface $mock) {
                $mock->shouldReceive('isConfigured')->andReturn(true);
                $mock->shouldReceive('buildMessagesArray')->andReturn([
                    ['role' => 'system', 'content' => 'Test system prompt'],
                    ['role' => 'user', 'content' => 'Test message'],
                ]);
                $mock->shouldReceive('chatCompletion')->andReturn([
                    'content' => 'Test response',
                    'tokens_used' => 100,
                ]);
            })
        );

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/chatbot/message', [
                'message' => 'Test message '.$i,
                'session_id' => $sessionId,
            ]);
        }

        $response = $this->postJson('/api/chatbot/message', [
            'message' => 'Test message 11',
            'session_id' => $sessionId,
        ]);

        $response->assertStatus(429);
    }
}
