<?php

namespace App\Console\Commands;

use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DownloadWebsiteImages extends Command
{
    protected $signature = 'images:download 
                            {--source= : Source identifier (tsa|strengthstoolbox)}
                            {--url= : Base URL to download images from}
                            {--page= : Specific page URL to download images from}
                            {--output=content-migration/images/original : Output directory}';

    protected $description = 'Download images from source websites for migration';

    protected array $downloadedUrls = [];

    protected array $downloadLog = [];

    public function handle(): int
    {
        $source = $this->option('source');
        $baseUrl = $this->option('url');
        $pageUrl = $this->option('page');
        $outputDir = $this->option('output');

        if (! $source && ! $baseUrl) {
            $this->error('Either --source or --url must be provided');
            $this->newLine();
            $this->line('Usage examples:');
            $this->line('  php artisan images:download --source=tsa --url=https://www.tsabusinessschool.co.za');
            $this->line('  php artisan images:download --source=strengthstoolbox --url=https://www.thestrengthstoolbox.com');
            $this->line('  php artisan images:download --url=https://example.com --page=/about-us');

            return Command::FAILURE;
        }

        // Set default URLs based on source
        if ($source === 'tsa' && ! $baseUrl) {
            $baseUrl = 'https://www.tsabusinessschool.co.za';
        } elseif ($source === 'strengthstoolbox' && ! $baseUrl) {
            $baseUrl = 'https://www.thestrengthstoolbox.com';
        }

        if (! $baseUrl) {
            $this->error('Base URL is required');

            return Command::FAILURE;
        }

        $baseUrl = rtrim($baseUrl, '/');
        $outputPath = base_path($outputDir);

        // Create source-specific directory
        if ($source) {
            $outputPath = $outputPath.'/'.$source;
        }

        $this->info("Downloading images from: {$baseUrl}");
        $this->info("Output directory: {$outputPath}");
        $this->newLine();

        // Create output directory structure
        if (! is_dir($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        // Determine which pages to process
        $pagesToProcess = $this->getPagesToProcess($baseUrl, $pageUrl);

        $totalImages = 0;
        $downloaded = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($pagesToProcess as $page) {
            $this->line("Processing page: {$page}");
            $images = $this->extractImagesFromPage($page, $baseUrl);

            foreach ($images as $imageUrl) {
                $totalImages++;
                try {
                    $result = $this->downloadImage($imageUrl, $outputPath, $baseUrl);
                    if ($result['success']) {
                        $downloaded++;
                        $this->line("  ✓ Downloaded: {$result['filename']}");
                    } else {
                        $skipped++;
                        $this->line("  ⊘ Skipped: {$result['reason']}");
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("  ✗ Error downloading {$imageUrl}: ".$e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info('Download Summary:');
        $this->line("  Total images found: {$totalImages}");
        $this->line("  Downloaded: {$downloaded}");
        $this->line("  Skipped: {$skipped}");
        if ($errors > 0) {
            $this->error("  Errors: {$errors}");
        }

        // Save download log
        $this->saveDownloadLog($outputPath);

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    protected function getPagesToProcess(string $baseUrl, ?string $pageUrl): array
    {
        if ($pageUrl) {
            // Single page
            return [Str::startsWith($pageUrl, 'http') ? $pageUrl : $baseUrl.$pageUrl];
        }

        // Default pages to process
        $pages = [
            '/',
            '/about-us',
            '/blog',
        ];

        // Add source-specific pages
        if (str_contains($baseUrl, 'tsabusinessschool')) {
            $pages = array_merge($pages, [
                '/strengths-programme',
            ]);
        }

        return array_map(fn ($page) => $baseUrl.$page, $pages);
    }

    protected function extractImagesFromPage(string $url, string $baseUrl): array
    {
        try {
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                $this->warn("  Failed to fetch page: {$url} (Status: {$response->status()})");

                return [];
            }

            $html = $response->body();
            $images = [];

            // Parse HTML
            libxml_use_internal_errors(true);
            $dom = new DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);

            // Find all img tags
            $imgNodes = $xpath->query('//img[@src]');

            foreach ($imgNodes as $imgNode) {
                $src = $imgNode->getAttribute('src');

                // Skip data URIs, placeholders, and very small images
                if (str_starts_with($src, 'data:') ||
                    str_contains($src, 'placeholder') ||
                    str_contains($src, 'spacer') ||
                    str_contains($src, 'pixel')) {
                    continue;
                }

                // Convert relative URLs to absolute
                $absoluteUrl = $this->makeAbsoluteUrl($src, $baseUrl, $url);

                // Only process image URLs
                if ($this->isImageUrl($absoluteUrl)) {
                    $images[] = $absoluteUrl;
                }
            }

            // Also check for background images in style attributes
            $elementsWithBg = $xpath->query('//*[@style]');
            foreach ($elementsWithBg as $element) {
                $style = $element->getAttribute('style');
                if (preg_match('/background-image:\s*url\(["\']?([^"\']+)["\']?\)/', $style, $matches)) {
                    $bgUrl = $this->makeAbsoluteUrl($matches[1], $baseUrl, $url);
                    if ($this->isImageUrl($bgUrl)) {
                        $images[] = $bgUrl;
                    }
                }
            }

            return array_unique($images);
        } catch (\Exception $e) {
            $this->error("  Error extracting images from {$url}: ".$e->getMessage());

            return [];
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

    protected function isImageUrl(string $url): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $path = parse_url($url, PHP_URL_PATH);

        if (! $path) {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, $imageExtensions);
    }

    protected function downloadImage(string $url, string $outputDir, string $baseUrl): array
    {
        // Skip if already downloaded
        if (in_array($url, $this->downloadedUrls)) {
            return ['success' => false, 'reason' => 'Already downloaded'];
        }

        try {
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                return ['success' => false, 'reason' => "HTTP {$response->status()}"];
            }

            $content = $response->body();
            $contentType = $response->header('Content-Type', '');

            // Verify it's actually an image
            if (! str_starts_with($contentType, 'image/') && ! $this->isImageContent($content)) {
                return ['success' => false, 'reason' => 'Not an image'];
            }

            // Generate filename
            $filename = $this->generateFilename($url, $baseUrl);
            $filePath = $outputDir.'/'.$filename;

            // Create subdirectories based on page context
            $subDir = $this->determineSubdirectory($url, $baseUrl);
            if ($subDir) {
                $filePath = $outputDir.'/'.$subDir.'/'.$filename;
                $subDirPath = $outputDir.'/'.$subDir;
                if (! is_dir($subDirPath)) {
                    mkdir($subDirPath, 0755, true);
                }
            }

            // Skip if file already exists
            if (file_exists($filePath)) {
                $this->downloadedUrls[] = $url;

                return ['success' => false, 'reason' => 'File exists', 'filename' => basename($filePath)];
            }

            // Save file
            file_put_contents($filePath, $content);

            // Log download
            $this->downloadedUrls[] = $url;
            $this->downloadLog[] = [
                'url' => $url,
                'filename' => basename($filePath),
                'path' => str_replace(base_path(), '', $filePath),
                'size' => filesize($filePath),
                'downloaded_at' => now()->toIso8601String(),
            ];

            return ['success' => true, 'filename' => basename($filePath), 'path' => $filePath];
        } catch (\Exception $e) {
            return ['success' => false, 'reason' => $e->getMessage()];
        }
    }

    protected function isImageContent(string $content): bool
    {
        // Check for image magic bytes
        $magicBytes = [
            "\xFF\xD8\xFF", // JPEG
            "\x89\x50\x4E\x47", // PNG
            'GIF87a', // GIF87a
            'GIF89a', // GIF89a
            'RIFF', // WebP (starts with RIFF)
        ];

        foreach ($magicBytes as $magic) {
            if (str_starts_with($content, $magic)) {
                return true;
            }
        }

        return false;
    }

    protected function generateFilename(string $url, string $baseUrl): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if ($path) {
            $filename = basename($path);
            // Clean filename
            $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

            // Ensure it has an extension
            if (! pathinfo($filename, PATHINFO_EXTENSION)) {
                $extension = $this->guessExtensionFromUrl($url);
                $filename .= '.'.$extension;
            }

            // Add hash to ensure uniqueness
            $hash = substr(md5($url), 0, 8);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            return $name.'_'.$hash.'.'.$ext;
        }

        // Fallback: generate from URL
        $hash = md5($url);

        return 'image_'.substr($hash, 0, 12).'.jpg';
    }

    protected function guessExtensionFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return $ext;
        }

        return 'jpg'; // Default
    }

    protected function determineSubdirectory(string $url, string $baseUrl): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        // Try to determine context from URL
        if (str_contains($url, '/about') || str_contains($url, 'about')) {
            return 'about';
        }
        if (str_contains($url, '/blog') || str_contains($url, 'blog')) {
            return 'blog';
        }
        if (str_contains($url, '/home') || str_contains($url, 'homepage') || str_contains($url, 'hero')) {
            return 'homepage';
        }
        if (str_contains($url, '/strengths') || str_contains($url, 'strengths-programme')) {
            return 'strengths-programme';
        }

        return null;
    }

    protected function saveDownloadLog(string $outputDir): void
    {
        $logFile = $outputDir.'/download-log.json';
        file_put_contents($logFile, json_encode([
            'downloaded_at' => now()->toIso8601String(),
            'total_downloaded' => count($this->downloadLog),
            'images' => $this->downloadLog,
        ], JSON_PRETTY_PRINT));

        $this->line("Download log saved to: {$logFile}");
    }
}
