<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChatbotRateLimitService extends BaseService
{
    protected bool $enabled;

    protected int $perMinute;

    protected int $perHour;

    protected int $perDay;

    protected int $perConversationPerMinute;

    protected int $perUserPerHour;

    public function __construct()
    {
        $this->enabled = config('chatbot.rate_limiting.enabled', true);
        $this->perMinute = config('chatbot.rate_limiting.per_minute', 10);
        $this->perHour = config('chatbot.rate_limiting.per_hour', 60);
        $this->perDay = config('chatbot.rate_limiting.per_day', 200);
        $this->perConversationPerMinute = config('chatbot.rate_limiting.per_conversation_per_minute', 20);
        $this->perUserPerHour = config('chatbot.rate_limiting.per_user_per_hour', 100);
    }

    /**
     * Check if request is within rate limits
     *
     * @param  string  $identifier  Session ID, user ID, or IP address
     * @param  string  $type  Type of rate limit (session, user, ip, conversation)
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_at' => int]
     */
    public function checkRateLimit(string $identifier, string $type = 'session'): array
    {
        if (! $this->enabled) {
            return [
                'allowed' => true,
                'remaining' => PHP_INT_MAX,
                'reset_at' => now()->addHour()->timestamp,
            ];
        }

        switch ($type) {
            case 'conversation':
                return $this->checkConversationRateLimit($identifier);
            case 'user':
                return $this->checkUserRateLimit($identifier);
            case 'ip':
                return $this->checkIpRateLimit($identifier);
            default:
                return $this->checkSessionRateLimit($identifier);
        }
    }

    /**
     * Check session rate limit
     */
    protected function checkSessionRateLimit(string $sessionId): array
    {
        $minuteKey = "chatbot.ratelimit.session.minute.{$sessionId}";
        $hourKey = "chatbot.ratelimit.session.hour.{$sessionId}";
        $dayKey = "chatbot.ratelimit.session.day.{$sessionId}";

        // Check per minute
        $minuteCount = Cache::get($minuteKey, 0);
        if ($minuteCount >= $this->perMinute) {
            return $this->rateLimitExceeded('minute', $this->perMinute, 60);
        }

        // Check per hour
        $hourCount = Cache::get($hourKey, 0);
        if ($hourCount >= $this->perHour) {
            return $this->rateLimitExceeded('hour', $this->perHour, 3600);
        }

        // Check per day
        $dayCount = Cache::get($dayKey, 0);
        if ($dayCount >= $this->perDay) {
            return $this->rateLimitExceeded('day', $this->perDay, 86400);
        }

        // Increment counters
        Cache::put($minuteKey, $minuteCount + 1, 60);
        Cache::put($hourKey, $hourCount + 1, 3600);
        Cache::put($dayKey, $dayCount + 1, 86400);

        return [
            'allowed' => true,
            'remaining' => min(
                $this->perMinute - $minuteCount - 1,
                $this->perHour - $hourCount - 1,
                $this->perDay - $dayCount - 1
            ),
            'reset_at' => now()->addMinute()->timestamp,
        ];
    }

    /**
     * Check conversation rate limit
     */
    protected function checkConversationRateLimit(int $conversationId): array
    {
        $key = "chatbot.ratelimit.conversation.minute.{$conversationId}";
        $count = Cache::get($key, 0);

        if ($count >= $this->perConversationPerMinute) {
            return $this->rateLimitExceeded('conversation_minute', $this->perConversationPerMinute, 60);
        }

        Cache::put($key, $count + 1, 60);

        return [
            'allowed' => true,
            'remaining' => $this->perConversationPerMinute - $count - 1,
            'reset_at' => now()->addMinute()->timestamp,
        ];
    }

    /**
     * Check user rate limit
     */
    protected function checkUserRateLimit(int $userId): array
    {
        $key = "chatbot.ratelimit.user.hour.{$userId}";
        $count = Cache::get($key, 0);

        if ($count >= $this->perUserPerHour) {
            return $this->rateLimitExceeded('user_hour', $this->perUserPerHour, 3600);
        }

        Cache::put($key, $count + 1, 3600);

        return [
            'allowed' => true,
            'remaining' => $this->perUserPerHour - $count - 1,
            'reset_at' => now()->addHour()->timestamp,
        ];
    }

    /**
     * Check IP rate limit
     */
    protected function checkIpRateLimit(string $ipAddress): array
    {
        $key = 'chatbot.ratelimit.ip.hour.'.md5($ipAddress);
        $count = Cache::get($key, 0);

        // Use same limits as session for IP
        if ($count >= $this->perHour) {
            return $this->rateLimitExceeded('ip_hour', $this->perHour, 3600);
        }

        Cache::put($key, $count + 1, 3600);

        return [
            'allowed' => true,
            'remaining' => $this->perHour - $count - 1,
            'reset_at' => now()->addHour()->timestamp,
        ];
    }

    /**
     * Rate limit exceeded response
     */
    protected function rateLimitExceeded(string $type, int $limit, int $resetSeconds): array
    {
        Log::warning('Chatbot rate limit exceeded', [
            'type' => $type,
            'limit' => $limit,
        ]);

        return [
            'allowed' => false,
            'remaining' => 0,
            'reset_at' => now()->addSeconds($resetSeconds)->timestamp,
            'limit' => $limit,
            'type' => $type,
        ];
    }

    /**
     * Get rate limit status
     */
    public function getRateLimitStatus(string $identifier, string $type = 'session'): array
    {
        return $this->checkRateLimit($identifier, $type);
    }

    /**
     * Reset rate limit for identifier
     */
    public function resetRateLimit(string $identifier, string $type = 'session'): void
    {
        $patterns = [
            "chatbot.ratelimit.{$type}.minute.{$identifier}",
            "chatbot.ratelimit.{$type}.hour.{$identifier}",
            "chatbot.ratelimit.{$type}.day.{$identifier}",
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}
