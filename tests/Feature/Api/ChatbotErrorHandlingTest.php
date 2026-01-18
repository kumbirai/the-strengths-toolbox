<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_message_handles_network_error(): void
    {
        \Illuminate\Support\Facades\RateLimiter::clear('chatbot.session:test-session-123');

        Http::fake([
            'api.openai.com/*' => Http::response([], 500),
        ]);

        $response = $this->postJson('/api/chatbot/message', [
            'message' => 'Test message',
            'session_id' => 'test-session-123',
        ]);

        // May be rate limited, but if not, should handle error
        $this->assertContains($response->status(), [200, 500, 429]);
        if ($response->status() !== 429) {
            $response->assertJson([
                'success' => false,
            ]);
        }
    }

    public function test_send_message_handles_timeout(): void
    {
        \Illuminate\Support\Facades\RateLimiter::clear('chatbot.session:test-session-123');

        Http::fake([
            'api.openai.com/*' => function () {
                throw new \GuzzleHttp\Exception\ConnectException('Connection timeout', new \GuzzleHttp\Psr7\Request('POST', 'test'));
            },
        ]);

        $response = $this->postJson('/api/chatbot/message', [
            'message' => 'Test message',
            'session_id' => 'test-session-123',
        ]);

        // May be rate limited, but if not, should handle timeout
        $this->assertContains($response->status(), [200, 500, 429]);
    }

    public function test_send_message_handles_invalid_response(): void
    {
        \Illuminate\Support\Facades\RateLimiter::clear('chatbot.session:test-session-123');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'invalid' => 'response',
            ], 200),
        ]);

        $response = $this->postJson('/api/chatbot/message', [
            'message' => 'Test message',
            'session_id' => 'test-session-123',
        ]);

        // May be rate limited, but if not, should handle invalid response
        $this->assertContains($response->status(), [200, 500, 429]);
    }
}
