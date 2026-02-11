<?php

namespace App\Console\Commands;

use App\Console\Commands\Scrapers\TsaBlogScraper;
use App\Console\Commands\Scrapers\ToolboxBlogScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Scrape blog posts from both WordPress sites (TSA Business School and The Strengths Toolbox)
 * Handles pagination, extracts full content, and merges duplicates
 */
class ScrapeWordPressBlogs extends Command
{
    protected $signature = 'blog:scrape-wordpress
                            {--tsa-url=https://www.tsabusinessschool.co.za : TSA Business School base URL}
                            {--toolbox-url=https://www.thestrengthstoolbox.com : The Strengths Toolbox base URL}
                            {--output=content-migration/scraped-blogs.json : Output JSON path}
                            {--delay=1 : Seconds to wait between HTTP requests}
                            {--dry-run : Only crawl, do not write file}';

    protected $description = 'Scrape all blog posts from both WordPress sites with pagination support';

    private const LISTING_PAGE_MAX = 50;

    public function handle(): int
    {
        $tsaUrl = rtrim($this->option('tsa-url'), '/');
        $toolboxUrl = rtrim($this->option('toolbox-url'), '/');
        $outputPath = $this->option('output');
        $fullPath = str_starts_with($outputPath, '/') ? $outputPath : base_path($outputPath);
        $delay = max(0, (int) $this->option('delay'));
        $dryRun = $this->option('dry-run');

        $this->info('Scraping WordPress blogs from both sites...');
        $this->line("TSA URL: {$tsaUrl}");
        $this->line("Toolbox URL: {$toolboxUrl}");
        $this->line("Output: {$outputPath}");
        if ($dryRun) {
            $this->warn('DRY RUN – no file will be written.');
        }
        $this->newLine();

        // Scrape both sites using site-specific scrapers
        $this->info('Scraping TSA Business School...');
        $tsaScraper = new TsaBlogScraper($tsaUrl, $delay);
        $tsaResult = $tsaScraper->scrape(function ($message) use ($tsaScraper) {
            $this->line("  {$message}");
        });
        $tsaPosts = $tsaResult['inventory'];
        $tsaStats = $tsaResult['stats'];
        $this->line("  Found ".count($tsaPosts).' posts');
        $this->displayContentStats($tsaStats);
        $this->newLine();

        $this->info('Scraping The Strengths Toolbox...');
        $toolboxScraper = new ToolboxBlogScraper($toolboxUrl, $delay);
        $toolboxResult = $toolboxScraper->scrape(function ($message) {
            $this->line("  {$message}");
        });
        $toolboxPosts = $toolboxResult['inventory'];
        $toolboxStats = $toolboxResult['stats'];
        $this->line("  Found ".count($toolboxPosts).' posts');
        $this->displayContentStats($toolboxStats);
        $this->newLine();

        // Merge duplicates
        $this->info('Merging duplicate posts...');
        $this->line("  TSA posts before merge: ".count($tsaPosts));
        $this->line("  Toolbox posts before merge: ".count($toolboxPosts));
        $mergedPosts = $this->mergeDuplicates($tsaPosts, $toolboxPosts);
        $this->line("  Total unique posts after merge: ".count($mergedPosts));
        $this->newLine();

        // Write output
        if (! $dryRun) {
            $dir = dirname($fullPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents(
                $fullPath,
                json_encode(array_values($mergedPosts), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
            $this->info("✓ Wrote: {$fullPath}");
        } else {
            $this->info("Would write: {$fullPath}");
        }

        $this->newLine();
        $this->info('✓ Scraping complete!');

        return Command::SUCCESS;
    }

    /**
     * Scrape a single WordPress site
     *
     * @return array<string, array>
     */
    protected function scrapeSite(string $baseUrl, string $siteName, int $delay): array
    {
        $inventory = [];
        $page = 1;

        while ($page <= self::LISTING_PAGE_MAX) {
            $url = $this->getListingUrl($baseUrl, $page);

            $this->line("  Fetching listing page {$page}: {$url}");

            $response = Http::timeout(30)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; WordPress-Blog-Scraper/1.0)'])
                ->get($url);

            if (! $response->successful()) {
                $this->warn("  HTTP {$response->status()}, stopping.");
                break;
            }

            $html = $response->body();
            $found = $this->extractPostsFromListing($baseUrl, $html, $inventory, $siteName);

            if ($found === 0) {
                $this->line("  No posts on page {$page}, stopping.");
                break;
            }

            $page++;
            if ($delay > 0) {
                sleep($delay);
            }
        }

        // Fetch full content for each post
        $this->info("  Fetching full content for ".count($inventory).' posts...');
        $bar = $this->output->createProgressBar(count($inventory));
        $bar->start();

        $contentStats = [
            'full' => 0,      // >= 500 chars
            'short' => 0,     // 200-499 chars
            'missing' => 0,   // < 200 chars or empty
            'errors' => 0,
        ];

        foreach ($inventory as $key => $post) {
            $url = $post['listing_url'] ?? '';
            if ($url) {
                try {
                    if ($delay > 0) {
                        sleep($delay);
                    }
                    $response = Http::timeout(30)->get($url);
                    if ($response->successful()) {
                        $html = $response->body();
                        $content = $this->extractArticleBody($html);
                        
                        // Validate content was extracted
                        $textLength = strlen(strip_tags($content));
                        if (empty($content) || $textLength < 200) {
                            $contentStats['missing']++;
                            $this->newLine();
                            $this->warn("  ⚠ Content too short or missing for: {$post['title']}");
                            $this->warn("     Text length: {$textLength} chars (minimum: 200)");
                        } elseif ($textLength < 500) {
                            $contentStats['short']++;
                            // Warn if content seems short (might be excerpt)
                            $this->newLine();
                            $this->warn("  ⚠ Content seems short for: {$post['title']} ({$textLength} chars)");
                        } else {
                            $contentStats['full']++;
                        }
                        
                        $inventory[$key]['content_html'] = $content;
                        $inventory[$key]['content_length'] = $textLength; // Store for validation
                        $inventory[$key]['author'] = $this->extractAuthor($html);
                        $inventory[$key]['categories'] = $this->extractCategories($html);
                        $inventory[$key]['tags'] = $this->extractTags($html);
                        $inventory[$key]['all_images'] = $this->extractAllImages($html, $baseUrl);
                    } else {
                        $contentStats['errors']++;
                        $this->newLine();
                        $this->warn("  ⚠ HTTP {$response->status()} for: {$post['title']} ({$url})");
                    }
                } catch (\Throwable $e) {
                    $contentStats['errors']++;
                    $this->newLine();
                    $this->warn("  Error fetching {$key}: ".$e->getMessage());
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Report content extraction statistics
        $this->info('Content Extraction Summary:');
        $this->line("  ✓ Full content (>=500 chars): {$contentStats['full']}");
        if ($contentStats['short'] > 0) {
            $this->warn("  ⚠ Short content (200-499 chars): {$contentStats['short']} (may be excerpts)");
        }
        if ($contentStats['missing'] > 0) {
            $this->error("  ✗ Missing/too short (<200 chars): {$contentStats['missing']}");
        }
        if ($contentStats['errors'] > 0) {
            $this->error("  ✗ Errors: {$contentStats['errors']}");
        }
        $this->newLine();

        return $inventory;
    }

    /**
     * Get listing URL for a page
     */
    protected function getListingUrl(string $baseUrl, int $page): string
    {
        if ($page === 1) {
            return $baseUrl.'/blog/';
        }

        // Try different pagination patterns
        $patterns = [
            $baseUrl.'/blog/page/'.$page.'/',
            $baseUrl.'/blog/page/'.$page.'/?et_blog',
            $baseUrl.'/blog/?paged='.$page,
        ];

        // Try first pattern, fallback handled in extraction
        return $patterns[0];
    }

    /**
     * Extract posts from listing HTML
     *
     * @param  array<string, array>  $inventory
     * @return int Number of posts extracted
     */
    protected function extractPostsFromListing(string $baseUrl, string $html, array &$inventory, string $siteName): int
    {
        $before = count($inventory);
        $count = $this->extractPostsRegex($baseUrl, $html, $inventory, $siteName);
        if ($count > 0) {
            return $count;
        }

        // DOM-based extraction
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        
        // Try multiple XPath patterns for different WordPress structures
        $postLinks = $xpath->query('//a[contains(@href, "/blog/") or contains(@href, "/2024/") or contains(@href, "/2025/") or contains(@href, "/2023/")]');
        
        // If no posts found, try article links
        if ($postLinks->length === 0) {
            $postLinks = $xpath->query('//article//a[@href] | //*[contains(@class, "post")]//a[@href] | //*[contains(@class, "entry")]//a[@href]');
        }

        $seen = [];
        foreach ($postLinks as $link) {
            if (! $link instanceof \DOMElement) {
                continue;
            }
            $href = $link->getAttribute('href');
            
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
            
            // Extract slug from various URL patterns
            $slug = null;
            
            // Pattern 1: /2024/11/27/slug
            if (preg_match('#/(\d{4})/(\d{2})/(\d{2})/([^/?]+)/?#', $href, $m)) {
                $slug = $m[4];
            }
            // Pattern 2: /2024/11/slug
            elseif (preg_match('#/(\d{4})/(\d{2})/([^/?]+)/?#', $href, $m)) {
                $slug = $m[3];
            }
            // Pattern 3: /blog/slug
            elseif (preg_match('#/blog/([^/?]+)/?#', $href, $m)) {
                $slug = $m[1];
            }
            // Pattern 4: Extract from full URL (last resort)
            elseif (preg_match('#https?://[^/]+/([^/?]+)/?#', $href, $m)) {
                $slug = $m[1];
                // Skip common non-post slugs and short slugs (likely not posts)
                $skipSlugs = ['contact', 'about', 'home', 'index', 'privacy', 'terms', 'blog', 
                              'booking', 'testimonials', 'books', 'keynote-talks', 'sales-courses',
                              'strengths-programme', 'facilitation', 'search', 'sitemap', 'robots'];
                if (in_array($slug, $skipSlugs) || strlen($slug) < 5) {
                    continue;
                }
            }
            
            if (! $slug) {
                continue;
            }
            $key = $siteName.'_'.$slug;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $listingUrl = str_starts_with($href, 'http') ? $href : $baseUrl.$href;
            $container = $this->findPostContainer($link, $xpath);
            $title = $this->extractTitle($container, $xpath, $link);
            $excerpt = $this->extractExcerpt($container, $xpath);
            $dateCategory = $this->extractDateAndCategory($container, $xpath);
            $featuredImageUrl = $this->extractFeaturedImageUrl($container, $xpath, $baseUrl);

            if ($title === '' && $container) {
                $title = trim($link->textContent ?? '');
            }

            // Validate this is actually a blog post (has title and reasonable slug)
            if (empty($title) || strlen($title) < 10 || strlen($slug) < 5) {
                continue;
            }

            // Additional validation: slug should look like a blog post slug
            // (not a page slug like 'contact', 'about', etc.)
            $pageSlugs = ['contact', 'about', 'home', 'index', 'privacy', 'terms', 'blog',
                          'booking', 'testimonials', 'books', 'keynote-talks', 'sales-courses',
                          'strengths-programme', 'facilitation', 'search', 'sitemap', 'robots'];
            if (in_array(strtolower($slug), $pageSlugs)) {
                continue;
            }

            $inventory[$key] = [
                'source_site' => $siteName,
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
     * Regex-based extraction fallback
     *
     * @param  array<string, array>  $inventory
     * @return int
     */
    protected function extractPostsRegex(string $baseUrl, string $html, array &$inventory, string $siteName): int
    {
        $count = 0;
        $seen = [];
        
        // Try multiple URL patterns for different WordPress structures
        $baseDomain = parse_url($baseUrl, PHP_URL_HOST);
        $baseDomainEscaped = preg_quote($baseDomain, '#');
        
        $patterns = [
            // Date-based: /2024/11/27/slug
            '#href=["\'](https?://[^"\']*?/\d{4}/\d{2}/\d{2}/[^/"\']+)/?["\']#iu',
            // Date-based without day: /2024/11/slug
            '#href=["\'](https?://[^"\']*?/\d{4}/\d{2}/[^/"\']+)/?["\']#iu',
            // Blog slug pattern: /blog/slug (but not /blog/ itself or /blog/page/)
            '#href=["\'](https?://[^"\']*?/blog/[^/"\']+)/?["\']#iu',
            // Any URL from the base domain that looks like a post (not common pages)
            '#href=["\'](https?://[^"\']*?'.$baseDomainEscaped.'/[^/"\']+)/?["\']#iu',
        ];
        
        $allMatches = [];
        foreach ($patterns as $patternIndex => $pattern) {
            if (preg_match_all($pattern, $html, $urlMatches)) {
                $allMatches = array_merge($allMatches, $urlMatches[1]);
            }
        }
        
        // Also try to find relative URLs that might be blog posts
        if (empty($allMatches)) {
            // Look for any href attributes that might be blog posts
            if (preg_match_all('#href=["\']([^"\']+)["\']#iu', $html, $relMatches)) {
                foreach ($relMatches[1] as $relUrl) {
                    // Skip if it's clearly not a post
                    if (str_starts_with($relUrl, '#') || 
                        str_starts_with($relUrl, 'mailto:') ||
                        str_starts_with($relUrl, 'tel:') ||
                        str_starts_with($relUrl, 'javascript:')) {
                        continue;
                    }
                    
                    // Convert relative to absolute if needed
                    $absoluteUrl = $relUrl;
                    if (! str_starts_with($relUrl, 'http')) {
                        if (str_starts_with($relUrl, '/')) {
                            $absoluteUrl = $baseUrl.$relUrl;
                        } else {
                            $absoluteUrl = $baseUrl.'/'.$relUrl;
                        }
                    }
                    
                    // Only include if it's from our domain
                    if (str_contains($absoluteUrl, $baseDomain)) {
                        $allMatches[] = $absoluteUrl;
                    }
                }
            }
        }
        
        if (empty($allMatches)) {
            return 0;
        }
        
        // Remove duplicates
        $allMatches = array_unique($allMatches);

        foreach ($allMatches as $listingUrl) {
            $listingUrl = trim(preg_replace('/\?.*/', '', rtrim($listingUrl, '/')));
            
            // Skip non-post URLs
            $skipPatterns = [
                '/category/', '/tag/', '/author/', '/page/', '/wp-', '/feed',
                '/contact', '/about', '/privacy', '/terms', '/search',
                '/wp-admin', '/wp-content', '/wp-includes', '/wp-json',
            ];
            
            $shouldSkip = false;
            foreach ($skipPatterns as $pattern) {
                if (str_contains($listingUrl, $pattern)) {
                    $shouldSkip = true;
                    break;
                }
            }
            
            if ($shouldSkip || 
                str_ends_with($listingUrl, '/blog') || 
                str_ends_with($listingUrl, '/blog/') ||
                str_ends_with($listingUrl, $baseUrl) ||
                str_ends_with($listingUrl, $baseUrl.'/')) {
                continue;
            }
            
            // Extract slug from various URL patterns
            // Only accept URLs that look like blog posts (have date pattern or /blog/ path)
            $slug = null;
            $isBlogPost = false;
            
            // Pattern 1: /2024/11/27/slug (date-based - definitely a post)
            if (preg_match('#/(\d{4})/(\d{2})/(\d{2})/([^/]+)/?$#', $listingUrl, $slugM)) {
                $slug = $slugM[4];
                $isBlogPost = true;
            }
            // Pattern 2: /2024/11/slug (date-based without day - likely a post)
            elseif (preg_match('#/(\d{4})/(\d{2})/([^/]+)/?$#', $listingUrl, $slugM)) {
                $slug = $slugM[3];
                $isBlogPost = true;
            }
            // Pattern 3: /blog/slug (blog path - likely a post)
            elseif (preg_match('#/blog/([^/]+)/?$#', $listingUrl, $slugM)) {
                $slug = $slugM[1];
                $isBlogPost = true;
                // But skip if it's a pagination or category page
                if (in_array(strtolower($slug), ['page', 'category', 'tag', 'author', 'archive'])) {
                    continue;
                }
            }
            
            // Only accept if we found a valid blog post pattern
            if (! $slug || ! $isBlogPost) {
                continue;
            }
            
            // Additional validation: skip common non-post slugs
            $skipSlugs = ['contact', 'about', 'home', 'index', 'privacy', 'terms', 'blog',
                          'booking', 'testimonials', 'books', 'keynote-talks', 'sales-courses',
                          'strengths-programme', 'facilitation', 'search', 'sitemap', 'robots',
                          'page', 'category', 'tag', 'author', 'archive', 'feed'];
            if (in_array(strtolower($slug), $skipSlugs) || strlen($slug) < 5) {
                continue;
            }

            $key = $siteName.'_'.$slug;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $pos = strpos($html, $listingUrl);
            if ($pos === false) {
                $pos = strpos($html, $listingUrl.'/');
            }
            $block = $pos !== false ? substr($html, $pos, 2500) : '';
            $imgUrl = '';
            if (preg_match('#<img[^>]+src=["\']([^"\']*(?:wp-content|uploads)[^"\']+)["\']#iu', $block, $imgM)) {
                $imgUrl = $imgM[1];
                if (str_contains($imgUrl, '-150x150') || str_contains($imgUrl, '-100x100')) {
                    $imgUrl = '';
                }
                if ($imgUrl !== '' && ! str_starts_with($imgUrl, 'http')) {
                    $imgUrl = str_starts_with($imgUrl, '//') ? 'https:'.$imgUrl : $baseUrl.$imgUrl;
                }
            }

            $title = '';
            $quoted = preg_quote($listingUrl, '#');
            if (preg_match('#<h2[^>]*>[\s\S]*?<a[^>]+href=["\']'.$quoted.'[^"\']*["\'][^>]*>([^<]+)</a>#iu', $block, $t)) {
                $title = trim(html_entity_decode(strip_tags($t[1])));
            }

            $date = '';
            $category = '';
            if (preg_match('#(\w{3,9}\s+\d{1,2},?\s+\d{4})\s*\|?\s*([^<\n\[\]]+)#', $block, $t)) {
                $date = trim($t[1]);
                $category = trim(preg_replace('/\s*\[.*$/', '', $t[2]));
            }

            // Validate this is actually a blog post
            if (empty($title) || strlen($title) < 10 || strlen($slug) < 5) {
                continue;
            }

            // Additional validation: slug should look like a blog post slug
            $pageSlugs = ['contact', 'about', 'home', 'index', 'privacy', 'terms', 'blog',
                          'booking', 'testimonials', 'books', 'keynote-talks', 'sales-courses',
                          'strengths-programme', 'facilitation', 'search', 'sitemap', 'robots'];
            if (in_array(strtolower($slug), $pageSlugs)) {
                continue;
            }

            $inventory[$key] = [
                'source_site' => $siteName,
                'listing_url' => $listingUrl,
                'slug' => $slug,
                'title' => $title,
                'excerpt' => '',
                'published_at' => $date,
                'category' => $category,
                'featured_image_url' => $imgUrl,
            ];
            $count++;
        }

        return $count;
    }

    private function findPostContainer(\DOMElement $link, DOMXPath $xpath): ?\DOMElement
    {
        $node = $link;
        for ($i = 0; $i < 15 && $node; $i++) {
            if ($node instanceof \DOMElement) {
                $class = $node->getAttribute('class');
                $tag = strtolower($node->nodeName ?? '');
                if ($tag === 'article' || str_contains($class, 'post') || str_contains($class, 'entry') || str_contains($class, 'et_pb')) {
                    return $node;
                }
            }
            $node = $node->parentNode;
        }

        return $link->parentNode instanceof \DOMElement ? $link->parentNode : null;
    }

    private function extractTitle(?\DOMElement $container, DOMXPath $xpath, \DOMElement $link): string
    {
        if (! $container) {
            return trim($link->textContent ?? '');
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

    private function extractExcerpt(?\DOMElement $container, DOMXPath $xpath): string
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

    private function extractDateAndCategory(?\DOMElement $container, DOMXPath $xpath): array
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
        if ($date === '' && $container) {
            $text = $container->textContent ?? '';
            if (preg_match('/([A-Za-z]+\s+\d{1,2},?\s+\d{4})\s*\|?\s*([^\n\[]+)/', $text, $m)) {
                $date = trim($m[1]);
                $category = trim($m[2]);
            }
        }

        return ['date' => $date, 'category' => $category];
    }

    private function extractFeaturedImageUrl(?\DOMElement $container, DOMXPath $xpath, string $baseUrl): string
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
            $src = str_starts_with($src, '//') ? 'https:'.$src : $baseUrl.$src;
        }

        return $src;
    }

    /**
     * Extract article body content
     * Validates that full content is extracted, not just excerpts
     */
    private function extractArticleBody(string $html): string
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Try multiple selectors in order of preference (most specific first)
        $selectors = [
            // WordPress common content classes
            '//*[contains(@class,"entry-content")]',
            '//*[contains(@class,"post-content")]',
            '//*[contains(@class,"article-content")]',
            '//*[contains(@class,"content-area")]//*[contains(@class,"entry-content")]',
            // Divi/ET themes
            '//*[contains(@class,"et_pb_post_content")]',
            // Generic content containers
            '//*[contains(@class,"post-body")]',
            '//*[contains(@class,"article-body")]',
            '//*[contains(@class,"main-content")]',
            // Main content area
            '//main//*[contains(@class,"content")]',
            '//main//article',
        ];

        $bestContent = '';
        $bestLength = 0;

        foreach ($selectors as $selector) {
            $candidates = $xpath->query($selector);

            foreach ($candidates as $candidate) {
                if (! $candidate instanceof \DOMElement) {
                    continue;
                }

                // Get all child nodes (full content)
                $inner = '';
                foreach ($candidate->childNodes as $child) {
                    $inner .= $candidate->ownerDocument->saveHTML($child);
                }
                $inner = trim($inner);

                // Remove script and style tags (PHP preg_replace doesn't use 'g' flag)
                $inner = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $inner);
                $inner = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/i', '', $inner);

                // Get text content length (excluding HTML tags)
                $textContent = strip_tags($inner);
                $textLength = strlen(trim($textContent));

                // Prefer longer content (likely full article, not excerpt)
                // Minimum 200 characters to be considered valid content
                if ($textLength > $bestLength && $textLength >= 200) {
                    $bestContent = $inner;
                    $bestLength = $textLength;
                }
            }
        }

        // Fallback to <article> tag if no good content found
        if (empty($bestContent) || $bestLength < 200) {
            $articles = $xpath->query('//article');
            foreach ($articles as $article) {
                if (! $article instanceof \DOMElement) {
                    continue;
                }

                $inner = '';
                foreach ($article->childNodes as $child) {
                    $inner .= $article->ownerDocument->saveHTML($child);
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

        // Final fallback: try to find main content area
        if (empty($bestContent) || $bestLength < 200) {
            $main = $xpath->query('//main | //*[@role="main"] | //*[@id="main"] | //*[@id="content"]');
            foreach ($main as $mainEl) {
                if (! $mainEl instanceof \DOMElement) {
                    continue;
                }

                // Exclude header, footer, sidebar, navigation
                $excludeSelectors = [
                    './/header',
                    './/footer',
                    './/nav',
                    './/aside',
                    './/*[contains(@class,"sidebar")]',
                    './/*[contains(@class,"navigation")]',
                    './/*[contains(@class,"widget")]',
                ];

                $inner = '';
                foreach ($mainEl->childNodes as $child) {
                    if ($child instanceof \DOMElement) {
                        $shouldExclude = false;
                        foreach ($excludeSelectors as $excludeSelector) {
                            if ($xpath->query($excludeSelector, $child)->length > 0) {
                                $shouldExclude = true;
                                break;
                            }
                        }
                        if (! $shouldExclude) {
                            $inner .= $mainEl->ownerDocument->saveHTML($child);
                        }
                    } else {
                        $inner .= $mainEl->ownerDocument->saveHTML($child);
                    }
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

        // Validate content length - warn if too short (might be excerpt)
        if (! empty($bestContent)) {
            $textContent = strip_tags($bestContent);
            $textLength = strlen(trim($textContent));

            // If content is less than 500 characters, it might be just an excerpt
            // But we'll still return it as it might be a valid short post
            if ($textLength < 500) {
                // Check if it contains "read more" or similar - likely excerpt
                if (preg_match('/\b(read\s+more|continue\s+reading|read\s+full|see\s+more)\b/i', $bestContent)) {
                    // This is likely an excerpt, try to find full content
                    return '';
                }
            }

            return '<div class="prose prose-lg max-w-none">'.$bestContent.'</div>';
        }

        return '';
    }

    /**
     * Extract author name
     */
    private function extractAuthor(string $html): string
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

        return 'Eberhard Niklaus'; // Default
    }

    /**
     * Extract categories
     */
    private function extractCategories(string $html): array
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

    /**
     * Extract tags
     */
    private function extractTags(string $html): array
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

    /**
     * Extract all images from HTML
     */
    private function extractAllImages(string $html, string $baseUrl): array
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
                    $src = str_starts_with($src, '//') ? 'https:'.$src : $baseUrl.$src;
                }
                $images[] = $src;
            }

            // Check srcset
            $srcset = $img->getAttribute('srcset');
            if ($srcset) {
                preg_match_all('/([^\s,]+)/', $srcset, $matches);
                foreach ($matches[1] as $url) {
                    if (str_contains($url, 'wp-content') || str_contains($url, 'uploads')) {
                        if (str_contains($url, '-150x150') || str_contains($url, '-100x100')) {
                            continue;
                        }
                        if (! str_starts_with($url, 'http')) {
                            $url = str_starts_with($url, '//') ? 'https:'.$url : $baseUrl.$url;
                        }
                        $images[] = $url;
                    }
                }
            }
        }

        return array_unique($images);
    }

    /**
     * Merge duplicate posts from both sites
     *
     * @param  array<string, array>  $tsaPosts
     * @param  array<string, array>  $toolboxPosts
     * @return array<string, array>
     */
    protected function mergeDuplicates(array $tsaPosts, array $toolboxPosts): array
    {
        $merged = [];
        $seenUrls = [];
        $seenTitles = [];

        // First pass: deduplicate within each site by URL
        $deduplicatedTsa = $this->deduplicateByUrl($tsaPosts);
        $deduplicatedToolbox = $this->deduplicateByUrl($toolboxPosts);

        // Process all posts
        foreach (array_merge($deduplicatedTsa, $deduplicatedToolbox) as $key => $post) {
            $slug = $post['slug'] ?? '';
            $title = trim($post['title'] ?? '');
            $url = $post['listing_url'] ?? '';

            // Skip if no valid slug or title
            if (empty($slug) || empty($title) || strlen($title) < 10) {
                continue;
            }

            // Normalize slug and title for comparison
            $normalizedSlug = Str::slug($slug);
            $normalizedTitle = Str::slug($title);

            // Check for duplicates by URL first
            if (! empty($url) && isset($seenUrls[$url])) {
                // Same URL - merge with existing
                $existingKey = $seenUrls[$url];
                $merged[$existingKey] = $this->mergePostData($merged[$existingKey], $post);
                continue;
            }

            // Check for duplicates by slug
            $mergeKey = $normalizedSlug;
            if (isset($merged[$mergeKey])) {
                // Same slug - merge with existing
                $merged[$mergeKey] = $this->mergePostData($merged[$mergeKey], $post);
                if (! empty($url)) {
                    $seenUrls[$url] = $mergeKey;
                }
                continue;
            }

            // Check for duplicates by title similarity (fuzzy match)
            $foundDuplicate = false;
            foreach ($merged as $existingKey => $existingPost) {
                $existingTitle = Str::slug(trim($existingPost['title'] ?? ''));
                // If titles are very similar (80% match), consider them duplicates
                similar_text($normalizedTitle, $existingTitle, $percent);
                if ($percent > 80 && strlen($normalizedTitle) > 20) {
                    $merged[$existingKey] = $this->mergePostData($existingPost, $post);
                    if (! empty($url)) {
                        $seenUrls[$url] = $existingKey;
                    }
                    $foundDuplicate = true;
                    break;
                }
            }

            if ($foundDuplicate) {
                continue;
            }

            // New unique post
            $merged[$mergeKey] = $post;
            if (! empty($url)) {
                $seenUrls[$url] = $mergeKey;
            }
            $seenTitles[$normalizedTitle] = $mergeKey;
        }

        return $merged;
    }

    /**
     * Deduplicate posts within a single site by URL
     *
     * @param  array<string, array>  $posts
     * @return array<string, array>
     */
    protected function deduplicateByUrl(array $posts): array
    {
        $deduplicated = [];
        $seenUrls = [];

        foreach ($posts as $key => $post) {
            $url = $post['listing_url'] ?? '';
            $slug = $post['slug'] ?? '';

            // Skip if no URL or slug
            if (empty($url) || empty($slug)) {
                continue;
            }

            // Normalize URL (remove query params, trailing slashes)
            $normalizedUrl = rtrim(preg_replace('/\?.*/', '', $url), '/');

            // If we've seen this URL before, skip it
            if (isset($seenUrls[$normalizedUrl])) {
                continue;
            }

            $seenUrls[$normalizedUrl] = true;
            $deduplicated[$key] = $post;
        }

        return $deduplicated;
    }

    /**
     * Merge two post data arrays
     */
    protected function mergePostData(array $existing, array $new): array
    {
        // Prefer non-empty values
        $merged = $existing;

        if (empty($merged['content_html']) && ! empty($new['content_html'])) {
            $merged['content_html'] = $new['content_html'];
        }

        if (empty($merged['excerpt']) && ! empty($new['excerpt'])) {
            $merged['excerpt'] = $new['excerpt'];
        }

        if (empty($merged['featured_image_url']) && ! empty($new['featured_image_url'])) {
            $merged['featured_image_url'] = $new['featured_image_url'];
        }

        // Merge categories and tags
        $merged['categories'] = array_unique(array_merge(
            $merged['categories'] ?? [],
            $new['categories'] ?? []
        ));

        $merged['tags'] = array_unique(array_merge(
            $merged['tags'] ?? [],
            $new['tags'] ?? []
        ));

        // Merge images
        $merged['all_images'] = array_unique(array_merge(
            $merged['all_images'] ?? [],
            $new['all_images'] ?? []
        ));

        // Track source sites
        if (! isset($merged['source_sites'])) {
            $merged['source_sites'] = [];
        }
        $merged['source_sites'][] = $new['source_site'] ?? 'unknown';
        $merged['source_sites'] = array_unique($merged['source_sites']);

        return $merged;
    }

    /**
     * Display content extraction statistics
     */
    protected function displayContentStats(array $stats): void
    {
        $this->line("  Content: Full ({$stats['full']}), Short ({$stats['short']}), Missing ({$stats['missing']}), Errors ({$stats['errors']})");
    }
}
