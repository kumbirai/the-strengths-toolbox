<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce HTTPS in production
        if (app()->environment('production')) {
            // Check if request is not secure (HTTP)
            if (! $request->secure()) {
                // Redirect to HTTPS version of the same URL
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $next($request);
    }
}
