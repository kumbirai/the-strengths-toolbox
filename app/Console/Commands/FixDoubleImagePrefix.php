<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixDoubleImagePrefix extends Command
{
    protected $signature = 'blog:fix-double-prefix 
                            {--dry-run : Show what would be fixed without making changes}
                            {--all : Check all posts, not just those matching the pattern}';

    protected $description = 'Fix blog posts with double images/images prefix in featured_image paths';

    protected int $fixed = 0;
    protected int $notFixed = 0;

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info('Fixing blog posts with double images/images prefix...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        $this->newLine();

        try {
            // Always check all posts with featured images
            $allPosts = BlogPost::whereNotNull('featured_image')
                ->where('featured_image', '!=', '')
                ->get();
            
            $this->info("Checking {$allPosts->count()} post(s) with featured images");
            
            // Filter posts with double prefix - check for various patterns
            $posts = collect();
            foreach ($allPosts as $post) {
                $path = $post->featured_image ?? '';
                // Check for double prefix in various forms
                if (str_contains($path, 'images/images/')) {
                    $posts->push($post);
                }
            }
            
            $this->info("Found {$posts->count()} post(s) with double prefix");
            $this->newLine();
            
            // Debug: Check specific post mentioned by user
            $testPost = $allPosts->firstWhere('slug', 'lessons-from-everyday-life-take-control-of-your-life');
            if ($testPost) {
                $this->line("Debug - Test post found:");
                $this->line("  Slug: {$testPost->slug}");
                $this->line("  Featured Image: {$testPost->featured_image}");
                $this->line("  Contains 'images/images/': " . (str_contains($testPost->featured_image, 'images/images/') ? 'YES' : 'NO'));
                $this->newLine();
            }
            
            // Debug: Show first few posts with featured images if none found
            if ($posts->count() === 0) {
                $this->warn('No posts found with double prefix. Showing sample of featured_image paths:');
                $this->newLine();
                foreach ($allPosts->take(10) as $post) {
                    $hasDouble = str_contains($post->featured_image, 'images/images/') ? ' [DOUBLE PREFIX]' : '';
                    $this->line("  {$post->slug}: {$post->featured_image}{$hasDouble}");
                }
                if ($allPosts->count() > 10) {
                    $this->line("  ... and " . ($allPosts->count() - 10) . " more");
                }
                $this->newLine();
            }
        } catch (\Exception $e) {
            $this->error('Failed to connect to database: ' . $e->getMessage());
            return Command::FAILURE;
        }

        if ($posts->count() === 0) {
            $this->info('✓ No posts with double prefix found');
            return Command::SUCCESS;
        }

        $tableData = [];
        foreach ($posts as $post) {
            $oldPath = $post->featured_image;
            $newPath = $this->fixPath($oldPath);

            $tableData[] = [
                $post->slug,
                Str::limit($post->title, 40),
                Str::limit($oldPath, 50),
                Str::limit($newPath, 50),
            ];

            if ($newPath && $newPath !== $oldPath) {
                // Verify the fixed path is valid
                if ($this->isPathValid($newPath)) {
                    if (!$dryRun) {
                        $post->featured_image = $newPath;
                        $post->save();
                    }
                    $this->fixed++;
                } else {
                    $this->notFixed++;
                }
            } else {
                $this->notFixed++;
            }
        }

        $this->table(
            ['Slug', 'Title', 'Old Path', 'New Path'],
            $tableData
        );

        $this->newLine();
        $this->info('Summary:');
        if ($this->fixed > 0) {
            $this->info("  ✓ Fixed: {$this->fixed}");
        }
        if ($this->notFixed > 0) {
            $this->warn("  ✗ Could not fix: {$this->notFixed}");
        }

        return Command::SUCCESS;
    }

    protected function fixPath(string $path): ?string
    {
        // Remove the double prefix: images/images/blog/... -> images/blog/...
        // Handle various patterns
        if (str_starts_with($path, 'images/images/')) {
            return substr($path, 7); // Remove 'images/'
        }

        // Handle cases where it appears anywhere in the path
        if (str_contains($path, 'images/images/')) {
            return str_replace('images/images/', 'images/', $path);
        }

        // Regex pattern to catch any variation
        if (preg_match('#images/images/(.+)$#', $path, $matches)) {
            return 'images/' . $matches[1];
        }

        return $path;
    }

    protected function isPathValid(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Check if path starts with images/blog/ (public folder)
        if (str_starts_with($path, 'images/blog/')) {
            return file_exists(public_path($path));
        }

        // Check if it's a storage path
        if (str_starts_with($path, 'blog/')) {
            return file_exists(storage_path('app/public/'.$path));
        }

        return false;
    }
}
