<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class UploadMigratedImages extends Command
{
    protected $signature = 'images:upload-migrated 
                            {--path=content-migration/images/optimized : Path to optimized images}
                            {--dry-run : Preview without uploading}';

    protected $description = 'Upload migrated images to media library';

    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        parent::__construct();
        $this->mediaService = $mediaService;
    }

    public function handle(): int
    {
        $path = $this->option('path');
        $dryRun = $this->option('dry-run');

        $fullPath = base_path($path);

        if (! is_dir($fullPath)) {
            $this->error("Path not found: {$fullPath}");

            return Command::FAILURE;
        }

        $this->info('Uploading migrated images...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be uploaded');
        }
        $this->newLine();

        $images = $this->findImages($fullPath);
        $uploaded = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($images as $imagePath) {
            try {
                if ($dryRun) {
                    $this->line('Would upload: '.str_replace($fullPath.'/', '', $imagePath));

                    continue;
                }

                // Check if already uploaded
                $filename = basename($imagePath);
                $existing = Media::where('filename', $filename)->first();

                if ($existing) {
                    $skipped++;
                    $this->line("  ⊘ Skipped (already exists): {$filename}");

                    continue;
                }

                $media = $this->uploadImage($imagePath);
                $uploaded++;
                $this->line("  ✓ Uploaded: {$media->filename}");
            } catch (\Exception $e) {
                $errors++;
                $this->error("  ✗ Error uploading {$imagePath}: ".$e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Uploaded: {$uploaded}, Skipped: {$skipped}, Errors: {$errors}");

        return Command::SUCCESS;
    }

    protected function findImages(string $path): array
    {
        $images = [];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            if ($file->isFile() && in_array(strtolower($file->getExtension()), ['webp', 'jpg', 'jpeg', 'png', 'gif'])) {
                $images[] = $file->getPathname();
            }
        }

        return $images;
    }

    protected function uploadImage(string $imagePath): Media
    {
        $filename = basename($imagePath);
        $mimeType = mime_content_type($imagePath);

        // Determine directory based on image path
        $directory = $this->determineDirectory($imagePath);

        // Read file content
        $fileContent = file_get_contents($imagePath);

        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
        file_put_contents($tempFile, $fileContent);

        // Create UploadedFile
        $uploadedFile = new UploadedFile(
            $tempFile,
            $filename,
            $mimeType,
            null,
            true
        );

        // Extract alt text from image mapping if available
        $altText = $this->getAltText($imagePath);

        // Upload using MediaService
        $media = $this->mediaService->upload($uploadedFile, [
            'directory' => $directory,
            'alt_text' => $altText,
        ]);

        // Clean up temp file
        unlink($tempFile);

        return $media;
    }

    protected function determineDirectory(string $imagePath): string
    {
        // Extract directory from path
        // Example: content-migration/images/optimized/tsa/homepage/hero/image.webp
        // Should become: media/tsa/homepage/hero

        $basePath = base_path('content-migration/images/optimized/');
        $relativePath = str_replace($basePath, '', $imagePath);
        $directory = dirname($relativePath);

        return 'media/'.$directory;
    }

    protected function getAltText(string $imagePath): ?string
    {
        // Try to get alt text from image mapping file
        $mappingFile = base_path('content-migration/images/image-mapping.json');

        if (file_exists($mappingFile)) {
            $mapping = json_decode(file_get_contents($mappingFile), true);
            $filename = basename($imagePath);

            // Search mapping for this image
            foreach ($mapping as $path => $data) {
                if (isset($data['new_filename']) && $data['new_filename'] === $filename) {
                    return $data['alt_text'] ?? null;
                }
            }
        }

        // Generate default alt text from filename
        $filename = basename($imagePath, '.webp');
        $filename = basename($filename, '.jpg');
        $filename = basename($filename, '.png');

        return Str::title(str_replace(['-', '_'], ' ', $filename));
    }
}
