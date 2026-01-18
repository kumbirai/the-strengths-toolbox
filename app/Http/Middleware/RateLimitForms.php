<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limit form submissions to prevent spam
 */
class RateLimitForms
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'form-submission:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => [
                        'rate_limit' => [
                            "Too many form submissions. Please try again in {$seconds} seconds.",
                        ],
                    ],
                ], 429);
            }

            return back()
                ->withInput()
                ->withErrors(['rate_limit' => "Too many form submissions. Please try again in {$seconds} seconds."]);
        }

        RateLimiter::hit($key, 60); // 5 attempts per minute

        return $next($request);
    }
}
