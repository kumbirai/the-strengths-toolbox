<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  int  $maxAttempts  Maximum number of attempts
     * @param  int  $decayMinutes  Number of minutes to wait before reset
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request);

        // Check current attempts
        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
            ], 429)
                ->header('Retry-After', $decayMinutes * 60);
        }

        // Increment attempts
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));

        // Add rate limit headers to response
        $response = $next($request);

        $remaining = $maxAttempts - ($attempts + 1);

        return $response->header('X-RateLimit-Limit', $maxAttempts)
            ->header('X-RateLimit-Remaining', max(0, $remaining));
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Use IP address as identifier
        // You can also use user ID if authenticated
        if ($user = $request->user()) {
            return 'rate_limit:'.$user->id.':'.$request->path();
        }

        return 'rate_limit:'.$request->ip().':'.$request->path();
    }
}
