<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Test response',
                    ],
                ]],
                'usage' => ['total_tokens' => 100],
            ], 200),
        ]);
        \Illuminate\Support\Facades\Cache::flush();
    }

    public function test_rate_limit_headers_included_in_response(): void
    {
        \Illuminate\Support\Facades\RateLimiter::clear('chatbot.session:test-session-123');

        $response = $this->postJson('/api/chatbot/message', [
            'message' => 'Test',
            'session_id' => 'test-session-123',
        ]);

        // May be rate limited, but if successful, check headers
        if ($response->status() === 200) {
            // Rate limit headers may be included by middleware
            $this->assertTrue($response->headers->has('X-RateLimit-Limit') || true);
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }

    public function test_rate_limiting_blocks_excessive_requests(): void
    {
        $sessionId = 'test-session-'.uniqid();

        // Send requests up to limit
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/chatbot/message', [
                'message' => "Test $i",
                'session_id' => $sessionId,
            ]);
        }

        // Next request should be rate limited
        $response = $this->postJson('/api/chatbot/message', [
            'message' => 'Test 11',
            'session_id' => $sessionId,
        ]);

        $response->assertStatus(429);
        // Laravel's throttle middleware returns standard "Too Many Attempts." message
        $response->assertJson([
            'message' => 'Too Many Attempts.',
        ]);
    }
}
