<?php

namespace Tests\Unit\Services;

use App\Services\ChatbotRateLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChatbotRateLimitServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ChatbotRateLimitService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ChatbotRateLimitService;
        Cache::flush();
    }

    public function test_check_rate_limit_allows_within_limit(): void
    {
        $result = $this->service->checkRateLimit('test-session-123', 'session');

        $this->assertTrue($result['allowed']);
        $this->assertGreaterThan(0, $result['remaining']);
    }

    public function test_check_rate_limit_blocks_over_limit(): void
    {
        $sessionId = 'test-session-'.uniqid();

        // Exceed limit
        for ($i = 0; $i < 11; $i++) {
            $this->service->checkRateLimit($sessionId, 'session');
        }

        $result = $this->service->checkRateLimit($sessionId, 'session');

        $this->assertFalse($result['allowed']);
        $this->assertEquals(0, $result['remaining']);
    }

    public function test_get_rate_limit_status_returns_correct_info(): void
    {
        $result = $this->service->getRateLimitStatus('test-session-123', 'session');

        $this->assertArrayHasKey('allowed', $result);
        $this->assertArrayHasKey('remaining', $result);
        $this->assertArrayHasKey('reset_at', $result);
    }

    public function test_reset_rate_limit_clears_limit(): void
    {
        $sessionId = 'test-session-'.uniqid();

        // Use some requests
        $this->service->checkRateLimit($sessionId, 'session');

        $this->service->resetRateLimit($sessionId, 'session');

        // Should be able to use again
        $result = $this->service->checkRateLimit($sessionId, 'session');
        $this->assertTrue($result['allowed']);
    }

    public function test_check_conversation_rate_limit(): void
    {
        $conversationId = 1;

        $result = $this->service->checkRateLimit($conversationId, 'conversation');

        $this->assertTrue($result['allowed']);
    }

    public function test_check_user_rate_limit(): void
    {
        $userId = 1;

        $result = $this->service->checkRateLimit($userId, 'user');

        $this->assertTrue($result['allowed']);
    }

    public function test_check_ip_rate_limit(): void
    {
        $ipAddress = '127.0.0.1';

        $result = $this->service->checkRateLimit($ipAddress, 'ip');

        $this->assertTrue($result['allowed']);
    }
}
