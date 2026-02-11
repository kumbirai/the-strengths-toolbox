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

        $blogPath = storage_path('app/public/blog');

        if (! is_dir($blogPath)) {
            $this->command->warn("Blog images directory not found: {$blogPath}");
            $this->command->warn('Skipping blog image upload. Run: php artisan blog:download-media');

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

        // Upload blog images from storage/app/public/blog
        $this->uploadBlogImages();

        $path = base_path('content-migration/images/optimized');

        if (! is_dir($path)) {
            $this->command->warn("Optimized images directory not found: {$path}");
            $this->command->warn('Skipping image upload. Run: php artisan images:upload-migrated');

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
        // Extract directory from path, removing strengthstoolbox/tsa prefixes
        // Example: content-migration/images/optimized/tsa/homepage/hero/image.webp
        // Should become: media/homepage/hero

        $basePath = base_path('content-migration/images/optimized/');
        $relativePath = str_replace($basePath, '', $imagePath);
        $directory = dirname($relativePath);

        // Handle root level images (no subdirectory)
        if ($directory === '.') {
            return 'media';
        }

        // Remove strengthstoolbox/ and tsa/ prefixes from the directory path
        $directory = preg_replace('#^(strengthstoolbox|tsa)/#', '', $directory);

        // If directory is now empty or just '.', return 'media'
        if ($directory === '' || $directory === '.') {
            return 'media';
        }

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
            if (isset($mapping['images'])) {
                foreach ($mapping['images'] as $path => $data) {
                    if (isset($data['new_filename']) && $data['new_filename'] === $filename) {
                        return $data['alt_text'] ?? null;
                    }
                }
            }
        }

        // Generate default alt text from filename
        $filename = basename($imagePath, '.webp');
        $filename = basename($filename, '.jpg');
        $filename = basename($filename, '.png');

        return Str::title(str_replace(['-', '_'], ' ', $filename));
    }

    protected function getDescription(string $imagePath): ?string
    {
        // Try to get description from image mapping file
        $mappingFile = base_path('content-migration/images/image-mapping.json');

        if (file_exists($mappingFile)) {
            $mapping = json_decode(file_get_contents($mappingFile), true);
            $filename = basename($imagePath);

            // Search mapping for this image
            if (isset($mapping['images'])) {
                foreach ($mapping['images'] as $path => $data) {
                    if (isset($data['new_filename']) && $data['new_filename'] === $filename) {
                        return $data['description'] ?? null;
                    }
                }
            }
        }

        return null;
    }

    protected function assignBlogPostImages(): void
    {
        $this->command->newLine();
        $this->command->info('Assigning featured images to blog posts...');

        $mappingPath = base_path('content-migration/images/image-mapping.json');

        if (! file_exists($mappingPath)) {
            $this->command->warn('Image mapping file not found. Skipping blog post image assignment.');

            return;
        }

        $mapping = json_decode(file_get_contents($mappingPath), true);

        if (! isset($mapping['images'])) {
            $this->command->warn('Invalid mapping file structure. Skipping blog post image assignment.');

            return;
        }

        $assigned = 0;
        $skipped = 0;
        $errors = 0;

        $blogPosts = BlogPost::all();

        foreach ($mapping['images'] as $originalPath => $imageData) {
            // Check if this image is for a blog post
            if (! isset($imageData['blog_post_slug'])) {
                continue;
            }

            $blogPostSlug = $imageData['blog_post_slug'];
            $newFilename = $imageData['new_filename'] ?? null;

            if (! $newFilename) {
                $skipped++;

                continue;
            }

            // Find blog post
            $blogPost = $blogPosts->firstWhere('slug', $blogPostSlug);

            if (! $blogPost) {
                $skipped++;

                continue;
            }

            // Find media by filename
            $media = Media::where('filename', 'like', '%'.pathinfo($newFilename, PATHINFO_FILENAME).'%')
                ->orWhere('original_filename', 'like', '%'.pathinfo($newFilename, PATHINFO_FILENAME).'%')
                ->first();

            if (! $media) {
                $skipped++;

                continue;
            }

            // Check if already assigned
            if ($blogPost->featured_image && $blogPost->featured_image === $media->path) {
                $skipped++;

                continue;
            }

            try {
                $blogPost->featured_image = $media->path;
                $blogPost->save();
                $assigned++;
                $this->command->line("  ✓ Assigned: {$media->filename} → {$blogPost->title}");
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("  ✗ Error assigning image to {$blogPost->title}: ".$e->getMessage());
            }
        }

        // Also try to match by filename patterns if no explicit mapping
        foreach ($blogPosts as $post) {
            if ($post->featured_image) {
                continue; // Already has image
            }

            // Try to find image by slug or title keywords
            $searchTerms = [
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
