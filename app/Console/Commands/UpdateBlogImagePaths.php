<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\BlogImageService;
use Illuminate\Console\Command;

/**
 * Update blog post featured_image paths from blog/ to images/blog/ format
 * 
 * This command:
 * - Finds all blog_posts with featured_image starting with blog/ (storage path)
 * - Converts to images/blog/ format
 * - Verifies file exists in new location
 * - Updates database records
 */
class UpdateBlogImagePaths extends Command
{
    protected $signature = 'blog:update-image-paths
                            {--dry-run : Preview changes without making them}';

    protected $description = 'Update blog post featured_image paths from blog/ to images/blog/ format';

    protected BlogImageService $blogImageService;

    protected int $updated = 0;
    protected int $skipped = 0;
    protected int $notFound = 0;
    protected array $errors = [];

    public function __construct(BlogImageService $blogImageService)
    {
        parent::__construct();
        $this->blogImageService = $blogImageService;
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('Updating blog post image paths...');
        $this->newLine();

        // Find posts with storage paths (blog/...) that are not already in images/blog/ format
        $posts = BlogPost::where('featured_image', 'like', 'blog/%')
            ->where('featured_image', 'not like', 'images/blog/%')
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No posts found with storage paths. All paths are already standardized.');
            return self::SUCCESS;
        }

        $this->info("Found {$posts->count()} post(s) to update");
        $this->newLine();

        $bar = $this->output->createProgressBar($posts->count());
        $bar->start();

        foreach ($posts as $post) {
            $oldPath = $post->featured_image;
            $filename = basename($oldPath);
            $newPath = 'images/blog/' . $filename;

            // Verify file exists in new location
            $fullPath = public_path($newPath);
            if (!file_exists($fullPath)) {
                $this->notFound++;
                $this->errors[] = [
                    'post' => $post,
                    'old_path' => $oldPath,
                    'new_path' => $newPath,
                    'reason' => 'File not found in public/images/blog/',
                ];
                $bar->advance();
                continue;
            }

            // Update the path
            if (!$dryRun) {
                try {
                    $post->featured_image = $newPath;
                    $post->save();
                    $this->updated++;
                } catch (\Exception $e) {
                    $this->errors[] = [
                        'post' => $post,
                        'old_path' => $oldPath,
                        'new_path' => $newPath,
                        'reason' => 'Database error: ' . $e->getMessage(),
                    ];
                }
            } else {
                $this->updated++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Update Summary:');
        $this->line("  Updated: {$this->updated}");
        $this->line("  Files not found: {$this->notFound}");

        if (!empty($this->errors)) {
            $this->newLine();
            $this->warn('Errors:');
            foreach ($this->errors as $error) {
                $this->line("  - {$error['post']->slug}: {$error['reason']}");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a dry run. Run without --dry-run to perform the updates.');
        }

        return self::SUCCESS;
    }
}
