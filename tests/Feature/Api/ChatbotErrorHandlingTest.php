<?php

namespace Tests\Feature\Api;

use App\Contracts\AIClientInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ChatbotErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_message_handles_network_error(): void
    {
        $sessionId = 'test-session-network-error-'.uniqid();
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
                    ->andThrow(new \RuntimeException('Network error: Unable to reach OpenAI API'));
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

    public function test_send_message_handles_timeout(): void
    {
        $sessionId = 'test-session-timeout-'.uniqid();
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
                    ->andThrow(new \RuntimeException('Connection timeout'));
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

    public function test_send_message_handles_invalid_response(): void
    {
        $sessionId = 'test-session-invalid-'.uniqid();
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
                    ->andThrow(new \RuntimeException('Invalid response from OpenAI API'));
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
}
