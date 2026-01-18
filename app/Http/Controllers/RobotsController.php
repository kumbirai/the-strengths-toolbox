<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate robots.txt content
     */
    public function index(): Response
    {
        $content = $this->generateRobotsTxt();

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * Generate robots.txt content based on environment
     */
    protected function generateRobotsTxt(): string
    {
        $lines = [];

        // In production, allow all crawlers
        // In staging/development, disallow all crawlers
        if (app()->environment('production')) {
            $lines[] = 'User-agent: *';
            $lines[] = 'Allow: /';
            $lines[] = '';
            $lines[] = '# Disallow admin area';
            $lines[] = 'Disallow: /admin/';
            $lines[] = 'Disallow: /admin';
            $lines[] = '';
            $lines[] = '# Disallow API endpoints';
            $lines[] = 'Disallow: /api/';
            $lines[] = '';
            $lines[] = '# Disallow search results';
            $lines[] = 'Disallow: /search?';
            $lines[] = 'Disallow: /blog/search?';
            $lines[] = '';
            $lines[] = '# Disallow health check endpoints';
            $lines[] = 'Disallow: /health';
            $lines[] = 'Disallow: /health/';
            $lines[] = '';
            $lines[] = '# Allow important paths';
            $lines[] = 'Allow: /blog/';
            $lines[] = 'Allow: /contact';
            $lines[] = 'Allow: /about-us';
            $lines[] = 'Allow: /strengths-programme';
            $lines[] = '';
            $lines[] = '# Sitemap location';
            $lines[] = 'Sitemap: '.config('app.url').'/sitemap.xml';
        } else {
            // Disallow all crawlers in non-production environments
            $lines[] = 'User-agent: *';
            $lines[] = 'Disallow: /';
            $lines[] = '';
            $lines[] = '# This is a '.app()->environment().' environment';
            $lines[] = '# Crawling is disabled';
        }

        return implode("\n", $lines);
    }
}
