<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\BlogImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixBlogPostImages extends Command
{
    protected $signature = 'blog:fix-images 
                            {--dry-run : Show what would be fixed without making changes}
                            {--slug= : Fix specific post by slug}';

    protected $description = 'Fix blog post featured images by resolving correct paths';

    protected int $fixed = 0;
    protected int $notFixed = 0;
    protected int $alreadyCorrect = 0;
    protected array $failedPosts = [];

    protected BlogImageService $blogImageService;

    public function __construct(BlogImageService $blogImageService)
    {
        parent::__construct();
        $this->blogImageService = $blogImageService;
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $slugFilter = $this->option('slug');

        $this->info('Fixing blog post featured images...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        $this->newLine();

        try {
            $query = BlogPost::where('is_published', true);
            if ($slugFilter) {
                $query->where('slug', $slugFilter);
            }
            $posts = $query->get();
        } catch (\Exception $e) {
            $this->error('Failed to connect to database: ' . $e->getMessage());
            $this->warn('Please ensure the database is running and configured correctly.');
            return Command::FAILURE;
        }

        $this->info("Found {$posts->count()} published blog post(s) to check");
        $this->newLine();

        $bar = $this->output->createProgressBar($posts->count());
        $bar->start();

        foreach ($posts as $post) {
            $result = $this->fixPostImage($post, $dryRun);
            if ($result === 'fixed') {
                $this->fixed++;
            } elseif ($result === 'not_fixed') {
                $this->notFixed++;
                $this->failedPosts[] = [
                    'post' => $post,
                    'reason' => $this->getFailureReason($post),
                ];
            } else {
                $this->alreadyCorrect++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Summary:');
        $this->line("  ✓ Already correct: {$this->alreadyCorrect}");
        if ($this->fixed > 0) {
            $this->info("  ✓ Fixed: {$this->fixed}");
        }
        if ($this->notFixed > 0) {
            $this->warn("  ✗ Could not fix: {$this->notFixed}");
            $this->newLine();
            $this->displayFailedPosts();
        }

        return Command::SUCCESS;
    }

    protected function getFailureReason(BlogPost $post): string
    {
        $slug = $post->slug;
        $currentPath = $post->featured_image;

        // Check if any files exist that might match
        $publicFiles = [];
        $storageFiles = [];
        
        $publicBlogPath = public_path('images/blog');
        
        if (is_dir($publicBlogPath)) {
            $allFiles = glob($publicBlogPath.'/*') ?: [];
        } else {
            $allFiles = [];
        }

        // Try to find partial matches
        $slugParts = explode('-', $slug);
        $matches = [];
        foreach ($allFiles as $file) {
            $filename = basename($file);
            $filenameLower = strtolower($filename);
            $slugLower = strtolower($slug);

            // Check if filename contains slug or significant parts of it
            if (str_contains($filenameLower, $slugLower)) {
                $matches[] = $filename;
            } elseif (count($slugParts) > 2) {
                // Try with first few parts of slug
                $partialSlug = implode('-', array_slice($slugParts, 0, 3));
                if (str_contains($filenameLower, strtolower($partialSlug))) {
                    $matches[] = $filename;
                }
            }
        }

        if (empty($matches)) {
            return "No image files found matching slug '{$slug}' in public/images/blog/";
        }

        return "Found potential matches but couldn't resolve: " . implode(', ', array_slice($matches, 0, 3));
    }

    protected function displayFailedPosts(): void
    {
        $this->warn('Posts that could not be fixed:');
        $this->newLine();

        $tableData = [];
        foreach ($this->failedPosts as $failed) {
            $post = $failed['post'];
            $tableData[] = [
                $post->slug,
                Str::limit($post->title, 50),
                $post->featured_image ?: '(empty)',
                Str::limit($failed['reason'], 80),
            ];
        }

        $this->table(
            ['Slug', 'Title', 'Current Image', 'Reason'],
            $tableData
        );
    }

    protected function fixPostImage(BlogPost $post, bool $dryRun): string
    {
        // Check if current image path is valid
        if ($this->isImagePathValid($post->featured_image)) {
            return 'correct';
        }

        // Try to find correct image
        $newPath = $this->findImageForPost($post);

        if ($newPath && $newPath !== $post->featured_image) {
            if (!$dryRun) {
                $post->featured_image = $newPath;
                $post->save();
            }
            return 'fixed';
        }

        return 'not_fixed';
    }

    protected function isImagePathValid(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Normalize path to images/blog/ format (this also removes double prefixes)
        $normalizedPath = $this->blogImageService->getStandardPath($path);
        
        // Check public folder
        if (str_starts_with($normalizedPath, 'images/blog/')) {
            return file_exists(public_path($normalizedPath));
        }
        
        // Check storage folder
        if (str_starts_with($normalizedPath, 'blog/')) {
            return file_exists(storage_path('app/public/'.$normalizedPath));
        }
        
        return false;
    }

    protected function findImageForPost(BlogPost $post): ?string
    {
        $slug = $post->slug;

        // Strategy 1: Try to find by slug
        $imagePath = $this->blogImageService->findBySlug($slug);
        if ($imagePath) {
            return $imagePath;
        }

        // Strategy 2: Try partial slug matching (for long slugs)
        $slugParts = explode('-', $slug);
        if (count($slugParts) > 3) {
            // Try with first 3-4 parts
            for ($i = 3; $i <= min(5, count($slugParts)); $i++) {
                $partialSlug = implode('-', array_slice($slugParts, 0, $i));
                $imagePath = $this->blogImageService->findBySlug($partialSlug);
                if ($imagePath) {
                    return $imagePath;
                }
            }
        }

        // Strategy 3: Try to extract from current path if it exists but is wrong
        if ($post->featured_image) {
            $filename = basename($post->featured_image);
            $imagePath = $this->blogImageService->findByFilename($filename);
            if ($imagePath) {
                return $imagePath;
            }
        }

        // Strategy 4: Try fuzzy matching - look for files containing key words from slug
        $imagePath = $this->blogImageService->findByFuzzyMatch($slug);
        if ($imagePath) {
            return $imagePath;
        }

        // Strategy 5: Try to resolve from seeder inventory if available
        $imagePath = $this->findImageFromSeederInventory($slug);
        if ($imagePath) {
            return $imagePath;
        }

        return null;
    }

    protected function findImageFromSeederInventory(string $slug): ?string
    {
        try {
            // Try to load inventory data directly from JSON file
            $jsonPath = database_path('data/scraped-blogs.json');
            if (!file_exists($jsonPath)) {
                return null;
            }

            $json = file_get_contents($jsonPath);
            $inventory = json_decode($json, true);

            if (!is_array($inventory)) {
                return null;
            }

            // Find the post in inventory
            foreach ($inventory as $item) {
                if (($item['slug'] ?? '') === $slug) {
                    $imageUrl = $item['featured_image_url'] ?? null;
                    if ($imageUrl) {
                        // Use BlogImageService to resolve the URL
                        return $this->blogImageService->resolveUrl($imageUrl, $slug);
                    }
                    break;
                }
            }
        } catch (\Exception $e) {
            // Silently fail - inventory might not be accessible
        }

        return null;
    }
}
