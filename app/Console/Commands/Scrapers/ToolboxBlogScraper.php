<?php

namespace App\Console\Commands\Scrapers;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Site-specific scraper for The Strengths Toolbox blog
 */
class ToolboxBlogScraper
{
    protected string $baseUrl;
    protected int $delay;

    public function __construct(string $baseUrl, int $delay = 1)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->delay = $delay;
    }

    /**
     * Scrape all blog posts from The Strengths Toolbox site
     */
    public function scrape(callable $progressCallback = null): array
    {
        $inventory = [];
        $page = 1;
        $maxPages = 50;

        while ($page <= $maxPages) {
            $url = $page === 1
                ? $this->baseUrl.'/blog/'
                : $this->baseUrl.'/blog/page/'.$page.'/';

            if ($progressCallback) {
                $progressCallback("Fetching listing page {$page}: {$url}");
            }

            $response = Http::timeout(30)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; WordPress-Blog-Scraper/1.0)'])
                ->get($url);

            if (! $response->successful()) {
                if ($progressCallback) {
                    $progressCallback("HTTP {$response->status()}, stopping.");
                }
                break;
            }

            $html = $response->body();
            $found = $this->extractPostsFromListing($html, $inventory);

            if ($found === 0) {
                if ($progressCallback) {
                    $progressCallback("No posts on page {$page}, stopping.");
                }
                break;
            }

            $page++;
            if ($this->delay > 0) {
                sleep($this->delay);
            }
        }

        // Fetch full content for each post
        if ($progressCallback) {
            $progressCallback("Fetching full content for ".count($inventory).' posts...');
        }

        $contentStats = ['full' => 0, 'short' => 0, 'missing' => 0, 'errors' => 0];
        $total = count($inventory);
        $current = 0;

        foreach ($inventory as $key => $post) {
            $current++;
            if ($progressCallback && $total > 0) {
                $percent = round(($current / $total) * 100);
                $progressCallback("  [{$current}/{$total}] Processing: ".($post['title'] ?? 'Unknown'));
            }
            $url = $post['listing_url'] ?? '';
            if ($url) {
                try {
                    if ($this->delay > 0) {
                        sleep($this->delay);
                    }
                    $response = Http::timeout(30)->get($url);
                    if ($response->successful()) {
                        $html = $response->body();
                        $content = $this->extractArticleBody($html);
                        
                        $textLength = strlen(strip_tags($content));
                        if (empty($content) || $textLength < 200) {
                            $contentStats['missing']++;
                        } elseif ($textLength < 500) {
                            $contentStats['short']++;
                        } else {
                            $contentStats['full']++;
                        }
                        
                        $inventory[$key]['content_html'] = $content;
                        $inventory[$key]['content_length'] = $textLength;
                        $inventory[$key]['author'] = $this->extractAuthor($html);
                        $inventory[$key]['categories'] = $this->extractCategories($html);
                        $inventory[$key]['tags'] = $this->extractTags($html);
                        $inventory[$key]['all_images'] = $this->extractAllImages($html);
                    } else {
                        $contentStats['errors']++;
                    }
                } catch (\Throwable $e) {
                    $contentStats['errors']++;
                }
            }
        }

        return [
            'inventory' => $inventory,
            'stats' => $contentStats,
        ];
    }

    /**
     * Extract posts from listing HTML - The Strengths Toolbox specific
     * Try regex first (more reliable), then DOM parsing
     */
    protected function extractPostsFromListing(string $html, array &$inventory): int
    {
        // First try regex-based extraction (more reliable)
        $count = $this->extractPostsRegex($html, $inventory);
        if ($count > 0) {
            return $count;
        }

        // Fallback to DOM parsing
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Try to find blog post links - use simple query
        $postLinks = $xpath->query(
            '//a[contains(@href, "/blog/") or contains(@href, "/2023/") or contains(@href, "/2024/") or contains(@href, "/2025/")]'
        );

        $seen = [];
        $count = 0;

        foreach ($postLinks as $link) {
            if (! $link instanceof \DOMElement) {
                continue;
            }
            $href = $link->getAttribute('href');
            
            // Skip non-post URLs and common pages
            $skipPatterns = [
                '/category/', '/tag/', '/author/', '/page/', '/wp-', '/feed',
                '/contact', '/booking', '/privacy', '/about', '/testimonials',
                '/books', '/keynote-talks', '/sales-courses', '/strengths-programme',
                '/facilitation', '/search', '/sitemap', '/robots',
            ];
            
            $shouldSkip = false;
            foreach ($skipPatterns as $pattern) {
                if (str_contains($href, $pattern)) {
                    $shouldSkip = true;
                    break;
                }
            }
            
            if ($shouldSkip || 
                str_ends_with($href, '/blog') || 
                str_ends_with($href, '/blog/') ||
                str_ends_with($href, $this->baseUrl) ||
                str_ends_with($href, $this->baseUrl.'/')) {
                continue;
            }

            // Extract slug from various URL patterns
            $slug = null;
            $listingUrl = null;

            // Pattern 1: /blog/slug
            if (preg_match('#/blog/([^/?]+)/?#', $href, $m)) {
                $slug = $m[1];
                $listingUrl = str_starts_with($href, 'http') ? $href : $this->baseUrl.$href;
            }
            // Pattern 2: /2024/11/27/slug
            elseif (preg_match('#/(\d{4})/(\d{2})/(\d{2})/([^/?]+)/?#', $href, $m)) {
                $slug = $m[4];
                $listingUrl = str_starts_with($href, 'http') ? $href : $this->baseUrl.$href;
            }
            // Pattern 3: /2024/11/slug
            elseif (preg_match('#/(\d{4})/(\d{2})/([^/?]+)/?#', $href, $m)) {
                $slug = $m[3];
                $listingUrl = str_starts_with($href, 'http') ? $href : $this->baseUrl.$href;
            }
            // Pattern 4: Direct slug from domain (only if it looks like a blog post)
            // Skip this pattern entirely - too risky for false positives
            // Only accept /blog/slug or date-based patterns

            if (! $slug || ! $listingUrl) {
                continue;
            }

            $key = 'toolbox_'.$slug;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $container = $this->findPostContainer($link, $xpath);
            $title = $this->extractTitle($container, $xpath, $link);
            $excerpt = $this->extractExcerpt($container, $xpath);
            $dateCategory = $this->extractDateAndCategory($container, $xpath);
            $featuredImageUrl = $this->extractFeaturedImageUrl($container, $xpath);

            if ($title === '' && $container) {
                $title = trim($link->textContent ?? '');
            }

            // Validate - must have title and reasonable slug
            if (empty($title) || strlen($title) < 10 || strlen($slug) < 5) {
                continue;
            }
            
            // Additional validation: skip common page titles (use pattern matching)
            $pageTitlePatterns = [
                'contact', 'booking', 'privacy', 'about', 'testimonials', 'books',
                'keynote', 'sales courses', 'sales-courses', 'strengths programme', 'strengths-programme',
                'facilitation', 'search', 'sitemap', 'robots', 'home', 'index',
            ];
            $titleLower = strtolower(trim($title));
            foreach ($pageTitlePatterns as $pattern) {
                // Check if title contains or matches the pattern
                if ($titleLower === $pattern || 
                    str_starts_with($titleLower, $pattern.' ') ||
                    str_ends_with($titleLower, ' '.$pattern) ||
                    str_contains($titleLower, ' '.$pattern.' ')) {
                    continue 2; // Skip this link - it's a page, not a blog post
                }
            }
            
            // Must have /blog/ in URL or be date-based to be considered a blog post
            // This is the most important check - only accept URLs that look like blog posts
            if (! str_contains($listingUrl, '/blog/') && 
                ! preg_match('#/\d{4}/#', $listingUrl)) {
                continue;
            }

            $inventory[$key] = [
                'source_site' => 'toolbox',
                'listing_url' => $listingUrl,
                'slug' => $slug,
                'title' => $title,
                'excerpt' => $excerpt,
                'published_at' => $dateCategory['date'],
                'category' => $dateCategory['category'],
                'featured_image_url' => $featuredImageUrl,
            ];
            $count++;
        }

        return $count;
    }

    /**
     * Extract posts using regex patterns (more reliable for initial discovery)
     */
    protected function extractPostsRegex(string $html, array &$inventory): int
    {
        $seen = [];
        $count = 0;

        // Pattern 1: /blog/slug
        if (preg_match_all('#href=["\']([^"\']*?/blog/([^/?"\']+))["\']#i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $href = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5);
                $slug = $match[2];
                
                // Skip non-post URLs
                if (str_contains($href, '/category/') || 
                    str_contains($href, '/tag/') || 
                    str_contains($href, '/author/') ||
                    str_contains($href, '/page/') ||
                    str_contains($href, '/wp-') ||
                    str_contains($href, '/feed') ||
                    str_ends_with($href, '/blog') ||
                    str_ends_with($href, '/blog/')) {
                    continue;
                }
                
                // Skip common page slugs
                $skipSlugs = ['contact', 'booking', 'privacy', 'about', 'testimonials', 'books',
                             'keynote-talks', 'sales-courses', 'strengths-programme', 'facilitation',
                             'search', 'sitemap', 'robots', 'home', 'index'];
                if (in_array(strtolower($slug), $skipSlugs) || strlen($slug) < 5) {
                    continue;
                }

                $listingUrl = str_starts_with($href, 'http') ? $href : $this->baseUrl.$href;
                $key = 'toolbox_'.$slug;
                
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                // Extract title from surrounding HTML
                $title = $this->extractTitleFromHtml($html, $href, $slug);
                
                if (empty($title) || strlen($title) < 10) {
                    continue;
                }
                
                // Skip common page titles
                $pageTitlePatterns = [
                    'contact', 'booking', 'privacy', 'about', 'testimonials', 'books',
                    'keynote', 'sales courses', 'sales-courses', 'strengths programme', 'strengths-programme',
                    'facilitation', 'search', 'sitemap', 'robots', 'home', 'index',
                ];
                $titleLower = strtolower(trim($title));
                foreach ($pageTitlePatterns as $pattern) {
                    if ($titleLower === $pattern || 
                        str_starts_with($titleLower, $pattern.' ') ||
                        str_ends_with($titleLower, ' '.$pattern) ||
                        str_contains($titleLower, ' '.$pattern.' ')) {
                        continue 2;
                    }
                }

                $inventory[$key] = [
                    'source_site' => 'toolbox',
                    'listing_url' => $listingUrl,
                    'slug' => $slug,
                    'title' => $title,
                    'excerpt' => '',
                    'published_at' => '',
                    'category' => '',
                    'featured_image_url' => '',
                ];
                $count++;
            }
        }

        // Pattern 2: Date-based URLs /2024/11/27/slug (year/month/day/slug) - check this first
        if (preg_match_all('#href=["\']([^"\']*?/(\d{4})/(\d{2})/(\d{2})/([^/?"\']+))["\']#i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $href = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5);
                $slug = $match[5];
                
                // Skip non-post URLs
                if (str_contains($href, '/category/') || 
                    str_contains($href, '/tag/') || 
                    str_contains($href, '/author/') ||
                    str_contains($href, '/page/') ||
                    str_contains($href, '/wp-') ||
                    str_contains($href, '/feed')) {
                    continue;
                }
                
                // Skip common page slugs
                $skipSlugs = ['contact', 'booking', 'privacy', 'about', 'testimonials', 'books',
                             'keynote-talks', 'sales-courses', 'strengths-programme', 'facilitation',
                             'search', 'sitemap', 'robots', 'home', 'index'];
                if (in_array(strtolower($slug), $skipSlugs) || strlen($slug) < 5) {
                    continue;
                }

                $listingUrl = str_starts_with($href, 'http') ? $href : $this->baseUrl.$href;
                $key = 'toolbox_'.$slug;
                
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                // Extract title from surrounding HTML
                $title = $this->extractTitleFromHtml($html, $href, $slug);
                
                if (empty($title) || strlen($title) < 10) {
                    continue;
                }
                
                // Skip common page titles
                $pageTitlePatterns = [
                    'contact', 'booking', 'privacy', 'about', 'testimonials', 'books',
                    'keynote', 'sales courses', 'sales-courses', 'strengths programme', 'strengths-programme',
                    'facilitation', 'search', 'sitemap', 'robots', 'home', 'index',
                ];
                $titleLower = strtolower(trim($title));
                foreach ($pageTitlePatterns as $pattern) {
                    if ($titleLower === $pattern || 
                        str_starts_with($titleLower, $pattern.' ') ||
                        str_ends_with($titleLower, ' '.$pattern) ||
                        str_contains($titleLower, ' '.$pattern.' ')) {
                        continue 2;
                    }
                }

                $inventory[$key] = [
                    'source_site' => 'toolbox',
                    'listing_url' => $listingUrl,
                    'slug' => $slug,
                    'title' => $title,
                    'excerpt' => '',
                    'published_at' => '',
                    'category' => '',
                    'featured_image_url' => '',
                ];
                $count++;
            }
        }

        // Pattern 3: Date-based URLs /2024/11/slug (year/month/slug) - The Strengths Toolbox format
        // Exclude URLs that match Pattern 2 (year/month/day/slug) - they have 4 date segments
        // Note: slug can have trailing slash, so we match up to the quote or end of href
        if (preg_match_all('#href=["\']([^"\']*?/(\d{4})/(\d{2})/([^/?"\']+)/?)["\']#i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $href = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5);
                
                // Skip if this matches Pattern 2 format (year/month/day/slug) - has 4 date segments
                if (preg_match('#/(\d{4})/(\d{2})/(\d{2})/#', $href)) {
                    continue; // Already handled by Pattern 2
                }
                
                $slug = $match[4];
                
                // Skip non-post URLs
                if (str_contains($href, '/category/') || 
                    str_contains($href, '/tag/') || 
                    str_contains($href, '/author/') ||
                    str_contains($href, '/page/') ||
                    str_contains($href, '/wp-') ||
                    str_contains($href, '/feed')) {
                    continue;
                }
                
                // Skip common page slugs
                $skipSlugs = ['contact', 'booking', 'privacy', 'about', 'testimonials', 'books',
                             'keynote-talks', 'sales-courses', 'strengths-programme', 'facilitation',
                             'search', 'sitemap', 'robots', 'home', 'index'];
                if (in_array(strtolower($slug), $skipSlugs) || strlen($slug) < 5) {
                    continue;
                }

                $listingUrl = str_starts_with($href, 'http') ? $href : $this->baseUrl.$href;
                $key = 'toolbox_'.$slug;
                
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                // Extract title from surrounding HTML
                $title = $this->extractTitleFromHtml($html, $href, $slug);
                
                if (empty($title) || strlen($title) < 10) {
                    continue;
                }
                
                // Skip common page titles
                $pageTitlePatterns = [
                    'contact', 'booking', 'privacy', 'about', 'testimonials', 'books',
                    'keynote', 'sales courses', 'sales-courses', 'strengths programme', 'strengths-programme',
                    'facilitation', 'search', 'sitemap', 'robots', 'home', 'index',
                ];
                $titleLower = strtolower(trim($title));
                foreach ($pageTitlePatterns as $pattern) {
                    if ($titleLower === $pattern || 
                        str_starts_with($titleLower, $pattern.' ') ||
                        str_ends_with($titleLower, ' '.$pattern) ||
                        str_contains($titleLower, ' '.$pattern.' ')) {
                        continue 2;
                    }
                }

                $inventory[$key] = [
                    'source_site' => 'toolbox',
                    'listing_url' => $listingUrl,
                    'slug' => $slug,
                    'title' => $title,
                    'excerpt' => '',
                    'published_at' => '',
                    'category' => '',
                    'featured_image_url' => '',
                ];
                $count++;
            }
        }

        return $count;
    }

    /**
     * Extract title from HTML by finding the link and its surrounding context
     */
    protected function extractTitleFromHtml(string $html, string $href, string $slug): string
    {
        // Try to find the link in HTML and extract title from nearby elements
        $escapedHref = preg_quote($href, '#');
        
        // Pattern 1: Look for title in <a> tag text
        if (preg_match("#<a[^>]*href=[\"']{$escapedHref}[\"'][^>]*>(.*?)</a>#is", $html, $m)) {
            $title = strip_tags($m[1]);
            $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5);
            $title = trim($title);
            if (strlen($title) >= 10) {
                return $title;
            }
        }
        
        // Pattern 2: Look for title in nearby heading (h1, h2, h3)
        if (preg_match("#<a[^>]*href=[\"']{$escapedHref}[\"'][^>]*>.*?</a>.*?<h[1-3][^>]*>(.*?)</h[1-3]>#is", $html, $m)) {
            $title = strip_tags($m[1]);
            $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5);
            $title = trim($title);
            if (strlen($title) >= 10) {
                return $title;
            }
        }
        
        // Pattern 3: Look for title before the link (reverse search)
        if (preg_match("#<h[1-3][^>]*>(.*?)</h[1-3]>.*?<a[^>]*href=[\"']{$escapedHref}[\"']#is", $html, $m)) {
            $title = strip_tags($m[1]);
            $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5);
            $title = trim($title);
            if (strlen($title) >= 10) {
                return $title;
            }
        }
        
        // Pattern 4: Use slug as fallback (convert to readable title)
        return ucwords(str_replace(['-', '_'], ' ', $slug));
    }

    /**
     * Extract article body - The Strengths Toolbox specific
     */
    protected function extractArticleBody(string $html): string
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // The Strengths Toolbox specific selectors
        $selectors = [
            '//*[contains(@class,"entry-content")]',
            '//*[contains(@class,"post-content")]',
            '//*[contains(@class,"article-content")]',
            '//*[contains(@class,"content-area")]//*[contains(@class,"entry-content")]',
            '//article',
            '//main//*[contains(@class,"content")]',
        ];

        $bestContent = '';
        $bestLength = 0;

        foreach ($selectors as $selector) {
            $candidates = $xpath->query($selector);
            foreach ($candidates as $candidate) {
                if (! $candidate instanceof \DOMElement) {
                    continue;
                }

                $inner = '';
                foreach ($candidate->childNodes as $child) {
                    $inner .= $candidate->ownerDocument->saveHTML($child);
                }
                $inner = trim($inner);

                // Remove script and style tags (PHP preg_replace doesn't use 'g' flag)
                $inner = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $inner);
                $inner = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/i', '', $inner);

                $textContent = strip_tags($inner);
                $textLength = strlen(trim($textContent));

                if ($textLength > $bestLength && $textLength >= 200) {
                    $bestContent = $inner;
                    $bestLength = $textLength;
                }
            }
        }

        if (! empty($bestContent)) {
            return '<div class="prose prose-lg max-w-none">'.$bestContent.'</div>';
        }

        return '';
    }

    protected function findPostContainer(?\DOMElement $link, DOMXPath $xpath): ?\DOMElement
    {
        $node = $link;
        for ($i = 0; $i < 15 && $node; $i++) {
            if ($node instanceof \DOMElement) {
                $class = $node->getAttribute('class');
                $tag = strtolower($node->nodeName ?? '');
                if ($tag === 'article' || str_contains($class, 'post') || str_contains($class, 'entry')) {
                    return $node;
                }
            }
            $node = $node->parentNode;
        }
        return $link->parentNode instanceof \DOMElement ? $link->parentNode : null;
    }

    protected function extractTitle(?\DOMElement $container, DOMXPath $xpath, \DOMElement $link): string
    {
        if (! $container) {
            return trim($link->textContent ?? '');
        }
        $h1 = $xpath->query('.//h1', $container)->item(0);
        if ($h1) {
            $text = trim($h1->textContent ?? '');
            if ($text !== '') {
                return $text;
            }
        }
        $h2 = $xpath->query('.//h2//a | .//h2', $container)->item(0);
        if ($h2) {
            $text = trim($h2->textContent ?? '');
            if ($text !== '') {
                return $text;
            }
        }
        return trim($link->getAttribute('title') ?: $link->textContent ?? '');
    }

    protected function extractExcerpt(?\DOMElement $container, DOMXPath $xpath): string
    {
        if (! $container) {
            return '';
        }
        $p = $xpath->query('.//*[contains(@class,"excerpt")]//p | .//*[contains(@class,"excerpt")] | .//p', $container)->item(0);
        if ($p) {
            return trim(preg_replace('/\s+/', ' ', $p->textContent ?? ''));
        }
        return '';
    }

    protected function extractDateAndCategory(?\DOMElement $container, DOMXPath $xpath): array
    {
        $date = '';
        $category = '';
        if (! $container) {
            return ['date' => $date, 'category' => $category];
        }
        $meta = $xpath->query('.//*[contains(@class,"meta")] | .//*[contains(@class,"date")] | .//time', $container);
        foreach ($meta as $el) {
            $text = trim($el->textContent ?? '');
            if (preg_match('/^([A-Za-z]+\s+\d{1,2},?\s+\d{4})/', $text, $m)) {
                $date = $m[1];
            }
            if (str_contains($text, '|')) {
                $parts = array_map('trim', explode('|', $text, 2));
                if (count($parts) >= 2) {
                    $date = $parts[0];
                    $category = preg_replace('/\s*\[.*$/', '', $parts[1]);
                }
            }
        }
        return ['date' => $date, 'category' => $category];
    }

    protected function extractFeaturedImageUrl(?\DOMElement $container, DOMXPath $xpath): string
    {
        if (! $container) {
            return '';
        }
        $img = $xpath->query('.//img[contains(@src,"wp-content") or contains(@src,"uploads")]', $container)->item(0);
        if (! $img) {
            return '';
        }
        $src = $img->getAttribute('src');
        if (! $src || str_contains($src, '-150x150') || str_contains($src, '-100x100')) {
            return '';
        }
        if (! str_starts_with($src, 'http')) {
            $src = str_starts_with($src, '//') ? 'https:'.$src : $this->baseUrl.$src;
        }
        return $src;
    }

    protected function extractAuthor(string $html): string
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $author = $xpath->query('//*[contains(@class,"author")] | //*[contains(@class,"byline")] | //*[@rel="author"]');
        foreach ($author as $el) {
            $text = trim($el->textContent ?? '');
            if ($text !== '') {
                return $text;
            }
        }
        return 'Eberhard Niklaus';
    }

    protected function extractCategories(string $html): array
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $categories = [];
        $links = $xpath->query('//a[contains(@rel,"category")] | //*[contains(@class,"category")]//a');
        foreach ($links as $link) {
            $text = trim($link->textContent ?? '');
            if ($text !== '') {
                $categories[] = $text;
            }
        }
        return array_unique($categories);
    }

    protected function extractTags(string $html): array
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $tags = [];
        $links = $xpath->query('//a[contains(@rel,"tag")] | //*[contains(@class,"tag")]//a');
        foreach ($links as $link) {
            $text = trim($link->textContent ?? '');
            if ($text !== '') {
                $tags[] = $text;
            }
        }
        return array_unique($tags);
    }

    protected function extractAllImages(string $html): array
    {
        $images = [];
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $imgs = $xpath->query('//img[@src]');
        foreach ($imgs as $img) {
            $src = $img->getAttribute('src');
            if ($src && (str_contains($src, 'wp-content') || str_contains($src, 'uploads'))) {
                if (str_contains($src, '-150x150') || str_contains($src, '-100x100')) {
                    continue;
                }
                if (! str_starts_with($src, 'http')) {
                    $src = str_starts_with($src, '//') ? 'https:'.$src : $this->baseUrl.$src;
                }
                $images[] = $src;
            }
            $srcset = $img->getAttribute('srcset');
            if ($srcset) {
                preg_match_all('/([^\s,]+)/', $srcset, $matches);
                foreach ($matches[1] as $url) {
                    if (str_contains($url, 'wp-content') || str_contains($url, 'uploads')) {
                        if (str_contains($url, '-150x150') || str_contains($url, '-100x100')) {
                            continue;
                        }
                        if (! str_starts_with($url, 'http')) {
                            $url = str_starts_with($url, '//') ? 'https:'.$url : $this->baseUrl.$url;
                        }
                        $images[] = $url;
                    }
                }
            }
        }
        return array_unique($images);
    }
}
