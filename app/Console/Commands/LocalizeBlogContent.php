<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MediaService;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Download all TSA blog content (images, CSV files) and localize references
 */
class LocalizeBlogContent extends Command
{
    protected $signature = 'blog:localize-content
                            {--inventory=content-migration/tsa-blog-inventory.json : Path to inventory JSON file}
                            {--output=content-migration/tsa-blog-inventory.json : Path to output updated inventory}
                            {--delay=1 : Seconds to wait between HTTP requests}
                            {--dry-run : Preview without downloading or updating}';

    protected $description = 'Download all TSA blog images and CSV files, upload to media library, and update inventory with local references';

    protected MediaService $mediaService;
    protected array $urlToMediaMap = [];
    protected array $urlToCsvMap = [];
    protected ?int $userId = null;

    public function __construct(MediaService $mediaService)
    {
        parent::__construct();
        $this->mediaService = $mediaService;
    }

    public function handle(): int
    {
        $inventoryPath = $this->option('inventory');
        $outputPath = $this->option('output');
        $delay = max(0, (int) $this->option('delay'));
        $dryRun = $this->option('dry-run');

        $fullInventoryPath = str_starts_with($inventoryPath, '/') ? $inventoryPath : base_path($inventoryPath);
        $fullOutputPath = str_starts_with($outputPath, '/') ? $outputPath : base_path($outputPath);

        if (! file_exists($fullInventoryPath)) {
            $this->error("Inventory file not found: {$fullInventoryPath}");

            return Command::FAILURE;
        }

        $inventory = json_decode(file_get_contents($fullInventoryPath), true);
        if (! is_array($inventory)) {
            $this->error('Invalid inventory file format');

            return Command::FAILURE;
        }

        $this->info('Localizing blog content...');
        $this->line("Inventory: {$inventoryPath}");
        $this->line("Output: {$outputPath}");
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be downloaded or updated');
        }
        $this->newLine();

        // Get or create a user for media uploads
        if (! $dryRun) {
            try {
                $this->userId = User::where('role', 'admin')->orWhere('role', 'author')->first()?->id ?? User::first()?->id;
                if (! $this->userId) {
                    $this->warn('No user found. Media uploads will have null uploaded_by.');
                    $this->userId = null;
                }
            } catch (\Exception $e) {
                // Database not available - continue without user ID
                $this->warn('Database not available. Media uploads will have null uploaded_by.');
                $this->userId = null;
            }
        }

        // Extract all URLs
        $this->info('Extracting URLs from inventory...');
        $imageUrls = $this->extractImageUrls($inventory);
        $csvUrls = $this->extractCsvUrls($inventory);
        $this->line("  Found ".count($imageUrls).' unique image URLs');
        $this->line("  Found ".count($csvUrls).' unique CSV URLs');
        $this->newLine();

        // Download and process images
        if (! empty($imageUrls)) {
            $this->info('Downloading and uploading images to media library...');
            $this->downloadAndUploadImages($imageUrls, $delay, $dryRun);
            $this->newLine();
        }

        // Download CSV files
        if (! empty($csvUrls)) {
            $this->info('Downloading CSV files...');
            $this->downloadCsvFiles($csvUrls, $delay, $dryRun);
            $this->newLine();
        }

        // Update inventory with local references
        $this->info('Updating inventory with local references...');
        $updatedInventory = $this->updateInventoryReferences($inventory, $dryRun);
        $this->newLine();

