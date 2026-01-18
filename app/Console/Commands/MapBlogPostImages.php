<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MapBlogPostImages extends Command
{
    protected $signature = 'blog:map-images 
                            {--url=https://www.thestrengthstoolbox.com : Base URL}
                            {--output=content-migration/images/original/strengthstoolbox/blog : Output directory}';

    protected $description = 'Map blog posts to their actual featured images from the website';

    protected array $postImageMap = [];

    public function handle(): int
    {
        $baseUrl = $this->option('url');
        $outputDir = base_path($this->option('output'));

        $this->info('Mapping blog posts to their featured images...');
        $this->newLine();

        // Extract image mappings from blog listing pages
        for ($page = 1; $page <= 4; $page++) {
            $this->line("Processing blog listing page {$page}...");
            $this->extractMappingsFromPage($baseUrl, $page);
        }

        $this->newLine();
        $this->info('Found '.count($this->postImageMap).' post-image mappings');
        $this->newLine();

        // Display mapping
        $this->table(
            ['Post Slug', 'Image URL'],
            array_map(function ($slug, $data) {
                return [$slug, basename(parse_url($data['url'], PHP_URL_PATH))];
            }, array_keys($this->postImageMap), $this->postImageMap)
        );

        // Group by image URL to see which posts share images
        $this->newLine();
        $this->info('Posts sharing images:');
        $imageGroups = [];
        foreach ($this->postImageMap as $slug => $data) {
            $imageUrl = $data['url'];
            if (! isset($imageGroups[$imageUrl])) {
                $imageGroups[$imageUrl] = [];
            }
            $imageGroups[$imageUrl][] = $slug;
        }

        foreach ($imageGroups as $imageUrl => $slugs) {
            if (count($slugs) > 1) {
                $this->line('  '.basename(parse_url($imageUrl, PHP_URL_PATH)).' ('.count($slugs).' posts):');
                foreach ($slugs as $slug) {
                    $post = BlogPost::where('slug', $slug)->first();
                    $this->line('    - '.($post ? $post->title : $slug));
                }
            }
        }

        // Save mapping to file
        $mappingFile = base_path('content-migration/images/blog-post-image-mapping.json');
        file_put_contents($mappingFile, json_encode($this->postImageMap, JSON_PRETTY_PRINT));
        $this->newLine();
        $this->info("Mapping saved to: {$mappingFile}");

        return Command::SUCCESS;
    }

    protected function extractMappingsFromPage(string $baseUrl, int $page): void
    {
        try {
            $url = rtrim($baseUrl, '/')."/blog/page/{$page}/";
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                return;
            }

            $html = $response->body();
            libxml_use_internal_errors(true);
            $dom = new DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);

            // Find all blog post entries with their images
            $entries = $xpath->query('//article | //div[contains(@class, "post")]');

            foreach ($entries as $entry) {
                // Find the post link
                $link = $xpath->query('.//a[contains(@href, "/2023/")]', $entry)->item(0);
                if (! $link) {
                    continue;
                }

                $href = $link->getAttribute('href');
                if (! preg_match('/\/(\d{4})\/(\d{2})\/([^\/]+)\/$/', $href, $matches)) {
                    continue;
                }

                $slug = $matches[3];

                // Find the featured image
                $img = $xpath->query('.//img[contains(@class, "wp-post-image")]', $entry)->item(0);
                if (! $img) {
                    continue;
                }

                $src = $img->getAttribute('src');
                if (! $src) {
                    continue;
                }

                // Skip thumbnails
                if (str_contains($src, '-150x150') || str_contains($src, '-100x100')) {
                    continue;
                }

                // Get full-size version
                $fullSizeUrl = preg_replace('/-\d+x\d+\.(jpg|jpeg|png)$/i', '.$1', $src);
                $absoluteUrl = $this->makeAbsoluteUrl($fullSizeUrl, rtrim($baseUrl, '/'), $url);

                if (! isset($this->postImageMap[$slug])) {
                    $this->postImageMap[$slug] = ['url' => $absoluteUrl];
                }
            }
        } catch (\Exception $e) {
            $this->warn("Error processing page {$page}: ".$e->getMessage());
        }
    }

    protected function makeAbsoluteUrl(string $url, string $baseUrl, string $currentPage): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        if (str_starts_with($url, '/')) {
            $parsed = parse_url($baseUrl);

            return ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '').$url;
        }

        $parsed = parse_url($currentPage);
        $base = ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '');
        $path = dirname($parsed['path'] ?? '/');
        if ($path === '.') {
            $path = '/';
        }
        $path = rtrim($path, '/').'/'.ltrim($url, '/');

        return $base.$path;
    }
}
