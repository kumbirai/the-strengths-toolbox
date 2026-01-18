<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AssignBlogPostImages extends Command
{
    protected $signature = 'blog:assign-featured-images 
                            {--mapping=content-migration/images/image-mapping.json : Path to image mapping file}
                            {--dry-run : Preview without making changes}';

    protected $description = 'Assign featured images to blog posts based on image mapping';

    public function handle(): int
    {
        $mappingPath = $this->option('mapping');
        $dryRun = $this->option('dry-run');

        $fullPath = str_starts_with($mappingPath, '/') ? $mappingPath : base_path($mappingPath);

        if (! file_exists($fullPath)) {
            $this->error("Image mapping file not found: {$fullPath}");

            return Command::FAILURE;
        }

        $mapping = json_decode(file_get_contents($fullPath), true);

        if (! isset($mapping['images'])) {
            $this->error("Invalid mapping file structure. Expected 'images' key.");

            return Command::FAILURE;
        }

        $this->info('Assigning featured images to blog posts...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        $this->newLine();

        $assigned = 0;
        $skipped = 0;
        $errors = 0;

        // Get all blog posts
        $blogPosts = BlogPost::all();

        foreach ($mapping['images'] as $originalPath => $imageData) {
            // Check if this image is for a blog post
            if (! isset($imageData['blog_post_slug'])) {
                continue;
            }

            $blogPostSlug = $imageData['blog_post_slug'];
            $newFilename = $imageData['new_filename'] ?? null;

            if (! $newFilename) {
                $this->warn("  ⊘ Skipping {$originalPath}: No new_filename specified");
                $skipped++;

                continue;
            }

            // Find blog post
            $blogPost = $blogPosts->firstWhere('slug', $blogPostSlug);

            if (! $blogPost) {
                $this->warn("  ⊘ Blog post not found: {$blogPostSlug}");
                $skipped++;

                continue;
            }

            // Find media by filename
            $media = Media::where('filename', 'like', '%'.pathinfo($newFilename, PATHINFO_FILENAME).'%')
                ->orWhere('original_filename', 'like', '%'.pathinfo($newFilename, PATHINFO_FILENAME).'%')
                ->first();

            if (! $media) {
                $this->warn("  ⊘ Media not found for: {$newFilename} (blog post: {$blogPostSlug})");
                $skipped++;

                continue;
            }

            // Check if already assigned
            if ($blogPost->featured_image && $blogPost->featured_image === $media->path) {
                $this->line("  ⊘ Already assigned: {$blogPost->title}");
                $skipped++;

                continue;
            }

            if ($dryRun) {
                $this->line("  Would assign: {$media->filename} → {$blogPost->title}");
            } else {
                try {
                    $blogPost->featured_image = $media->path;
                    $blogPost->save();

                    $this->line("  ✓ Assigned: {$media->filename} → {$blogPost->title}");
                    $assigned++;
                } catch (\Exception $e) {
                    $this->error("  ✗ Error assigning image to {$blogPost->title}: ".$e->getMessage());
                    $errors++;
                }
            }
        }

        // Also try to match by filename patterns if no explicit mapping
        $this->newLine();
        $this->info('Attempting automatic matching for unmapped blog posts...');

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
                    if ($dryRun) {
                        $this->line("  Would auto-assign: {$media->filename} → {$post->title}");
                    } else {
                        try {
                            $post->featured_image = $media->path;
                            $post->save();
                            $this->line("  ✓ Auto-assigned: {$media->filename} → {$post->title}");
                            $assigned++;
                            break;
                        } catch (\Exception $e) {
                            $this->error("  ✗ Error auto-assigning to {$post->title}: ".$e->getMessage());
                            $errors++;
                        }
                    }
                }
            }
        }

        $this->newLine();
        $this->info('Assignment Summary:');
        $this->line("  Assigned: {$assigned}");
        $this->line("  Skipped: {$skipped}");
        if ($errors > 0) {
            $this->error("  Errors: {$errors}");
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
