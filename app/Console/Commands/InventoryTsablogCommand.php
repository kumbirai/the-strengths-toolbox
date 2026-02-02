<?php

namespace App\Console\Commands;

use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Crawl TSA blog listing pages, build inventory (slug, title, excerpt, date, category, featured_image_url).
 * Optionally fetch full content per post and/or download images (see --fetch-content and --download-images).
 */
class InventoryTsablogCommand extends Command
{
    protected $signature = 'blog:inventory-tsa
                            {--base=https://www.tsabusinessschool.co.za : TSA blog base URL}
                            {--output=content-migration/tsa-blog-inventory.json : Output JSON path}
                            {--fetch-content : Fetch full article body for each post and add to inventory}
                            {--download-images : Download featured images and assign to blog_posts (requires DB)}
                            {--delay=1 : Seconds to wait between HTTP requests}
                            {--dry-run : Only crawl listing pages, do not write file or fetch content}';

    protected $description = 'Inventory TSA blog from paginated listing pages; optionally fetch full content and download images';

    private const LISTING_PAGE_MAX = 20;

    public function handle(): int
    {
        $baseUrl = rtrim($this->option('base'), '/');
        $outputPath = $this->option('output');
        $fullPath = str_starts_with($outputPath, '/') ? $outputPath : base_path($outputPath);
        $fetchContent = $this->option('fetch-content');
        $downloadImages = $this->option('download-images');
        $delay = max(0, (int) $this->option('delay'));
        $dryRun = $this->option('dry-run');

        $this->info('Inventorying TSA blog...');
        $this->line("Base URL: {$baseUrl}");
        $this->line('Output: '.$outputPath);
        if ($dryRun) {
            $this->warn('DRY RUN – no file will be written.');
        }
        $this->newLine();

        $inventory = [];
        $loadedFromFile = false;
        if ($downloadImages && ! $fetchContent && file_exists($fullPath)) {
            $existing = json_decode(file_get_contents($fullPath), true);
            if (is_array($existing)) {
                foreach ($existing as $item) {
                    $slug = $item['slug'] ?? '';
                    if ($slug !== '') {
                        $inventory[$slug] = $item;
                    }
                }
                $loadedFromFile = count($inventory) > 0;
                if ($loadedFromFile) {
                    $this->info('Loaded existing inventory: '.count($inventory).' posts');
                    $this->newLine();
                }
            }
        }

        if (! $loadedFromFile) {
            $inventory = $this->crawlListingPages($baseUrl, $delay);

            if (empty($inventory)) {
                $this->warn('No posts found. Check base URL and page structure.');

                return Command::FAILURE;
            }

            $this->info('Listing inventory: '.count($inventory).' posts');
            $this->newLine();

            if ($fetchContent) {
                $this->info('Fetching full content for each post...');
                $inventory = $this->fetchFullContent($baseUrl, $inventory, $delay);
            }
        }

        if (! $dryRun && ! $loadedFromFile) {
            $dir = dirname($fullPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents(
                $fullPath,
                json_encode(array_values($inventory), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
            $this->info('Wrote: '.$fullPath);
        }

        if ($downloadImages && ! $dryRun) {
            $this->newLine();
            $this->info('Downloading featured images and assigning to blog posts...');
            $this->downloadAndAssignImages($inventory, $delay);
        }

        return Command::SUCCESS;
    }

    /**
     * Crawl blog listing pages until no more posts or no "Older Entries".
     *
     * @return array<string, array{listing_url: string, slug: string, title: string, excerpt: string, published_at: string, category: string, featured_image_url: string}>
     */
    protected function crawlListingPages(string $baseUrl, int $delay): array
    {
        $inventory = [];
        $page = 1;

        while ($page <= self::LISTING_PAGE_MAX) {
            $url = $page === 1
                ? $baseUrl.'/blog/'
                : $baseUrl.'/blog/page/'.$page.'/?et_blog';

            $this->line("  Fetching listing page {$page}: {$url}");

            $response = Http::timeout(30)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; TSA-Blog-Inventory/1.0)'])
                ->get($url);

            if (! $response->successful()) {
                $this->warn("  HTTP {$response->status()}, stopping.");
                break;
            }

            $html = $response->body();
            $found = $this->extractPostsFromListingHtml($baseUrl, $html, $inventory);

            if ($found === 0) {
                $this->line("  No posts on page {$page}, stopping.");
                break;
            }

            $page++;
            if ($delay > 0) {
                sleep($delay);
            }
        }

        return $inventory;
    }

    /**
     * Parse listing HTML and merge extracted posts into inventory (keyed by slug).
     *
     * @param  array<string, array<string, string>>  $inventory
     * @return int Number of posts extracted from this page
     */
    protected function extractPostsFromListingHtml(string $baseUrl, string $html, array &$inventory): int
    {
        $before = count($inventory);
        $count = $this->extractPostsFromListingHtmlRegex($baseUrl, $html, $inventory);
        if ($count > 0) {
            return $count;
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $postLinks = $xpath->query(
            '//a[contains(@href, "/2024/") or contains(@href, "/2025/")]'
        );

        $count = 0;
        $seen = [];

        foreach ($postLinks as $link) {
            if (! $link instanceof \DOMElement) {
                continue;
            }
            $href = $link->getAttribute('href');
            if (! preg_match('#/(\d{4})/(\d{2})/([^/?]+)/?(?:\?|$)#', $href, $m)) {
                continue;
            }
            $slug = $m[3];
            if (isset($seen[$slug])) {
                continue;
            }
            $seen[$slug] = true;

            $listingUrl = str_starts_with($href, 'http') ? $href : $baseUrl.$href;

            $container = $this->findPostContainer($link, $xpath);
            $title = $this->extractTitle($container, $xpath, $link);
            $excerpt = $this->extractExcerpt($container, $xpath);
            $dateCategory = $this->extractDateAndCategory($container, $xpath);
            $featuredImageUrl = $this->extractFeaturedImageUrl($container, $xpath, $baseUrl);

            if ($title === '' && $container) {
                $title = trim($link->textContent ?? '');
            }

            $inventory[$slug] = [
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
     * Regex fallback when DOM extraction finds no posts (e.g. different HTML structure).
     * Two-step: find post URLs, then for each find nearest img in same block.
     *
     * @param  array<string, array<string, string>>  $inventory
     * @return int
     */
    protected function extractPostsFromListingHtmlRegex(string $baseUrl, string $html, array &$inventory): int
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
                if (preg_match('#/(\d{4})/(\d{2})/(\d{2})/([^/]+)#', $listingUrl, $slugM)) {
                    $slug = $slugM[4];
                } else {
                    continue;
                }
            } else {
                $slug = $slugM[4];
            }
            if (isset($seen[$slug])) {
                continue;
            }
            $seen[$slug] = true;
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
            $inventory[$slug] = [
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
     * Fetch full article body for each inventory item and add content_html.
     *
     * @param  array<string, array<string, string>>  $inventory
     * @return array<string, array<string, string>>
     */
    protected function fetchFullContent(string $baseUrl, array $inventory, int $delay): array
    {
        $bar = $this->output->createProgressBar(count($inventory));
        $bar->start();

        foreach ($inventory as $slug => $item) {
            $url = $item['listing_url'] ?? '';
            if ($url === '') {
                $bar->advance();
                continue;
            }

            try {
                $response = Http::timeout(30)->get($url);
                if (! $response->successful()) {
                    $inventory[$slug]['content_html'] = '';
                    $bar->advance();
                    if ($delay > 0) {
                        sleep($delay);
                    }
                    continue;
                }

                $html = $response->body();
                $content = $this->extractArticleBody($html);
                $inventory[$slug]['content_html'] = $content;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("  {$slug}: ".$e->getMessage());
                $inventory[$slug]['content_html'] = '';
            }

            $bar->advance();
            if ($delay > 0) {
                sleep($delay);
            }
        }

        $bar->finish();
        $this->newLine(2);

        return $inventory;
    }

    private function extractArticleBody(string $html): string
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $candidates = $xpath->query(
            '//*[contains(@class,"entry-content") or contains(@class,"post-content") or contains(@class,"article-content") or contains(@class,"et_pb_post_content")]'
        );

        if ($candidates->length > 0) {
            $body = $candidates->item(0);
            if ($body instanceof \DOMElement) {
                $inner = '';
                foreach ($body->childNodes as $child) {
                    $inner .= $body->ownerDocument->saveHTML($child);
                }
                $inner = trim($inner);
                if ($inner !== '') {
                    return '<div class="prose prose-lg max-w-none">'.$inner.'</div>';
                }
            }
        }

        $article = $xpath->query('//article')->item(0);
        if ($article instanceof \DOMElement) {
            $inner = '';
            foreach ($article->childNodes as $child) {
                $inner .= $article->ownerDocument->saveHTML($child);
            }
            $inner = trim($inner);
            if ($inner !== '') {
                return '<div class="prose prose-lg max-w-none">'.$inner.'</div>';
            }
        }

        return '';
    }

    /**
     * Download featured images to storage and set BlogPost.featured_image by slug.
     *
     * @param  array<string, array<string, string>>  $inventory
     */
    protected function downloadAndAssignImages(array $inventory, int $delay): void
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $storageDir = 'blog';
        if (! $disk->exists($storageDir)) {
            $disk->makeDirectory($storageDir);
        }

        $assigned = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($inventory as $slug => $item) {
            $imageUrl = $item['featured_image_url'] ?? '';
            if ($imageUrl === '') {
                $skipped++;
                continue;
            }

            $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $ext = strtolower($ext) === 'jpeg' ? 'jpg' : strtolower($ext);
            $filename = 'blog-'.preg_replace('/[^a-z0-9-]/', '-', strtolower($slug)).'.'.$ext;
            $relativePath = $storageDir.'/'.$filename;

            try {
                if ($delay > 0) {
                    sleep($delay);
                }
                $response = \Illuminate\Support\Facades\Http::timeout(30)->get($imageUrl);
                if (! $response->successful()) {
                    $this->warn("  HTTP {$response->status()} for {$slug}");
                    $errors++;
                    continue;
                }
                $disk->put($relativePath, $response->body());
            } catch (\Throwable $e) {
                $this->warn("  {$slug}: ".$e->getMessage());
                $errors++;
                continue;
            }

            $post = \App\Models\BlogPost::where('slug', $slug)->first();
            if (! $post && ! empty($item['title'] ?? '')) {
                $post = \App\Models\BlogPost::where('title', $item['title'])->first();
            }
            if (! $post) {
                $skipped++;
                continue;
            }
            $post->featured_image = $relativePath;
            $post->save();
            $this->line("  ✓ {$filename} → {$post->title}");
            $assigned++;
        }

        $this->newLine();
        $this->info("Summary: Assigned {$assigned}, Skipped {$skipped}, Errors {$errors}");
    }
}
