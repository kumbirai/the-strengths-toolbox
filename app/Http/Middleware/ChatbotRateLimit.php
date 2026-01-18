<?php

namespace App\Http\Middleware;

use App\Services\ChatbotRateLimitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChatbotRateLimit
{
    protected ChatbotRateLimitService $rateLimitService;

    public function __construct(ChatbotRateLimitService $rateLimitService)
    {
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = $request->input('session_id') ?? session()->getId();
        $userId = auth()->id();
        $ipAddress = $request->ip();

        // Check session rate limit
        $rateLimit = $this->rateLimitService->checkRateLimit($sessionId, 'session');

        if (! $rateLimit['allowed']) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded. Please try again later.',
                'error' => 'rate_limit_exceeded',
                'rate_limit' => [
                    'limit' => $rateLimit['limit'],
                    'remaining' => 0,
                    'reset_at' => \Carbon\Carbon::createFromTimestamp($rateLimit['reset_at'])->toIso8601String(),
                ],
            ], 429)->withHeaders([
                'X-RateLimit-Limit' => $rateLimit['limit'],
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => $rateLimit['reset_at'],
                'Retry-After' => $rateLimit['reset_at'] - now()->timestamp,
            ]);
        }

        $response = $next($request);

        // Add rate limit headers
        return $response->withHeaders([
            'X-RateLimit-Limit' => $rateLimit['limit'] ?? 10,
            'X-RateLimit-Remaining' => $rateLimit['remaining'] ?? 0,
            'X-RateLimit-Reset' => $rateLimit['reset_at'] ?? now()->addMinute()->timestamp,
        ]);
    }
}
