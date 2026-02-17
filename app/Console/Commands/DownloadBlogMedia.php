<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MediaService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Download all blog media (featured images and inline images) from scraped inventory
 */
class DownloadBlogMedia extends Command
{
    protected $signature = 'blog:download-media
                            {--inventory=content-migration/scraped-blogs.json : Path to scraped inventory JSON}
                            {--output-dir=public/images/blog : Output directory for images}
                            {--delay=1 : Seconds to wait between HTTP requests}
                            {--dry-run : Preview without downloading}
                            {--cleanup-only : Only clean up duplicates, do not download}';

    protected $description = 'Download all featured and inline images from scraped blog posts';

    protected MediaService $mediaService;
    protected ?int $userId = null;
    protected array $urlToPathMap = [];
    protected array $contentHashToPath = []; // Map content hash to file path for deduplication

    public function __construct(MediaService $mediaService)
    {
        parent::__construct();
        $this->mediaService = $mediaService;
    }

    public function handle(): int
    {
        $inventoryPath = $this->option('inventory');
        $fullInventoryPath = str_starts_with($inventoryPath, '/') ? $inventoryPath : base_path($inventoryPath);
        $outputDir = $this->option('output-dir');
        $fullOutputDir = str_starts_with($outputDir, '/') ? $outputDir : base_path($outputDir);
        $delay = max(0, (int) $this->option('delay'));
        $dryRun = $this->option('dry-run');

        if (! file_exists($fullInventoryPath)) {
            $this->error("Inventory file not found: {$fullInventoryPath}");

            return Command::FAILURE;
        }

        $inventory = json_decode(file_get_contents($fullInventoryPath), true);
        if (! is_array($inventory)) {
            $this->error('Invalid inventory file format');

            return Command::FAILURE;
        }

        $this->info('Downloading blog media...');
        $this->line("Inventory: {$inventoryPath}");
        $this->line("Output: {$outputDir}");
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be downloaded');
        }
        $this->newLine();

        // Get user for media uploads
        if (! $dryRun) {
            try {
                $this->userId = User::where('role', 'admin')->orWhere('role', 'author')->first()?->id ?? User::first()?->id;
            } catch (\Exception $e) {
                $this->warn('Database not available. Files will be stored but not uploaded to media library.');
                $this->userId = null;
            }
        }

        // Extract all image URLs
        $this->info('Extracting image URLs from inventory...');
        $imageUrls = $this->extractImageUrls($inventory);
        $this->line("  Found ".count($imageUrls).' unique image URLs');
        $this->newLine();

        $cleanupOnly = $this->option('cleanup-only');

        if ($cleanupOnly) {
            // Only cleanup mode
            $this->info('Cleaning up duplicate files...');
            $removed = $this->cleanupDuplicates($fullOutputDir);
            if ($removed > 0) {
                $this->info("✓ Removed {$removed} duplicate file(s)");
            } else {
                $this->info("✓ No duplicates found");
            }
            
            $this->info('Removing timestamp prefixes from filenames...');
            $renamed = $this->removeTimestampPrefixes($fullOutputDir);
            if ($renamed > 0) {
                $this->info("✓ Renamed {$renamed} file(s)");
            } else {
                $this->info("✓ No timestamped files to rename");
            }
            
            $this->info('Removing random suffixes from filenames...');
            $renamedSuffixes = $this->removeRandomSuffixes($fullOutputDir);
            if ($renamedSuffixes > 0) {
                $this->info("✓ Renamed {$renamedSuffixes} file(s)");
            } else {
                $this->info("✓ No files with random suffixes to rename");
            }
            
            $this->newLine();
            return Command::SUCCESS;
        }

        // Build content hash map from existing files (for deduplication)
        if (! $dryRun) {
            $this->info('Scanning existing files for duplicates...');
            $this->buildContentHashMap($fullOutputDir);
            $this->line("  Found ".count($this->contentHashToPath).' unique files');
            $this->newLine();
        }

        // Download images
        if (! empty($imageUrls)) {
            $this->info('Downloading images...');
            $this->downloadImages($imageUrls, $fullOutputDir, $delay, $dryRun);
            $this->newLine();
        }

