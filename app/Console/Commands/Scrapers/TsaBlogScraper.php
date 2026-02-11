<?php

namespace App\Console\Commands\Scrapers;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;

/**
 * Site-specific scraper for TSA Business School blog
 */
class TsaBlogScraper
{
    protected string $baseUrl;
    protected int $delay;

    public function __construct(string $baseUrl, int $delay = 1)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->delay = $delay;
    }

    /**
     * Scrape all blog posts from TSA site
     */
    public function scrape(callable $progressCallback = null): array
    {
        $inventory = [];
        $page = 1;
        $maxPages = 50;

        while ($page <= $maxPages) {
            $url = $page === 1
                ? $this->baseUrl.'/blog/'
                : $this->baseUrl.'/blog/page/'.$page.'/?et_blog';

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
     * Extract posts from listing HTML
     */
    protected function extractPostsFromListing(string $html, array &$inventory): int
    {
        $count = $this->extractPostsRegex($html, $inventory);
        if ($count > 0) {
            return $count;
        }

        // DOM-based extraction
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $postLinks = $xpath->query('//a[contains(@href, "/2024/") or contains(@href, "/2025/") or contains(@href, "/2023/")]');

        $seen = [];
        foreach ($postLinks as $link) {
            if (! $link instanceof \DOMElement) {
                continue;
            }
            $href = $link->getAttribute('href');
            if (! preg_match('#/(\d{4})/(\d{2})/(\d{2})/([^/?]+)/?#', $href, $m)) {
                continue;
            }
            $slug = $m[4];
            if (isset($seen[$slug])) {
                continue;
            }
            $seen[$slug] = true;

            $listingUrl = str_starts_with($href, 'http') ? $href : $this->baseUrl.$href;
            $container = $this->findPostContainer($link, $xpath);
            $title = $this->extractTitle($container, $xpath, $link);
            $excerpt = $this->extractExcerpt($container, $xpath);
            $dateCategory = $this->extractDateAndCategory($container, $xpath);
            $featuredImageUrl = $this->extractFeaturedImageUrl($container, $xpath);

            if ($title === '' && $container) {
                $title = trim($link->textContent ?? '');
            }

            // Validate
            if (empty($title) || strlen($title) < 10 || strlen($slug) < 5) {
                continue;
            }

            $inventory['tsa_'.$slug] = [
                'source_site' => 'tsa',
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
     * Regex-based extraction for TSA
     */
    protected function extractPostsRegex(string $html, array &$inventory): int
    {
        $count = 0;
        $seen = [];
        $pattern = '#href=["\'](https?://[^"\']*?/\d{4}/\d{2}/\d{2}/[^/"\']+)/?["\']#iu';
        
        if (! preg_match_all($pattern, $html, $urlMatches)) {
            return 0;
        }

        foreach ($urlMatches[1] as $listingUrl) {
            $listingUrl = trim(preg_replace('/\?.*/', '', rtrim($listingUrl, '/')));
            if (! preg_match('#/(\d{4})/(\d{2})/(\d{2})/([^/]+)/?$#', $listingUrl, $slugM)) {
                continue;
            }
            $slug = $slugM[4];
            $key = 'tsa_'.$slug;
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
                    $imgUrl = str_starts_with($imgUrl, '//') ? 'https:'.$imgUrl : $this->baseUrl.$imgUrl;
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

            // Validate
            if (empty($title) || strlen($title) < 10 || strlen($slug) < 5) {
                continue;
            }

            $inventory[$key] = [
                'source_site' => 'tsa',
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

    /**
     * Extract article body - TSA specific
     */
    protected function extractArticleBody(string $html): string
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // TSA-specific selectors (Divi theme)
        $selectors = [
            '//*[contains(@class,"et_pb_post_content")]',
            '//*[contains(@class,"entry-content")]',
            '//*[contains(@class,"post-content")]',
            '//article',
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
                if ($tag === 'article' || str_contains($class, 'post') || str_contains($class, 'entry') || str_contains($class, 'et_pb')) {
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
