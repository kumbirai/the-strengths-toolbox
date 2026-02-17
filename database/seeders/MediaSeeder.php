<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Seed media library and image storage
 * 
 * Ensures storage link exists, downloads images, uploads to media library,
 * and assigns featured images to blog posts.
 */
class MediaSeeder extends Seeder
{
    protected MediaService $mediaService;

    protected ?int $userId = null;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function run(): void
    {
        $this->command->info('Seeding media library and images...');
        $this->command->newLine();

        // Ensure storage symlink exists
        $this->ensureStorageLink();

        // Download Sales Courses and blog images
        $this->downloadImages();

        // Upload images from optimized directory to media library
        $this->uploadOptimizedImages();

        // Assign blog post featured images
        $this->assignBlogPostImages();

        $this->command->newLine();
        $this->command->info('✓ Media seeding completed successfully!');
    }

    protected function ensureStorageLink(): void
    {
        $this->command->info('Ensuring storage link...');

        $link = public_path('storage');
        if (! File::exists($link)) {
            Artisan::call('storage:link');
            $this->command->line('  ✓ Storage link created');
        } else {
            $this->command->line('  ✓ Storage link already exists');
        }
    }

    protected function downloadImages(): void
    {
        $this->command->info('Downloading images...');

        // Download Sales Courses images (referenced in page content as /storage/sales-courses/...)
        $exitCode = Artisan::call('content:download-sales-courses-images');
        if ($exitCode === 0) {
            $this->command->line('  ✓ Sales Courses images downloaded');
        } else {
            $this->command->warn('  ⊘ Sales Courses image download had issues (check network)');
        }

        // Download blog media (featured and inline images from scraped blogs)
        $exitCode = Artisan::call('blog:download-media');
        if ($exitCode === 0) {
            $this->command->line('  ✓ Blog media downloaded');
        } else {
            $this->command->warn('  ⊘ Blog media download had issues (check network or scraped-blogs.json)');
        }
    }

    protected function uploadBlogImages(): void
    {
        $this->command->info('Uploading blog images to media library...');
        $this->command->newLine();

        $blogPath = public_path('images/blog');

        if (! is_dir($blogPath)) {
            $this->command->warn("Blog images directory not found: {$blogPath}");
            $this->command->warn('Skipping blog image upload. Images should be in public/images/blog/');

            return;
        }

        $images = $this->findImages($blogPath);
        $uploaded = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($images as $imagePath) {
            try {
                $filename = basename($imagePath);
                $existing = Media::where('filename', $filename)
                    ->orWhere('original_filename', $filename)
                    ->first();

                if ($existing) {
                    $skipped++;
                    $this->command->line("  ⊘ Skipped (already exists): {$filename}");
                    continue;
                }

                $media = $this->uploadImage($imagePath);
                $uploaded++;
                $this->command->line("  ✓ Uploaded: {$media->filename}");
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("  ✗ Error uploading {$imagePath}: ".$e->getMessage());
            }
        }

        $this->command->newLine();
        $this->command->info('Blog Image Upload Summary:');
        $this->command->line("  Uploaded: {$uploaded}");
        $this->command->line("  Skipped: {$skipped}");
        if ($errors > 0) {
            $this->command->error("  Errors: {$errors}");
        }
        $this->command->newLine();
    }