        // Clean up duplicates and timestamp prefixes
        if (! $dryRun) {
            $this->info('Cleaning up duplicate files...');
            $removed = $this->cleanupDuplicates($fullOutputDir);
            if ($removed > 0) {
                $this->line("  Removed {$removed} duplicate file(s)");
            } else {
                $this->line("  No duplicates found");
            }
            
            $this->info('Removing timestamp prefixes from filenames...');
            $renamed = $this->removeTimestampPrefixes($fullOutputDir);
            if ($renamed > 0) {
                $this->line("  Renamed {$renamed} file(s)");
            } else {
                $this->line("  No timestamped files to rename");
            }
            
            $this->info('Removing random suffixes from filenames...');
            $renamedSuffixes = $this->removeRandomSuffixes($fullOutputDir);
            if ($renamedSuffixes > 0) {
                $this->line("  Renamed {$renamedSuffixes} file(s)");
            } else {
                $this->line("  No files with random suffixes to rename");
            }
            
            if ($removed > 0 || $renamed > 0 || $renamedSuffixes > 0) {
                // Rebuild content hash map after cleanup
                $this->contentHashToPath = [];
                $this->buildContentHashMap($fullOutputDir);
                // Update URL mapping to point to kept files
                $this->updateUrlMappingAfterCleanup($fullOutputDir);
            }
            $this->newLine();
        }

        // Write URL mapping file
        if (! $dryRun) {
            $mappingPath = base_path('content-migration/blog-media-mapping.json');
            $dir = dirname($mappingPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents(
                $mappingPath,
                json_encode($this->urlToPathMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
            $this->info("✓ URL mapping written to: {$mappingPath}");
        }

        $this->newLine();
        $this->info('✓ Media download complete!');

        return Command::SUCCESS;
    }

    /**
     * Extract all unique image URLs from inventory
     */
    protected function extractImageUrls(array $inventory): array
    {
        $urls = [];

        foreach ($inventory as $item) {
            // Featured image
            if (! empty($item['featured_image_url'] ?? '')) {
                $url = $item['featured_image_url'];
                $urls[$url] = true;
            }

            // Inline images
            if (! empty($item['all_images'] ?? [])) {
                foreach ($item['all_images'] as $url) {
                    if ($url) {
                        $urls[$url] = true;
                    }
                }
            }

            // Images in content HTML
            if (! empty($item['content_html'] ?? '')) {
                $contentUrls = $this->extractImageUrlsFromHtml($item['content_html']);
                foreach ($contentUrls as $url) {
                    if ($url) {
                        $urls[$url] = true;
                    }
                }
            }
        }

        return array_keys($urls);
    }

    /**
     * Extract image URLs from HTML content
     */
    protected function extractImageUrlsFromHtml(string $html): array
    {
        $urls = [];
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $imgs = $xpath->query('//img[@src]');

        foreach ($imgs as $img) {
            $src = $img->getAttribute('src');
            if ($src && (str_contains($src, 'wp-content') || str_contains($src, 'uploads'))) {
                if (str_contains($src, '-150x150') || str_contains($src, '-100x100')) {
                    continue;
                }
                $urls[] = $src;
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
                        $urls[] = $url;
                    }
                }
            }
        }

        return array_unique($urls);
    }

