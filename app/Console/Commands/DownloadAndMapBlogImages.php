<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DownloadAndMapBlogImages extends Command
{
    protected $signature = 'blog:download-and-map-images 
                            {--url=https://www.thestrengthstoolbox.com : Base URL}
                            {--output=content-migration/images/original/strengthstoolbox/blog : Output directory}';

    protected $description = 'Download unique images and map them to all blog posts';

    protected array $uniqueImages = []; // URL => local file path

    protected array $postImageMap = []; // slug => image URL

    public function handle(): int
    {
        $baseUrl = $this->option('url');
        $outputDir = base_path($this->option('output'));

        $this->info('Downloading and mapping blog post images...');
        $this->newLine();

        // Create output directory
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Step 1: Extract mappings from blog listing pages
        $this->info('Step 1: Extracting image mappings from blog listing pages...');
        for ($page = 1; $page <= 4; $page++) {
            $this->extractMappingsFromPage($baseUrl, $page);
        }

        $this->info('Found '.count($this->postImageMap).' post-image mappings');
        $this->newLine();

        // Step 2: Download unique images
        $this->info('Step 2: Downloading unique images...');
        $uniqueUrls = array_unique(array_column($this->postImageMap, 'url'));
        $this->info('Found '.count($uniqueUrls).' unique images to download');
        $this->newLine();

        $downloaded = 0;
        $skipped = 0;

        foreach ($uniqueUrls as $imageUrl) {
            $result = $this->downloadUniqueImage($imageUrl, $outputDir);
            if ($result['success']) {
                $this->uniqueImages[$imageUrl] = $result['path'];
                $this->line('  ✓ Downloaded: '.basename($result['path']));
                $downloaded++;
            } else {
                $this->warn("  ⊘ {$result['reason']}");
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Downloaded: {$downloaded}, Skipped: {$skipped}");
        $this->newLine();

        // Step 3: Copy images to correct filenames for each post
        $this->info('Step 3: Creating post-specific image files...');
        $copied = 0;

        foreach ($this->postImageMap as $slug => $data) {
            $imageUrl = $data['url'];
            $post = BlogPost::where('slug', $slug)->first();

            if (! $post) {
                continue;
            }

            if (! isset($this->uniqueImages[$imageUrl])) {
                $this->warn("  ⊘ Image not downloaded for: {$post->title}");

                continue;
            }

            $sourceFile = $this->uniqueImages[$imageUrl];
            $extension = pathinfo($sourceFile, PATHINFO_EXTENSION);
            $targetFilename = "blog-{$slug}.{$extension}";
            $targetPath = $outputDir.'/'.$targetFilename;

            // Copy if doesn't exist or is different
            if (! file_exists($targetPath) || md5_file($targetPath) !== md5_file($sourceFile)) {
                copy($sourceFile, $targetPath);
                $this->line("  ✓ Created: {$targetFilename} → {$post->title}");
                $copied++;
            } else {
                $this->line("  ⊘ Already exists: {$targetFilename}");
            }
        }

        $this->newLine();
        $this->info("Created {$copied} post-specific image files");
        $this->newLine();
        $this->info('Next steps:');
        $this->line('1. Copy to optimized: cp content-migration/images/original/strengthstoolbox/blog/* content-migration/images/optimized/strengthstoolbox/');
        $this->line('2. Upload to media library: php artisan db:seed --class=MediaSeeder');
        $this->line('3. Assign to blog posts: php artisan blog:reassign-images --force');

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
            $entries = $xpath->query('//article | //div[contains(@class, "post")]');

            foreach ($entries as $entry) {
                $link = $xpath->query('.//a[contains(@href, "/2023/")]', $entry)->item(0);
                if (! $link) {
                    continue;
                }

                $href = $link->getAttribute('href');
                if (! preg_match('/\/(\d{4})\/(\d{2})\/([^\/]+)\/$/', $href, $matches)) {
                    continue;
                }

                $slug = $matches[3];
                $img = $xpath->query('.//img[contains(@class, "wp-post-image")]', $entry)->item(0);
                if (! $img) {
                    continue;
                }

                $src = $img->getAttribute('src');
                if (! $src || str_contains($src, '-150x150') || str_contains($src, '-100x100')) {
                    continue;
                }

                $fullSizeUrl = preg_replace('/-\d+x\d+\.(jpg|jpeg|png)$/i', '.$1', $src);
                $absoluteUrl = $this->makeAbsoluteUrl($fullSizeUrl, rtrim($baseUrl, '/'), $url);

                if (! isset($this->postImageMap[$slug])) {
                    $this->postImageMap[$slug] = ['url' => $absoluteUrl];
                }
            }
        } catch (\Exception $e) {
            // Silent fail
        }
    }

    protected function downloadUniqueImage(string $url, string $outputDir): array
    {
        try {
            // Generate a unique filename from the URL
            $path = parse_url($url, PHP_URL_PATH);
            $filename = basename($path);
            if (empty($filename) || $filename === '/') {
                $filename = 'image-'.md5($url).'.jpg';
            }

            $filePath = $outputDir.'/_unique_'.$filename;

            // Skip if already downloaded
            if (file_exists($filePath)) {
                return ['success' => true, 'path' => $filePath, 'filename' => $filename];
            }

            $response = Http::timeout(30)->get($url);
            if (! $response->successful()) {
                return ['success' => false, 'reason' => "HTTP {$response->status()}"];
            }

            $content = $response->body();
            $contentType = $response->header('Content-Type', '');

            if (! str_starts_with($contentType, 'image/')) {
                return ['success' => false, 'reason' => 'Not an image'];
            }

            file_put_contents($filePath, $content);

            return ['success' => true, 'path' => $filePath, 'filename' => $filename];
        } catch (\Exception $e) {
            return ['success' => false, 'reason' => $e->getMessage()];
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
