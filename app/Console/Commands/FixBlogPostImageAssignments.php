<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Media;
use Illuminate\Console\Command;

class FixBlogPostImageAssignments extends Command
{
    protected $signature = 'blog:fix-image-assignments';

    protected $description = 'Fix blog post image assignments by matching to correct media files';

    public function handle(): int
    {
        $this->info('Fixing blog post image assignments...');
        $this->newLine();

        // Load the mapping
        $mappingFile = base_path('content-migration/images/blog-post-image-mapping.json');
        if (! file_exists($mappingFile)) {
            $this->error('Mapping file not found. Run blog:map-images first.');

            return Command::FAILURE;
        }

        $mapping = json_decode(file_get_contents($mappingFile), true);
        $this->info('Loaded '.count($mapping).' image mappings');
        $this->newLine();

        $fixed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($mapping as $slug => $data) {
            $post = BlogPost::where('slug', $slug)->first();
            if (! $post) {
                $this->warn("  ⊘ Post not found: {$slug}");
                $skipped++;

                continue;
            }

            // Extract the expected image filename from URL
            $imageUrl = $data['url'];
            $expectedFilename = basename(parse_url($imageUrl, PHP_URL_PATH));

            // Remove extension and try to find matching media
            $baseName = pathinfo($expectedFilename, PATHINFO_FILENAME);

            // Try multiple search patterns
            $patterns = [
                $baseName,
                str_replace(['_', '-'], ['%', '%'], $baseName),
                "blog-{$slug}",
            ];

            $media = null;
            foreach ($patterns as $pattern) {
                $media = Media::where('filename', 'like', "%{$pattern}%")
                    ->orWhere('original_filename', 'like', "%{$pattern}%")
                    ->first();

                if ($media) {
                    break;
                }
            }

            if (! $media) {
                // Try to find by slug in filename
                $slugParts = explode('-', $slug);
                $shortSlug = implode('-', array_slice($slugParts, 0, 3));
                $media = Media::where('filename', 'like', "%blog-{$shortSlug}%")
                    ->orWhere('original_filename', 'like', "%blog-{$shortSlug}%")
                    ->first();
            }

            if (! $media) {
                $this->warn("  ⊘ No media found for: {$post->title}");
                $errors++;

                continue;
            }

            // Check if file exists
            $mediaPath = storage_path('app/public/'.$media->path);
            if (! file_exists($mediaPath)) {
                $this->warn("  ⊘ Media file not found: {$media->path}");
                $errors++;

                continue;
            }

            // Update blog post
            $oldImage = $post->featured_image;
            $post->featured_image = $media->path;
            $post->save();

            if ($oldImage !== $post->featured_image) {
                $this->line("  ✓ Fixed: {$post->title}");
                $this->line('    Old: '.basename($oldImage ?: 'NULL'));
                $this->line('    New: '.basename($media->path));
                $fixed++;
            } else {
                $this->line("  ⊘ Already correct: {$post->title}");
                $skipped++;
            }
        }

        $this->newLine();
        $this->info('Summary:');
        $this->line("  Fixed: {$fixed}");
        $this->line("  Skipped: {$skipped}");
        $this->line("  Errors: {$errors}");

        return Command::SUCCESS;
    }
}