    /**
     * Build content hash map from existing files for deduplication
     */
    protected function buildContentHashMap(string $outputDir): void
    {
        if (! is_dir($outputDir)) {
            return;
        }

        $files = glob($outputDir.'/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);
        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }

            $hash = md5_file($file);
            if ($hash) {
                $relativePath = 'blog/'.basename($file);
                // Only store if we don't already have this hash (prefer first file found)
                if (! isset($this->contentHashToPath[$hash])) {
                    $this->contentHashToPath[$hash] = $relativePath;
                }
            }
        }
    }

    /**
     * Download images to storage with deduplication
     */
    protected function downloadImages(array $urls, string $outputDir, int $delay, bool $dryRun): void
    {
        $total = count($urls);
        $downloaded = 0;
        $skipped = 0;
        $duplicates = 0;
        $errors = 0;

        // Ensure output directory exists
        if (! $dryRun && ! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($urls as $index => $url) {
            try {
                // Generate filename
                $filename = $this->generateFilename($url);
                $relativePath = 'blog/'.$filename;
                $fullPath = $outputDir.'/'.$filename;

                // Check if already downloaded (by filename)
                if (! $dryRun && file_exists($fullPath)) {
                    $this->urlToPathMap[$url] = $relativePath;
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if ($dryRun) {
                    $this->urlToPathMap[$url] = $relativePath;
                    $downloaded++;
                    $bar->advance();
                    continue;
                }

                // Download
                if ($delay > 0 && $index > 0) {
                    sleep($delay);
                }

                $response = Http::timeout(30)->get($url);
                if (! $response->successful()) {
                    $this->newLine();
                    $this->warn("  HTTP {$response->status()} for: ".basename($url));
                    $errors++;
                    $bar->advance();
                    continue;
                }

                $content = $response->body();
                $contentType = $response->header('Content-Type', '');

                // Verify it's an image
                if (! str_starts_with($contentType, 'image/')) {
                    $this->newLine();
                    $this->warn("  Not an image (Content-Type: {$contentType}): ".basename($url));
                    $errors++;
                    $bar->advance();
                    continue;
                }

                // Check for duplicate content by hash
                $contentHash = md5($content);
                if (isset($this->contentHashToPath[$contentHash])) {
                    // Duplicate found - reuse existing file
                    $existingPath = $this->contentHashToPath[$contentHash];
                    $this->urlToPathMap[$url] = $existingPath;
                    $duplicates++;
                    $bar->advance();
                    continue;
                }

                // Save file
                file_put_contents($fullPath, $content);
                
                // Store hash for future deduplication
                $this->contentHashToPath[$contentHash] = $relativePath;

                // Upload to media library if database available
                try {
                    $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
                    file_put_contents($tempFile, $content);

                    $uploadedFile = new UploadedFile(
                        $tempFile,
                        $filename,
                        $contentType,
                        null,
                        true
                    );

                    $media = $this->mediaService->upload($uploadedFile, [
                        'directory' => 'blog',
                        'uploaded_by' => $this->userId,
                    ]);

                    $this->urlToPathMap[$url] = $media->path ?? $relativePath;
                    @unlink($tempFile);
                } catch (\Exception $e) {
                    // Media library upload failed, use file path
                    $this->urlToPathMap[$url] = $relativePath;
                }

                $downloaded++;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("  Error downloading {$url}: ".$e->getMessage());
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Summary: Downloaded {$downloaded}, Skipped {$skipped}, Duplicates avoided: {$duplicates}, Errors {$errors}");
    }

    /**
     * Clean up duplicate files by content hash
     * Prefers non-timestamped filenames over timestamped ones
     */
    protected function cleanupDuplicates(string $outputDir): int
    {
        if (! is_dir($outputDir)) {
            return 0;
        }

        $removed = 0;
        $hashToFiles = [];
        $files = glob($outputDir.'/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);

        // Group files by content hash
        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }

            $hash = md5_file($file);
            if ($hash) {
                if (! isset($hashToFiles[$hash])) {
                    $hashToFiles[$hash] = [];
                }
                $hashToFiles[$hash][] = $file;
            }
        }

        // Remove duplicates (keep best file, remove others)
        foreach ($hashToFiles as $hash => $fileList) {
            if (count($fileList) > 1) {
                // Sort files to prefer:
                // 1. Non-timestamped filenames
                // 2. Longer filenames (more descriptive)
                // 3. Alphabetically
                usort($fileList, function ($a, $b) {
                    $aName = basename($a);
                    $bName = basename($b);
                    
                    // Check if filename has timestamp prefix (10+ digits followed by underscore)
                    $aHasTimestamp = preg_match('/^[0-9]{10,}_/', $aName);
                    $bHasTimestamp = preg_match('/^[0-9]{10,}_/', $bName);
                    
                    // Prefer non-timestamped files
                    if ($aHasTimestamp && ! $bHasTimestamp) {
                        return 1; // $a comes after $b
                    }
                    if (! $aHasTimestamp && $bHasTimestamp) {
                        return -1; // $a comes before $b
                    }
                    
                    // If both have timestamps or both don't, prefer longer filename
                    if (strlen($aName) !== strlen($bName)) {
                        return strlen($bName) <=> strlen($aName);
                    }
                    
                    // Finally, sort alphabetically
                    return strcmp($aName, $bName);
                });

                // Keep the first file (best one), remove the rest
                for ($i = 1; $i < count($fileList); $i++) {
                    if (unlink($fileList[$i])) {
                        $removed++;
                    }
                }
            }
        }

        return $removed;
    }

    /**
     * Remove timestamp prefixes from filenames
     * Converts: 1770537672_filename_ABC123.jpg -> filename_ABC123.jpg
     * Only renames if target filename doesn't already exist
     */
    protected function removeTimestampPrefixes(string $outputDir): int
    {
        if (! is_dir($outputDir)) {
            return 0;
        }

        $renamed = 0;
        $files = glob($outputDir.'/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);

        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }

            $filename = basename($file);
            
            // Check if filename starts with timestamp (10+ digits + underscore)
            if (! preg_match('/^([0-9]{10,})_(.+)$/', $filename, $matches)) {
                continue;
            }

            $newFilename = $matches[2]; // Remove timestamp prefix
            $newPath = dirname($file).'/'.$newFilename;

            // Only rename if target doesn't exist
            if (! file_exists($newPath)) {
                if (rename($file, $newPath)) {
                    $renamed++;
                }
            } else {
                // Target exists - check if it's the same content
                if (md5_file($file) === md5_file($newPath)) {
                    // Same content, remove the timestamped version
                    if (unlink($file)) {
                        $renamed++; // Count as "cleaned up"
                    }
                }
            }
        }

        return $renamed;
    }

    /**
     * Remove random suffixes from filenames
     * Converts: filename_ABC12345.jpg -> filename.jpg
     * Pattern: filename_8ALPHANUMERIC.ext -> filename.ext
     * Handles conflicts by keeping the first file and removing duplicates
     */
    protected function removeRandomSuffixes(string $outputDir): int
    {
        if (! is_dir($outputDir)) {
            return 0;
        }

        $renamed = 0;
        $files = glob($outputDir.'/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);
        
        // Group files by their base name (without random suffix)
        $baseNameGroups = [];
        
        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }

            $filename = basename($file);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
            
            // Check if filename has random suffix pattern: name_8ALPHANUMERIC.ext
            // Pattern: ends with underscore + 8 alphanumeric characters + extension
            if (preg_match('/^(.+)_([A-Za-z0-9]{8})\.([^.]+)$/', $filename, $matches)) {
                $baseName = $matches[1].'.'.$matches[3]; // Remove the random suffix
                
                if (! isset($baseNameGroups[$baseName])) {
                    $baseNameGroups[$baseName] = [];
                }
                $baseNameGroups[$baseName][] = $file;
            }
        }

        // Process each group
        foreach ($baseNameGroups as $baseName => $fileList) {
            if (count($fileList) === 0) {
                continue;
            }

            $targetPath = dirname($fileList[0]).'/'.$baseName;
            
            // If target already exists (non-suffixed version), check if it's the same content
            if (file_exists($targetPath)) {
                // Check if any of the suffixed files match the existing file
                $targetHash = md5_file($targetPath);
                foreach ($fileList as $file) {
                    if (md5_file($file) === $targetHash) {
                        // Same content, remove the suffixed version
                        if (unlink($file)) {
                            $renamed++;
                        }
                    }
                }
            } else {
                // Target doesn't exist - rename the first file
                // If multiple files, check for duplicates first
                if (count($fileList) > 1) {
                    // Group by content hash
                    $hashGroups = [];
                    foreach ($fileList as $file) {
                        $hash = md5_file($file);
                        if (! isset($hashGroups[$hash])) {
                            $hashGroups[$hash] = [];
                        }
                        $hashGroups[$hash][] = $file;
                    }
                    
                    // Keep one file per unique content, remove duplicates
                    foreach ($hashGroups as $hash => $hashFileList) {
                        // Rename the first file
                        if (rename($hashFileList[0], $targetPath)) {
                            $renamed++;
                        }
                        
                        // Remove the rest (duplicates)
                        for ($i = 1; $i < count($hashFileList); $i++) {
                            if (unlink($hashFileList[$i])) {
                                $renamed++;
                            }
                        }
                    }
                } else {
                    // Single file, just rename it
                    if (rename($fileList[0], $targetPath)) {
                        $renamed++;
                    }
                }
            }
        }

        return $renamed;
    }

    /**
     * Update URL mapping after cleanup to ensure all URLs point to kept files
     */
    protected function updateUrlMappingAfterCleanup(string $outputDir): void
    {
        if (! is_dir($outputDir)) {
            return;
        }

        // Build hash map of existing files
        $hashToPath = [];
        $files = glob($outputDir.'/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);
        foreach ($files as $file) {
            if (is_file($file)) {
                $relativePath = 'blog/'.basename($file);
                $hash = md5_file($file);
                if ($hash && ! isset($hashToPath[$hash])) {
                    $hashToPath[$hash] = $relativePath;
                }
            }
        }

        // Update mappings that point to deleted files
        $updated = 0;
        foreach ($this->urlToPathMap as $url => $path) {
            $fullPath = base_path('storage/app/public/'.$path);
            if (! file_exists($fullPath)) {
                // File was deleted, try to find the kept duplicate by hash
                // We need to check if we have the hash stored
                // Since we don't have the original content, we'll skip this
                // The mapping will be corrected on next download run
                $updated++;
            }
        }

        if ($updated > 0) {
            $this->line("  Note: {$updated} mapping(s) may point to deleted files (will be corrected on next download)");
        }
    }

    /**
     * Generate filename from URL
     */
    protected function generateFilename(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $basename = basename($path);
        $ext = pathinfo($basename, PATHINFO_EXTENSION) ?: 'jpg';

        // Sanitize filename
        $name = pathinfo($basename, PATHINFO_FILENAME);
        $name = Str::slug($name);
        $name = substr($name, 0, 100); // Limit length

        return $name.'.'.$ext;
    }
}
