<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\BlogImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DiagnoseBlogImages extends Command
{
    protected $signature = 'blog:diagnose-images {--fix : Fix missing images by reassigning them}';

    protected $description = 'Diagnose blog post featured images and optionally fix them';

    protected BlogImageService $blogImageService;

    public function __construct(BlogImageService $blogImageService)
    {
        parent::__construct();
        $this->blogImageService = $blogImageService;
    }

    public function handle(): int
    {
        $this->info('Diagnosing blog post featured images...');
        $this->newLine();

        $posts = BlogPost::where('is_published', true)->get();
        $totalPosts = $posts->count();
        $issues = [];

        foreach ($posts as $post) {
            $status = $this->checkPostImage($post);
            if (!$status['ok']) {
                $issues[] = [
                    'post' => $post,
                    'status' => $status,
                ];
            }
        }

        // Display summary
        $this->info("Total published posts: {$totalPosts}");
        $this->info("Posts with issues: " . count($issues));
        $this->newLine();

        if (count($issues) > 0) {
            $this->warn('Posts with image issues:');
            $this->newLine();

            $tableData = [];
            foreach ($issues as $issue) {
                $post = $issue['post'];
                $status = $issue['status'];
                
                $tableData[] = [
                    $post->slug,
                    Str::limit($post->title, 50),
                    $post->featured_image ?? '(empty)',
                    $status['reason'],
                ];
            }

            $this->table(
                ['Slug', 'Title', 'Featured Image Path', 'Issue'],
                $tableData
            );

            if ($this->option('fix')) {
                $this->newLine();
                $this->info('Fixing images...');
                $this->fixImages($issues);
            }
        } else {
            $this->info('✓ All blog posts have valid featured images!');
        }

        return Command::SUCCESS;
    }

    protected function checkPostImage(BlogPost $post): array
    {
        // Check if featured_image is set
        if (empty($post->featured_image)) {
            return [
                'ok' => false,
                'reason' => 'No featured_image set',
                'file_exists' => false,
            ];
        }

        $path = $post->featured_image;
        
        // Normalize path to images/blog/ format
        $normalizedPath = $this->blogImageService->getStandardPath($path);
        $publicPath = public_path($normalizedPath);
        $exists = file_exists($publicPath);
        
        return [
            'ok' => $exists,
            'reason' => $exists ? 'OK' : "File not found: {$normalizedPath}",
            'file_exists' => $exists,
            'checked_path' => $publicPath,
        ];
    }

    protected function fixImages(array $issues): void
    {
        $fixed = 0;
        $notFixed = 0;

        foreach ($issues as $issue) {
            $post = $issue['post'];
            $slug = $post->slug;

            // Try to find image by slug
            $imagePath = $this->findImageForSlug($slug);

            if ($imagePath) {
                $post->featured_image = $imagePath;
                $post->save();
                $this->line("  ✓ Fixed: {$post->title}");
                $this->line("    → {$imagePath}");
                $fixed++;
            } else {
                $this->warn("  ✗ Could not find image for: {$post->title}");
                $notFixed++;
            }
        }

        $this->newLine();
        $this->info("Fixed: {$fixed}, Could not fix: {$notFixed}");
    }

    protected function findImageForSlug(string $slug): ?string
    {
        return $this->blogImageService->findBySlug($slug);
    }
}
