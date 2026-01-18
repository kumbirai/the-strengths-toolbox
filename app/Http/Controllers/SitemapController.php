<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Page;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    protected int $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = config('cache.ttl.sitemap', 3600); // 1 hour
    }

    /**
     * Generate and return XML sitemap
     */
    public function index(): Response
    {
        $sitemap = Cache::remember('sitemap.xml', $this->cacheTtl, function () {
            return $this->generateSitemap();
        });

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    /**
     * Generate sitemap XML content
     */
    protected function generateSitemap(): string
    {
        $urls = [];

        // Homepage
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '1.0',
        ];

        // Static pages
        $staticPages = [
            ['url' => route('about-us'), 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['url' => route('strengths-programme'), 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['url' => route('contact'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => route('booking'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => route('blog.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('testimonials'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => route('books'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => route('keynote-talks'), 'priority' => '0.7', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => $page['url'],
                'lastmod' => now()->toAtomString(),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ];
        }

        // Published pages from CMS
        $pages = Page::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($pages as $page) {
            // Skip if page is already in static pages
            $pageUrl = url('/'.$page->slug);
            $isStatic = collect($staticPages)->contains(function ($static) use ($pageUrl) {
                return $static['url'] === $pageUrl;
            });

            if (! $isStatic) {
                $urls[] = [
                    'loc' => $pageUrl,
                    'lastmod' => $page->updated_at->toAtomString(),
                    'changefreq' => $this->determineChangeFreq($page->updated_at),
                    'priority' => '0.8',
                ];
            }
        }

        // Published blog posts
        $posts = BlogPost::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->get();

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => $post->updated_at->toAtomString(),
                'changefreq' => $this->determineChangeFreq($post->updated_at),
                'priority' => '0.7',
            ];
        }

        // Blog categories
        $categories = Category::has('blogPosts')->get();
        foreach ($categories as $category) {
            $urls[] = [
                'loc' => route('blog.category', $category->slug),
                'lastmod' => $category->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        return $this->renderSitemap($urls);
    }

    /**
     * Determine change frequency based on last update
     *
     * @param  \Illuminate\Support\Carbon  $updatedAt
     */
    protected function determineChangeFreq($updatedAt): string
    {
        $daysSinceUpdate = $updatedAt->diffInDays(now());

        if ($daysSinceUpdate < 7) {
            return 'daily';
        } elseif ($daysSinceUpdate < 30) {
            return 'weekly';
        } elseif ($daysSinceUpdate < 365) {
            return 'monthly';
        } else {
            return 'yearly';
        }
    }

    /**
     * Render sitemap XML
     */
    protected function renderSitemap(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8')."</loc>\n";

            if (isset($url['lastmod'])) {
                $xml .= '    <lastmod>'.htmlspecialchars($url['lastmod'], ENT_XML1, 'UTF-8')."</lastmod>\n";
            }

            if (isset($url['changefreq'])) {
                $xml .= '    <changefreq>'.htmlspecialchars($url['changefreq'], ENT_XML1, 'UTF-8')."</changefreq>\n";
            }

            if (isset($url['priority'])) {
                $xml .= '    <priority>'.htmlspecialchars($url['priority'], ENT_XML1, 'UTF-8')."</priority>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Clear sitemap cache
     */
    public static function clearCache(): void
    {
        Cache::forget('sitemap.xml');
    }
}
