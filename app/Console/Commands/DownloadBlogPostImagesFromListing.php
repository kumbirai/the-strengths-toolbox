<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DownloadBlogPostImagesFromListing extends Command
{
    protected $signature = 'blog:download-images-from-listing 
                            {--url=https://www.thestrengthstoolbox.com : Base URL}
                            {--output=content-migration/images/original/strengthstoolbox/blog : Output directory}';

    protected $description = 'Download featured images from blog listing page (more reliable)';

    protected array $imageHashes = [];

    protected array $postImageMap = [];

    public function handle(): int
    {
        $baseUrl = $this->option('url');
        $outputDir = base_path($this->option('output'));

        $this->info('Downloading blog post featured images from listing pages...');
        $this->info("Base URL: {$baseUrl}");
        $this->info("Output directory: {$outputDir}");
        $this->newLine();

        // Create output directory
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Get all blog posts from database
        $blogPosts = BlogPost::all()->keyBy('slug');

        if ($blogPosts->isEmpty()) {
            $this->warn('No blog posts found in database. Run BlogPostMigrationSeeder first.');

            return Command::FAILURE;
        }

        $this->info("Found {$blogPosts->count()} blog posts in database");
        $this->newLine();

        // Extract images from all blog listing pages
        $this->info('Extracting images from blog listing pages...');
        for ($page = 1; $page <= 4; $page++) {
            $this->line("Processing page {$page}...");
            $this->extractImagesFromListingPage($baseUrl, $page, $blogPosts);
        }

        $this->newLine();
        $this->info('Download Summary:');
        $this->line('  Unique images found: '.count($this->imageHashes));
        $this->line('  Posts mapped: '.count($this->postImageMap));

        // Download unique images
        $this->newLine();
        $this->info('Downloading unique images...');
        $downloaded = 0;
        $skipped = 0;

        foreach ($this->postImageMap as $slug => $imageData) {
            $post = $blogPosts->get($slug);
            if (! $post) {
                continue;
            }

            $imageUrl = $imageData['url'];
            $hash = $imageData['hash'];

            // Check if we already have this image
            if (isset($this->imageHashes[$hash]['file'])) {
                $this->line("  ⊘ Already have: {$post->title} (using existing image)");
                $skipped++;

                continue;
            }

            // Download the image
            $result = $this->downloadImage($imageUrl, $outputDir, $slug, $hash);

            if ($result['success']) {
                $this->line("  ✓ Downloaded: {$result['filename']} → {$post->title}");
                $downloaded++;
                $this->imageHashes[$hash]['file'] = $result['path'];
            } else {
                $this->warn("  ⊘ {$result['reason']}: {$post->title}");
                $skipped++;
            }
        }

        $this->newLine();
        $this->info('Final Summary:');
        $this->line("  Downloaded: {$downloaded}");
        $this->line("  Skipped: {$skipped}");
        $this->line('  Unique images: '.count($this->imageHashes));

        return Command::SUCCESS;
    }

    protected function extractImagesFromListingPage(string $baseUrl, int $page, $blogPosts): void
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

            // Find all blog post entries
            $entries = $xpath->query('//article | //div[contains(@class, "post")] | //div[contains(@class, "blog-post")]');

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

                // Find the featured image in this entry
                $img = $xpath->query('.//img[contains(@class, "wp-post-image") or contains(@class, "post-thumbnail")]', $entry)->item(0);
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

                // Create a hash of the URL to track unique images
                $urlHash = md5($absoluteUrl);

                if (! isset($this->postImageMap[$slug])) {
                    $this->postImageMap[$slug] = [
                        'url' => $absoluteUrl,
                        'hash' => $urlHash,
                    ];
                }

                // Track unique images
                if (! isset($this->imageHashes[$urlHash])) {
                    $this->imageHashes[$urlHash] = [
                        'url' => $absoluteUrl,
                        'posts' => [],
                    ];
                }

                if (! in_array($slug, $this->imageHashes[$urlHash]['posts'])) {
                    $this->imageHashes[$urlHash]['posts'][] = $slug;
                }
            }
        } catch (\Exception $e) {
            $this->warn("Error processing page {$page}: ".$e->getMessage());
        }
    }

    protected function downloadImage(string $url, string $outputDir, string $slug, string $hash): array
    {
        try {
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                return ['success' => false, 'reason' => "HTTP {$response->status()}"];
            }

            $content = $response->body();
            $contentType = $response->header('Content-Type', '');

            // Verify it's an image
            if (! str_starts_with($contentType, 'image/')) {
                return ['success' => false, 'reason' => 'Not an image'];
            }

            // Check content hash
            $contentHash = md5($content);

            // If we already have this image content, reuse it
            foreach ($this->imageHashes as $existingHash => $data) {
                if (isset($data['file']) && file_exists($data['file'])) {
                    $existingContentHash = md5_file($data['file']);
                    if ($existingContentHash === $contentHash) {
                        return ['success' => false, 'reason' => 'Duplicate content', 'filename' => basename($data['file']), 'path' => $data['file']];
                    }
                }
            }

            // Generate filename
            $extension = $this->getExtensionFromUrl($url, $contentType);
            $mappingFilename = $this->getMappingFilename($slug);
            $filename = "{$mappingFilename}.{$extension}";
            $filePath = $outputDir.'/'.$filename;

            // Skip if already exists
            if (file_exists($filePath)) {
                return ['success' => false, 'reason' => 'File exists', 'filename' => $filename, 'path' => $filePath];
            }

            // Save file
            file_put_contents($filePath, $content);

            return ['success' => true, 'filename' => $filename, 'path' => $filePath];
        } catch (\Exception $e) {
            return ['success' => false, 'reason' => $e->getMessage()];
        }
    }

    protected function getExtensionFromUrl(string $url, string $contentType): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                return $ext;
            }
        }

        if (preg_match('/image\/(jpeg|jpg|png|gif|webp)/i', $contentType, $matches)) {
            return strtolower($matches[1] === 'jpeg' ? 'jpg' : $matches[1]);
        }

        return 'jpg';
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

    protected function getMappingFilename(string $slug): string
    {
        $mapping = [
            'how-your-natural-talents-are-the-key-to-unlocking-your-potential' => 'blog-natural-talents-unlock-potential',
            'why-goals-are-essential-for-salespeople' => 'blog-goals-essential-salespeople',
            'the-benefits-of-strengths-based-selling' => 'blog-strengths-based-selling',
            'the-idea-that-anyone-can-sell-is-nonsense' => 'blog-anyone-can-sell-nonsense',
        ];

        if (isset($mapping[$slug])) {
            return $mapping[$slug];
        }

        return 'blog-'.Str::slug($slug);
    }
}
