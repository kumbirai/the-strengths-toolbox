<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearBlogCache extends Command
{
    protected $signature = 'blog:clear-cache {--slug= : Clear cache for specific post by slug}';

    protected $description = 'Clear cache for blog posts';

    public function handle(): int
    {
        $slugFilter = $this->option('slug');

        $this->info('Clearing blog post cache...');
        $this->newLine();

        try {
            if ($slugFilter) {
                $post = BlogPost::where('slug', $slugFilter)->first();
                if ($post) {
                    $this->clearPostCache($post);
                    $this->info("✓ Cleared cache for: {$post->slug}");
                } else {
                    $this->warn("Post not found: {$slugFilter}");
                    return Command::FAILURE;
                }
            } else {
                // Clear all blog post caches
                $posts = BlogPost::all();
                $cleared = 0;
                
                $bar = $this->output->createProgressBar($posts->count());
                $bar->start();
                
                foreach ($posts as $post) {
                    $this->clearPostCache($post);
                    $cleared++;
                    $bar->advance();
                }
                
                $bar->finish();
                $this->newLine(2);
                $this->info("✓ Cleared cache for {$cleared} post(s)");
            }

            // Also clear list caches
            Cache::flush(); // Or use specific keys if you want more control
            $this->info("✓ Cleared all blog-related caches");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to clear cache: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function clearPostCache(BlogPost $post): void
    {
        Cache::forget("blog_post.published.slug.{$post->slug}");
        Cache::forget("blog_post.id.{$post->id}");
        Cache::forget("seo.blog_post.{$post->id}");
    }
}
