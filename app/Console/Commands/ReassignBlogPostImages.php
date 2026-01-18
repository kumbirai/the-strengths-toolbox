<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ReassignBlogPostImages extends Command
{
    protected $signature = 'blog:reassign-images {--force : Force reassignment even if image already exists}';

    protected $description = 'Reassign featured images to all blog posts by matching filenames';

    public function handle(): int
    {
        $force = $this->option('force');

        $this->info('Reassigning featured images to blog posts...');
        $this->newLine();

        $blogPosts = BlogPost::all();
        $assigned = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($blogPosts as $post) {
            if (! $force && $post->featured_image) {
                // Verify the file actually exists
                $filePath = storage_path('app/public/'.$post->featured_image);
                if (file_exists($filePath)) {
                    $skipped++;

                    continue;
                } else {
                    $this->warn("  ⊘ File missing for: {$post->title}");
                    // Continue to reassign
                }
            }

            // Try to find matching media by slug
            $slug = $post->slug;
            $searchTerms = [
                $slug,
                Str::slug($post->title),
                Str::slug(Str::words($post->title, 3, '')),
            ];

            $media = null;
            foreach ($searchTerms as $term) {
                // Try exact match first
                $media = Media::where('filename', 'like', "%blog-{$term}%")
                    ->orWhere('original_filename', 'like', "%blog-{$term}%")
                    ->orWhere('path', 'like', "%blog-{$term}%")
                    ->first();

                if ($media) {
                    break;
                }

                // Try partial match
                $media = Media::where(function ($query) use ($term) {
                    $query->where('filename', 'like', "%{$term}%")
                        ->orWhere('original_filename', 'like', "%{$term}%")
                        ->orWhere('path', 'like', "%{$term}%");
                })
                    ->where(function ($query) {
                        $query->where('filename', 'like', '%blog%')
                            ->orWhere('path', 'like', '%blog%');
                    })
                    ->first();

                if ($media) {
                    break;
                }
            }

            if ($media) {
                try {
                    // Verify file exists
                    $filePath = storage_path('app/public/'.$media->path);
                    if (! file_exists($filePath)) {
                        $this->warn("  ⊘ Media file not found: {$media->path}");
                        $skipped++;

                        continue;
                    }

                    $post->featured_image = $media->path;
                    $post->save();

                    $this->line("  ✓ Assigned: {$media->filename} → {$post->title}");
                    $assigned++;
                } catch (\Exception $e) {
                    $this->error("  ✗ Error assigning to {$post->title}: ".$e->getMessage());
                    $errors++;
                }
            } else {
                $this->warn("  ⊘ No matching media found for: {$post->title}");
                $skipped++;
            }
        }

        $this->newLine();
        $this->info('Reassignment Summary:');
        $this->line("  Assigned: {$assigned}");
        $this->line("  Skipped: {$skipped}");
        if ($errors > 0) {
            $this->error("  Errors: {$errors}");
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
