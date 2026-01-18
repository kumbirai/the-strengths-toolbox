<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCacheHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Don't cache in development
        if (app()->environment('local')) {
            return $response;
        }

        // Don't cache admin area
        if ($request->is('admin*')) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');

            return $response;
        }

        // Static assets (handled by web server, but set headers as fallback)
        if ($request->is('build/*') || $request->is('images/*') || $request->is('css/*') || $request->is('js/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');

            return $response;
        }

        // HTML pages - shorter cache with revalidation
        if ($response->headers->get('Content-Type') && str_contains($response->headers->get('Content-Type'), 'text/html')) {
            // Cache for 1 hour, but allow revalidation
            $response->headers->set('Cache-Control', 'public, max-age=3600, must-revalidate');

            // Set ETag based on content hash
            $etag = md5($response->getContent());
            $response->setEtag($etag);

            // Check if client has cached version
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        return $response;
    }
}
