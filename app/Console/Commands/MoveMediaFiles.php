<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MoveMediaFiles extends Command
{
    protected $signature = 'media:move-files 
                            {--dry-run : Preview changes without moving files}';

    protected $description = 'Move media files from strengthstoolbox/tsa subdirectories to unified media/ location';

    public function handle(): int
    {
        $this->info('Moving media files to unified location...');
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No files will be moved');
        }
        $this->newLine();

        // Use Laravel's storage path
        $basePath = Storage::disk('public')->path('media');
        $moved = 0;
        $skipped = 0;
        $errors = 0;
        $conflicts = 0;

        // Process strengthstoolbox directory
        $strengthstoolboxPath = $basePath.'/strengthstoolbox';
        if (is_dir($strengthstoolboxPath)) {
            $this->info('Processing strengthstoolbox/ directory...');
            $result = $this->moveFilesFromDirectory($strengthstoolboxPath, $basePath, 'strengthstoolbox');
            $moved += $result['moved'];
            $skipped += $result['skipped'];
            $errors += $result['errors'];
            $conflicts += $result['conflicts'];
        } else {
            $this->line("  ⊘ Directory not found: strengthstoolbox/");
        }

        // Process tsa directory
        $tsaPath = $basePath.'/tsa';
        if (is_dir($tsaPath)) {
            $this->info('Processing tsa/ directory...');
            $result = $this->moveFilesFromDirectory($tsaPath, $basePath, 'tsa');
            $moved += $result['moved'];
            $skipped += $result['skipped'];
            $errors += $result['errors'];
            $conflicts += $result['conflicts'];
        } else {
            $this->line("  ⊘ Directory not found: tsa/");
        }

        // Clean up empty directories
        if (! $this->option('dry-run')) {
            $this->cleanupEmptyDirectories($basePath);
        }

        $this->newLine();
        $this->info('Summary:');
        $this->line("  Files moved: {$moved}");
        $this->line("  Skipped: {$skipped}");
        if ($conflicts > 0) {
            $this->warn("  Conflicts: {$conflicts}");
        }
        if ($errors > 0) {
            $this->error("  Errors: {$errors}");
        }

        if ($moved > 0 && ! $this->option('dry-run')) {
            $this->newLine();
            $this->info('Next step: Run "php artisan media:update-paths" to update database paths.');
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    protected function moveFilesFromDirectory(string $sourceDir, string $targetDir, string $prefix): array
    {
        $moved = 0;
        $skipped = 0;
        $errors = 0;
        $conflicts = 0;

        if (! is_dir($sourceDir)) {
            return compact('moved', 'skipped', 'errors', 'conflicts');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $sourcePath = $file->getPathname();
            // Get relative path from the source directory (e.g., "homepage/hero/image.webp" or just "image.webp")
            $relativePath = str_replace($sourceDir.DIRECTORY_SEPARATOR, '', $sourcePath);
            $relativePath = str_replace('\\', '/', $relativePath); // Normalize path separators
            
            // The relativePath already has the prefix removed since we're starting from inside the prefix directory
            // So if sourceDir is "media/strengthstoolbox" and file is "media/strengthstoolbox/homepage/hero/image.webp"
            // relativePath will be "homepage/hero/image.webp"
            $targetRelativePath = $relativePath;
            $targetPath = $targetDir.'/'.$targetRelativePath;

            // Create target directory if needed
            $targetFileDir = dirname($targetPath);
            if (! is_dir($targetFileDir) && ! $this->option('dry-run')) {
                File::makeDirectory($targetFileDir, 0755, true);
            }

            // Check if target file already exists
            if (file_exists($targetPath)) {
                if (md5_file($sourcePath) === md5_file($targetPath)) {
                    // Same file, just delete source
                    if (! $this->option('dry-run')) {
                        File::delete($sourcePath);
                    }
                    $this->line("  ⊘ Duplicate (same content): {$relativePath}");
                    $skipped++;
                    continue;
                } else {
                    // Different file with same name - rename with prefix
                    $pathInfo = pathinfo($targetRelativePath);
                    $dirname = $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'].'/';
                    $newName = $dirname.$pathInfo['filename'].'-'.$prefix.'.'.$pathInfo['extension'];
                    $targetPath = $targetDir.'/'.$newName;
                    $this->warn("  ⚠ Conflict: {$relativePath} → {$newName}");
                    $conflicts++;
                }
            }

            // Move the file
            try {
                if (! $this->option('dry-run')) {
                    File::move($sourcePath, $targetPath);
                }
                $displayPath = $prefix.'/'.$relativePath;
                $this->line("  ✓ Moved: {$displayPath} → {$targetRelativePath}");
                $moved++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error moving {$relativePath}: ".$e->getMessage());
                $errors++;
            }
        }

        return compact('moved', 'skipped', 'errors', 'conflicts');
    }

    protected function cleanupEmptyDirectories(string $basePath): void
    {
        $this->info('Cleaning up empty directories...');

        $directoriesToCheck = [
            $basePath.'/strengthstoolbox',
            $basePath.'/tsa',
        ];

        foreach ($directoriesToCheck as $dir) {
            if (is_dir($dir)) {
                $this->removeEmptyDirectory($dir);
            }
        }
    }

    protected function removeEmptyDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        // Recursively remove subdirectories
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            if (is_dir($path)) {
                $this->removeEmptyDirectory($path);
            }
        }

        // Check if directory is now empty
        $files = array_diff(scandir($dir), ['.', '..']);
        if (empty($files)) {
            rmdir($dir);
            $relativePath = str_replace(Storage::disk('public')->path(''), '', $dir);
            $this->line("  ✓ Removed empty directory: {$relativePath}");
        }
    }
}