        // Write updated inventory
        if (! $dryRun) {
            $dir = dirname($fullOutputPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents(
                $fullOutputPath,
                json_encode($updatedInventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
            $this->info("✓ Updated inventory written to: {$outputPath}");
        } else {
            $this->info("Would write updated inventory to: {$outputPath}");
        }

        $this->newLine();
        $this->info('✓ Localization complete!');

        return Command::SUCCESS;
    }

    /**
     * Extract all unique image URLs from inventory
     */
    protected function extractImageUrls(array $inventory): array
    {
        $urls = [];

        foreach ($inventory as $item) {
            // Featured image URL
            if (! empty($item['featured_image_url'] ?? '')) {
                $url = $item['featured_image_url'];
                if ($this->isTsaUrl($url)) {
                    $urls[$url] = true;
                }
            }

            // Images in content HTML
            if (! empty($item['content_html'] ?? '')) {
                $contentUrls = $this->extractImageUrlsFromHtml($item['content_html']);
                foreach ($contentUrls as $url) {
                    if ($this->isTsaUrl($url)) {
                        $urls[$url] = true;
                    }
                }
            }
        }

        return array_keys($urls);
    }

    /**
     * Extract image URLs from HTML content (including srcset)
     */
    protected function extractImageUrlsFromHtml(string $html): array
    {
        $urls = [];

        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $images = $xpath->query('//img');

        foreach ($images as $img) {
            // Get src attribute
            $src = $img->getAttribute('src');
            if ($src) {
                $urls[] = $src;
            }

            // Get srcset attribute and parse it
            $srcset = $img->getAttribute('srcset');
            if ($srcset) {
                // Parse srcset: "url1 1024w, url2 980w, url3 480w"
                preg_match_all('/([^\s,]+)(?:\s+\d+w)?/i', $srcset, $matches);
                if (! empty($matches[1])) {
                    foreach ($matches[1] as $url) {
                        $urls[] = trim($url);
                    }
                }
            }
        }

        return $urls;
    }

    /**
     * Extract CSV file URLs from inventory
     */
    protected function extractCsvUrls(array $inventory): array
    {
        $urls = [];

        foreach ($inventory as $item) {
            if (! empty($item['content_html'] ?? '')) {
                $contentUrls = $this->extractCsvUrlsFromHtml($item['content_html']);
                foreach ($contentUrls as $url) {
                    if ($this->isTsaUrl($url) && $this->isCsvUrl($url)) {
                        $urls[$url] = true;
                    }
                }
            }
        }

        return array_keys($urls);
    }

    /**
     * Extract CSV URLs from HTML content
     */
    protected function extractCsvUrlsFromHtml(string $html): array
    {
        $urls = [];

        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $links = $xpath->query('//a[@href]');

        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if ($href && $this->isCsvUrl($href)) {
                $urls[] = $href;
            }
        }

        return $urls;
    }

    /**
     * Check if URL is from TSA domain
     */
    protected function isTsaUrl(string $url): bool
    {
        return str_contains($url, 'tsabusinessschool.co.za');
    }

    /**
     * Check if URL is a CSV file
     */
    protected function isCsvUrl(string $url): bool
    {
        return str_ends_with(strtolower($url), '.csv');
    }

    /**
     * Download images and upload to media library
     */
    protected function downloadAndUploadImages(array $urls, int $delay, bool $dryRun): void
    {
        $total = count($urls);
        $downloaded = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($urls as $index => $url) {
            $this->line("  [".($index + 1)."/{$total}] Processing: ".basename(parse_url($url, PHP_URL_PATH)));

            // Check if already processed
            if (isset($this->urlToMediaMap[$url])) {
                $this->line("    ⊘ Already processed");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("    Would download and upload: {$url}");
                $downloaded++;
                continue;
            }

            try {
                if ($delay > 0 && $index > 0) {
                    sleep($delay);
                }

                $response = Http::timeout(30)->get($url);
                if (! $response->successful()) {
                    $this->warn("    ⊘ HTTP {$response->status()}");
                    $errors++;
                    continue;
                }

                $content = $response->body();
                $contentType = $response->header('Content-Type', '');

                // Verify it's an image
                if (! str_starts_with($contentType, 'image/')) {
                    $this->warn("    ⊘ Not an image (Content-Type: {$contentType})");
                    $errors++;
                    continue;
                }

                // Create temporary file
                $tempPath = sys_get_temp_dir().'/'.Str::random(16).'.'.pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                file_put_contents($tempPath, $content);

                // Store file directly to storage (works without database)
                $filename = basename(parse_url($url, PHP_URL_PATH));
                $relativePath = 'blog/'.$filename;
                $disk = Storage::disk('public');
                
                // Check if file already exists
                if ($disk->exists($relativePath)) {
                    $this->line("    ⊘ Already exists: {$relativePath}");
                    $this->urlToMediaMap[$url] = asset('storage/'.$relativePath);
                    @unlink($tempPath);
                    $skipped++;
                    continue;
                }
                
                // Store file to disk
                $disk->put($relativePath, $content);
                
                // Try to upload to media library if database is available
                try {
                    $uploadedFile = new UploadedFile(
                        $tempPath,
                        $filename,
                        $contentType,
                        null,
                        true
                    );
                    
                    $media = $this->mediaService->upload($uploadedFile, [
                        'directory' => 'blog',
                        'uploaded_by' => $this->userId,
                    ]);
                    
                    // Use media library URL
                    $this->urlToMediaMap[$url] = $media->url;
                } catch (\Exception $e) {
                    // Database not available - use direct storage path
                    $this->urlToMediaMap[$url] = asset('storage/'.$relativePath);
                }

                // Clean up temp file
                @unlink($tempPath);

                $this->line("    ✓ Stored: {$relativePath}");
                $downloaded++;
            } catch (\Throwable $e) {
                $this->error("    ✗ Error: ".$e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->info("Summary: Downloaded {$downloaded}, Skipped {$skipped}, Errors {$errors}");
    }

    /**
     * Download CSV files
     */
    protected function downloadCsvFiles(array $urls, int $delay, bool $dryRun): void
    {
        $storageDir = 'blog/files';
        $disk = Storage::disk('public');

        if (! $dryRun && ! $disk->exists($storageDir)) {
            $disk->makeDirectory($storageDir);
        }

        $total = count($urls);
        $downloaded = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($urls as $index => $url) {
            $filename = basename(parse_url($url, PHP_URL_PATH));
            $this->line("  [".($index + 1)."/{$total}] Processing: {$filename}");

            // Check if already processed
            if (isset($this->urlToCsvMap[$url])) {
                $this->line("    ⊘ Already processed");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("    Would download: {$url}");
                $downloaded++;
                continue;
            }

            try {
                if ($delay > 0 && $index > 0) {
                    sleep($delay);
                }

                $response = Http::timeout(30)->get($url);
                if (! $response->successful()) {
                    $this->warn("    ⊘ HTTP {$response->status()}");
                    $errors++;
                    continue;
                }

                $relativePath = $storageDir.'/'.$filename;
                $disk->put($relativePath, $response->body());

                // Map URL to local path
                $this->urlToCsvMap[$url] = asset('storage/'.$relativePath);

                $this->line("    ✓ Downloaded: {$relativePath}");
                $downloaded++;
            } catch (\Throwable $e) {
                $this->error("    ✗ Error: ".$e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->info("Summary: Downloaded {$downloaded}, Skipped {$skipped}, Errors {$errors}");
    }

    /**
     * Update inventory with local references
     */
    protected function updateInventoryReferences(array $inventory, bool $dryRun): array
    {
        $updated = 0;

        foreach ($inventory as &$item) {
            $itemUpdated = false;

            // Update featured image URL
            if (! empty($item['featured_image_url'] ?? '')) {
                $url = $item['featured_image_url'];
                if (isset($this->urlToMediaMap[$url])) {
                    $item['featured_image_url'] = $this->urlToMediaMap[$url];
                    $itemUpdated = true;
                }
            }

            // Update content HTML
            if (! empty($item['content_html'] ?? '')) {
                $updatedContent = $this->replaceUrlsInHtml($item['content_html']);
                if ($updatedContent !== $item['content_html']) {
                    $item['content_html'] = $updatedContent;
                    $itemUpdated = true;
                }
            }

            if ($itemUpdated) {
                $updated++;
            }
        }

        $this->line("  Updated {$updated} items");

        return $inventory;
    }

    /**
     * Replace TSA URLs in HTML content with local references
     */
    protected function replaceUrlsInHtml(string $html): string
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $modified = false;

        // Replace image src attributes
        $images = $xpath->query('//img[@src]');
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            if (isset($this->urlToMediaMap[$src])) {
                $img->setAttribute('src', $this->urlToMediaMap[$src]);
                $modified = true;
            }
        }

        // Replace image srcset attributes
        $imagesWithSrcset = $xpath->query('//img[@srcset]');
        foreach ($imagesWithSrcset as $img) {
            $srcset = $img->getAttribute('srcset');
            if ($srcset) {
                $newSrcset = $this->replaceSrcsetUrls($srcset);
                if ($newSrcset !== $srcset) {
                    $img->setAttribute('srcset', $newSrcset);
                    $modified = true;
                }
            }
        }

        // Replace CSV file links
        $links = $xpath->query('//a[@href]');
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if (isset($this->urlToCsvMap[$href])) {
                $link->setAttribute('href', $this->urlToCsvMap[$href]);
                $modified = true;
            }
        }

        if (! $modified) {
            return $html;
        }

        // Get updated HTML
        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            $newHtml = '';
            foreach ($body->childNodes as $child) {
                $newHtml .= $dom->saveHTML($child);
            }
            return $newHtml;
        }

        return $html;
    }

    /**
     * Replace URLs in srcset attribute
     */
    protected function replaceSrcsetUrls(string $srcset): string
    {
        // Parse srcset: "url1 1024w, url2 980w, url3 480w"
        $parts = preg_split('/,\s*/', $srcset);
        $newParts = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/^(.+?)(\s+\d+w)?$/i', $part, $matches)) {
                $url = trim($matches[1]);
                $size = $matches[2] ?? '';

                if (isset($this->urlToMediaMap[$url])) {
                    $newParts[] = $this->urlToMediaMap[$url].$size;
                } else {
                    $newParts[] = $part;
                }
            } else {
                $newParts[] = $part;
            }
        }

        return implode(', ', $newParts);
    }
}
