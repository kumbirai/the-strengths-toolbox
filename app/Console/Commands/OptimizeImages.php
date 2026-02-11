<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Optimize images for production
 */
class OptimizeImages extends Command
{
    protected $signature = 'images:optimize 
                            {--path=content-migration/images/original : Path to images}
                            {--output=content-migration/images/optimized : Output directory for optimized images}
                            {--quality=85 : JPEG quality (1-100)}
                            {--format=webp : Output format (webp, jpg, png)}
                            {--max-width=1920 : Maximum width for images (800 for blog featured)}';

    protected $description = 'Optimize images for production (convert to WebP, compress, resize)';

    protected ?ImageManager $imageManager = null;

    protected function getImageManager(): ImageManager
    {
        if ($this->imageManager === null) {
            $this->imageManager = new ImageManager(new Driver);
        }

        return $this->imageManager;
    }

    public function handle(): int
    {
        $path = $this->option('path');
        $outputDir = $this->option('output');
        $quality = (int) $this->option('quality');
        $format = $this->option('format');
        $maxWidth = (int) $this->option('max-width');

        // Convert relative paths to absolute
        $fullPath = str_starts_with($path, '/') ? $path : base_path($path);
        $fullOutputDir = str_starts_with($outputDir, '/') ? $outputDir : base_path($outputDir);

        $this->info("Optimizing images in: {$fullPath}");
        $this->info("Output directory: {$fullOutputDir}");
        $this->info("Format: {$format}, Quality: {$quality}, Max Width: {$maxWidth}px");
        $this->newLine();

        if (! is_dir($fullPath)) {
            $this->error("Path does not exist: {$fullPath}");

            return Command::FAILURE;
        }

        // Create output directory if it doesn't exist
        if (! is_dir($fullOutputDir)) {
            mkdir($fullOutputDir, 0755, true);
        }

        $images = $this->findImages($fullPath);

        if (empty($images)) {
            $this->warn('No images found to optimize.');

            return Command::SUCCESS;
        }

        $this->info('Found '.count($images).' images to optimize.');
        $this->newLine();

        $bar = $this->output->createProgressBar(count($images));
        $bar->start();

        $optimized = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($images as $imagePath) {
            try {
                $result = $this->optimizeImage($imagePath, $fullPath, $fullOutputDir, $format, $quality, $maxWidth);
                if ($result['success']) {
                    $optimized++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error optimizing {$imagePath}: ".$e->getMessage());
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Optimization complete!');
        $this->line("  Optimized: {$optimized}");
        $this->line("  Skipped: {$skipped}");
        if ($errors > 0) {
            $this->error("  Errors: {$errors}");
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    protected function findImages(string $path): array
    {
        $images = [];
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, $extensions)) {
                    $images[] = $file->getPathname();
                }
            }
        }

        return $images;
    }

    protected function optimizeImage(string $path, string $sourceDir, string $outputDir, string $format, int $quality, int $maxWidth): array
    {
        $info = pathinfo($path);
        $currentFormat = strtolower($info['extension']);

        // Skip if already in target format and exists in output
        if ($format === 'webp' && $currentFormat === 'webp') {
            return ['success' => false, 'reason' => 'Already WebP'];
        }

        try {
            $image = $this->getImageManager()->read($path);

            // Determine max width based on image context
            $imageMaxWidth = $this->determineMaxWidth($path, $maxWidth);

            // Resize if too large
            if ($image->width() > $imageMaxWidth) {
                $image->scale(width: $imageMaxWidth);
            }

            // Preserve directory structure in output
            $relativePath = str_replace($sourceDir.'/', '', $path);
            $relativeDir = dirname($relativePath);

            // Create subdirectories in output if needed
            if ($relativeDir !== '.' && $relativeDir !== '') {
                $outputSubDir = $outputDir.'/'.$relativeDir;
                if (! is_dir($outputSubDir)) {
                    mkdir($outputSubDir, 0755, true);
                }
            } else {
                $outputSubDir = $outputDir;
            }

            // Generate output filename (change extension to target format)
            $outputFilename = $info['filename'].'.'.$format;
            $outputPath = $outputSubDir.'/'.$outputFilename;

            // Skip if already exists
            if (file_exists($outputPath)) {
                return ['success' => false, 'reason' => 'Already exists'];
            }

            // Save optimized image
            if ($format === 'webp') {
                $image->toWebp($quality)->save($outputPath);
            } elseif ($format === 'jpg' || $format === 'jpeg') {
                $image->toJpeg($quality)->save($outputPath);
            } else {
                $image->save($outputPath, quality: $quality);
            }

            return ['success' => true, 'path' => $outputPath];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function determineMaxWidth(string $path, int $defaultMaxWidth): int
    {
        // Determine max width based on image context/path
        $pathLower = strtolower($path);

        // Blog featured images: 800px
        if (str_contains($pathLower, 'blog') || str_contains($pathLower, 'featured')) {
            return 800;
        }

        // About page images: 1200px
        if (str_contains($pathLower, 'about') || str_contains($pathLower, 'eberhard')) {
            return 1200;
        }

        // Homepage hero: 1920px
        if (str_contains($pathLower, 'homepage') || str_contains($pathLower, 'hero')) {
            return 1920;
        }

        // Default: use provided max width
        return $defaultMaxWidth;
    }
}