    protected function uploadOptimizedImages(): void
    {
        $this->command->info('Uploading optimized images to media library...');
        $this->command->newLine();

        // Get or create a default user for uploaded_by
        $user = User::first();
        $this->userId = $user ? $user->id : null;

        // Upload blog images from public/images/blog
        $this->uploadBlogImages();

        $path = public_path('images/blog');

        if (! is_dir($path)) {
            $this->command->warn("Blog images directory not found: {$path}");
            $this->command->warn('Skipping image upload. Images should be in public/images/blog/');

            return;
        }

        $images = $this->findImages($path);
        $uploaded = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($images as $imagePath) {
            try {
                // Check if already uploaded (by filename pattern)
                $filename = basename($imagePath);
                $existing = Media::where('filename', $filename)
                    ->orWhere('original_filename', $filename)
                    ->first();

                if ($existing) {
                    $skipped++;
                    $this->command->line("  ⊘ Skipped (already exists): {$filename}");

                    continue;
                }

                $media = $this->uploadImage($imagePath);
                $uploaded++;
                $this->command->line("  ✓ Uploaded: {$media->filename}");
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("  ✗ Error uploading {$imagePath}: ".$e->getMessage());
            }
        }

        $this->command->newLine();
        $this->command->info('Image Upload Summary:');
        $this->command->line("  Uploaded: {$uploaded}");
        $this->command->line("  Skipped: {$skipped}");
        if ($errors > 0) {
            $this->command->error("  Errors: {$errors}");
        }
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
        $description = $this->getDescription($imagePath);

        // Upload using MediaService
        $media = $this->mediaService->upload($uploadedFile, [
            'directory' => $directory,
            'alt_text' => $altText,
            'description' => $description,
        ]);

        // Set uploaded_by manually since we're in a seeder context
        if ($this->userId) {
            $media->uploaded_by = $this->userId;
            $media->save();
        }

        // Clean up temp file
        unlink($tempFile);

        return $media;
    }

    protected function determineDirectory(string $imagePath): string
    {
        // Extract directory from path
        // Example: public/images/blog/image.webp -> media/blog
        // Example: public/images/blog/subfolder/image.webp -> media/blog/subfolder

        $basePath = public_path('images/blog');
        if (str_starts_with($imagePath, $basePath)) {
            $relativePath = str_replace($basePath, '', $imagePath);
            $directory = dirname($relativePath);
            
            // Handle root level images (no subdirectory)
            if ($directory === '.' || $directory === '') {
                return 'media/blog';
            }
            
            // Remove leading slash
            $directory = ltrim($directory, '/');
            
            return 'media/blog/'.$directory;
        }

        // Fallback for other paths
        return 'media';
    }

    protected function getAltText(string $imagePath): ?string
    {
        // Generate default alt text from filename
        // In production, alt text can be enhanced from metadata if needed
        $filename = basename($imagePath, '.webp');
        $filename = basename($filename, '.jpg');
        $filename = basename($filename, '.png');
        $filename = basename($filename, '.gif');

        return Str::title(str_replace(['-', '_'], ' ', $filename));
    }

    protected function getDescription(string $imagePath): ?string
    {
        // In production, description can be loaded from metadata if available
        return null;
    }

    protected function assignBlogPostImages(): void
    {
        $this->command->newLine();
        $this->command->info('Assigning featured images to blog posts...');

        // In production, blog post images are primarily assigned via BlogSeeder
        // This method attempts to match remaining images by filename patterns
        $this->command->line('  Note: Blog post images are primarily assigned via BlogSeeder');

        $assigned = 0;
        $skipped = 0;
        $errors = 0;

        $blogPosts = BlogPost::all();

        // Try to match by filename patterns
        foreach ($blogPosts as $post) {
            if ($post->featured_image) {
                continue; // Already has image
            }

            // Try to find image by slug or title keywords
            $searchTerms = [
                Str::slug($post->slug),
                Str::slug($post->title),
                Str::slug(Str::words($post->title, 3, '')),
            ];

            foreach ($searchTerms as $term) {
                $media = Media::where(function ($query) use ($term) {
                    $query->where('filename', 'like', "%{$term}%")
                        ->orWhere('original_filename', 'like', "%{$term}%");
                })->first();

                if ($media) {
                    try {
                        $post->featured_image = $media->path;
                        $post->save();
                        $assigned++;
                        $this->command->line("  ✓ Auto-assigned: {$media->filename} → {$post->title}");
                        break;
                    } catch (\Exception $e) {
                        $errors++;
                    }
                }
            }
        }

        $this->command->newLine();
        $this->command->info('Blog Post Image Assignment Summary:');
        $this->command->line("  Assigned: {$assigned}");
        $this->command->line("  Skipped: {$skipped}");
        if ($errors > 0) {
            $this->command->error("  Errors: {$errors}");
        }
    }
}
