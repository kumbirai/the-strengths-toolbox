<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DownloadBlogPostImages extends Command
{
    protected $signature = 'blog:download-images 
                            {--url=https://www.thestrengthstoolbox.com : Base URL}
                            {--output=content-migration/images/original/strengthstoolbox/blog : Output directory}';

    protected $description = 'Download featured images from blog posts on the website';

    protected array $downloadedUrls = [];

    protected array $imageHashes = []; // Track MD5 hashes to detect duplicates

    public function handle(): int
    {
        $baseUrl = $this->option('url');
        $outputDir = base_path($this->option('output'));

        $this->info('Downloading blog post featured images...');
        $this->info("Base URL: {$baseUrl}");
        $this->info("Output directory: {$outputDir}");
        $this->newLine();

        // Create output directory
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Get all blog posts from database
        $blogPosts = BlogPost::all();

        if ($blogPosts->isEmpty()) {
            $this->warn('No blog posts found in database. Run BlogPostMigrationSeeder first.');

            return Command::FAILURE;
        }

        $this->info("Found {$blogPosts->count()} blog posts in database");
        $this->newLine();

        $downloaded = 0;
        $skipped = 0;
        $duplicates = 0;
        $errors = 0;

        foreach ($blogPosts as $post) {
            $this->line("Processing: {$post->title}");

            // Construct blog post URL - try different date patterns
            $postUrl = $this->getPostUrl($baseUrl, $post->slug);

            try {
                $imageUrl = $this->extractFeaturedImage($postUrl, $baseUrl);

                if (! $imageUrl) {
                    $this->warn('  ⊘ No featured image found');
                    $skipped++;

                    continue;
                }

                // Check if we've already downloaded this exact image (by URL)
                if (in_array($imageUrl, $this->downloadedUrls)) {
                    $this->warn('  ⊘ Duplicate URL, skipping: '.basename(parse_url($imageUrl, PHP_URL_PATH)));
                    $duplicates++;

                    continue;
                }

                // Download the image
                $result = $this->downloadImage($imageUrl, $outputDir, $post->slug);

                if ($result['success']) {
                    // Check for duplicate by MD5 hash
                    $filePath = $result['path'];
                    $hash = md5_file($filePath);

                    if (isset($this->imageHashes[$hash])) {
                        $this->warn("  ⊘ Duplicate image (same as: {$this->imageHashes[$hash]}), removing duplicate");
                        unlink($filePath);
                        $duplicates++;
                        // Use the existing file instead
                        $result['filename'] = basename($this->imageHashes[$hash]);
                        $result['path'] = $this->imageHashes[$hash];
                    } else {
                        $this->imageHashes[$hash] = $filePath;
                        $this->downloadedUrls[] = $imageUrl;
                        $this->line("  ✓ Downloaded: {$result['filename']}");
                        $downloaded++;
                    }
                } else {
                    $this->warn("  ⊘ {$result['reason']}");
                    $skipped++;
                }
            } catch (\Exception $e) {
                $this->error('  ✗ Error: '.$e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->info('Download Summary:');
        $this->line("  Downloaded: {$downloaded}");
        $this->line("  Duplicates skipped: {$duplicates}");
        $this->line("  Skipped: {$skipped}");
        if ($errors > 0) {
            $this->error("  Errors: {$errors}");
        }

        $this->newLine();
        $this->info('Next steps:');
        $this->line('1. Optimize images: Move/convert images to content-migration/images/optimized/');
        $this->line('2. Upload to media library: php artisan db:seed --class=MediaSeeder');
        $this->line('3. Assign to blog posts: php artisan blog:assign-featured-images');

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    protected function extractFeaturedImage(string $url, string $baseUrl): ?string
    {
        try {
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                return null;
            }

            $html = $response->body();

            // Parse HTML
            libxml_use_internal_errors(true);
            $dom = new DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);

            // Try to find featured image in common WordPress locations
            // 1. Post thumbnail / featured image - look for the largest version
            $featuredImages = $xpath->query('//img[contains(@class, "wp-post-image") or contains(@class, "post-thumbnail") or contains(@class, "featured-image")]');
            if ($featuredImages->length > 0) {
                $bestImage = null;
                $bestSize = 0;

                foreach ($featuredImages as $img) {
                    $src = $img->getAttribute('src');
                    if (! $src) {
                        continue;
                    }

                    // Skip thumbnails (look for larger images)
                    if (str_contains($src, '-150x150') || str_contains($src, '-100x100')) {
                        continue;
                    }

                    // Prefer full-size or larger images
                    $width = (int) $img->getAttribute('width') ?: 0;
                    $height = (int) $img->getAttribute('height') ?: 0;
                    $size = $width * $height;

                    if ($size > $bestSize) {
                        $bestSize = $size;
                        $bestImage = $src;
                    }
                }

                if ($bestImage) {
                    // Try to get full-size version (remove size suffix)
                    $fullSizeUrl = preg_replace('/-\d+x\d+\.(jpg|jpeg|png)$/i', '.$1', $bestImage);

                    return $this->makeAbsoluteUrl($fullSizeUrl, $baseUrl, $url);
                }
            }

            // 2. First large image in post content
            $contentImages = $xpath->query('//article//img[@src] | //div[contains(@class, "entry-content")]//img[@src] | //div[contains(@class, "post-content")]//img[@src]');
            foreach ($contentImages as $img) {
                $src = $img->getAttribute('src');
                $width = $img->getAttribute('width');
                $height = $img->getAttribute('height');

                // Prefer larger images (likely featured images)
                if ($width && (int) $width >= 300 && $height && (int) $height >= 200) {
                    return $this->makeAbsoluteUrl($src, $baseUrl, $url);
                }
            }

            // 3. First image in content as fallback
            if ($contentImages->length > 0) {
                $src = $contentImages->item(0)->getAttribute('src');
                if ($src) {
                    return $this->makeAbsoluteUrl($src, $baseUrl, $url);
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function makeAbsoluteUrl(string $url, string $baseUrl, string $currentPage): string
    {
        // Already absolute
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        // Protocol-relative URL
        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        // Absolute path
        if (str_starts_with($url, '/')) {
            $parsed = parse_url($baseUrl);

            return ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '').$url;
        }

        // Relative path
        $parsed = parse_url($currentPage);
        $base = ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '');
        $path = dirname($parsed['path'] ?? '/');
        if ($path === '.') {
            $path = '/';
        }
        $path = rtrim($path, '/').'/'.ltrim($url, '/');

        return $base.$path;
    }

    protected function downloadImage(string $url, string $outputDir, string $slug): array
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

            // Generate filename based on slug - convert to mapping format
            $extension = $this->getExtensionFromUrl($url, $contentType);

            // Convert slug to mapping filename format
            $mappingFilename = $this->getMappingFilename($slug);
            $filename = "{$mappingFilename}.{$extension}";
            $filePath = $outputDir.'/'.$filename;

            // Check for duplicate by content hash before saving
            $contentHash = md5($content);
            if (isset($this->imageHashes[$contentHash])) {
                return ['success' => false, 'reason' => 'Duplicate content', 'filename' => basename($this->imageHashes[$contentHash]), 'path' => $this->imageHashes[$contentHash]];
            }

            // Skip if already exists (but check if it's the same content)
            if (file_exists($filePath)) {
                $existingHash = md5_file($filePath);
                if ($existingHash === $contentHash) {
                    $this->imageHashes[$contentHash] = $filePath;

                    return ['success' => false, 'reason' => 'File exists', 'filename' => $filename, 'path' => $filePath];
                }
            }

            // Save file
            file_put_contents($filePath, $content);
            $this->imageHashes[$contentHash] = $filePath;

            return ['success' => true, 'filename' => $filename, 'path' => $filePath];
        } catch (\Exception $e) {
            return ['success' => false, 'reason' => $e->getMessage()];
        }
    }

    protected function getExtensionFromUrl(string $url, string $contentType): string
    {
        // Try to get extension from URL
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                return $ext;
            }
        }

        // Try to get from content type
        if (preg_match('/image\/(jpeg|jpg|png|gif|webp)/i', $contentType, $matches)) {
            return strtolower($matches[1] === 'jpeg' ? 'jpg' : $matches[1]);
        }

        // Default
        return 'jpg';
    }

    protected function getPostUrl(string $baseUrl, string $slug): string
    {
        // Try different URL patterns based on slug
        // Most posts are from 2023/09 or 2023/11
        $patterns = [
            "/2023/11/{$slug}",
            "/2023/09/{$slug}",
            "/{$slug}",
        ];

        foreach ($patterns as $pattern) {
            $url = rtrim($baseUrl, '/').$pattern;
            try {
                $response = Http::timeout(10)->head($url);
                if ($response->successful()) {
                    return $url;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Default to most common pattern
        return rtrim($baseUrl, '/')."/2023/09/{$slug}";
    }

    protected function getMappingFilename(string $slug): string
    {
        // Map blog post slugs to their image mapping filenames
        $mapping = [
            'how-your-natural-talents-are-the-key-to-unlocking-your-potential' => 'blog-natural-talents-unlock-potential',
            'why-goals-are-essential-for-salespeople' => 'blog-goals-essential-salespeople',
            'the-benefits-of-strengths-based-selling' => 'blog-strengths-based-selling',
            'the-idea-that-anyone-can-sell-is-nonsense' => 'blog-anyone-can-sell-nonsense',
            // Add more mappings as needed, or generate from slug
        ];

        if (isset($mapping[$slug])) {
            return $mapping[$slug];
        }

        // Fallback: generate from slug
        return 'blog-'.Str::slug($slug);
    }
}
