<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache all (config, routes, views)
     */
    public function cacheAll(): void
    {
        if (app()->environment('production')) {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
        }
    }

    /**
     * Clear all application cache
     */
    public function clearAll(): void
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
    }

    /**
     * Clear cache for a specific tag pattern
     */
    public function clearByPattern(string $pattern): void
    {
        // Note: This works with file cache driver
        // For Redis/Memcached, use cache tags
        $this->clearAll();
    }

    /**
     * Clear page-related caches
     */
    public function clearPages(): void
    {
        Cache::forget('pages.published.all');
        // Individual page caches will expire naturally
    }

    /**
     * Clear blog post-related caches
     */
    public function clearBlogPosts(): void
    {
        $this->clearAll(); // Simplified for file cache
    }

    /**
     * Clear SEO-related caches
     */
    public function clearSEO(): void
    {
        $this->clearAll(); // Simplified for file cache
    }

    /**
     * Warm up frequently accessed caches
     */
    public function warmUp(): void
    {
        // This can be called via a scheduled command
        // Pre-load common data into cache
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        // Basic stats - implementation depends on cache driver
        return [
            'driver' => config('cache.default'),
            'ttl' => config('cache.ttl', []),
        ];
    }
}
