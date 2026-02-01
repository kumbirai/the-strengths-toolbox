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
        // Check if behind a proxy with HTTPS (like ngrok)
        $isProxiedHttps = $request->header('X-Forwarded-Proto') === 'https' 
                       || $request->header('X-Forwarded-Ssl') === 'on';

        // Force HTTPS URLs when behind HTTPS proxy or in production
        if ($isProxiedHttps || app()->environment('production')) {
            \URL::forceScheme('https');
            
            // Only redirect in production if not already secure
            if (app()->environment('production') && ! $request->secure() && ! $isProxiedHttps) {
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $next($request);
    }
}
