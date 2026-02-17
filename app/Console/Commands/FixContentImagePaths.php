<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FixContentImagePaths extends Command
{
    protected $signature = 'blog:fix-content-images 
                            {--dry-run : Show what would be fixed without making changes}
                            {--slug= : Fix specific post by slug}';

    protected $description = 'Fix double images/images prefix in blog post content HTML';

    protected int $fixed = 0;
    protected int $checked = 0;

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $slugFilter = $this->option('slug');

        $this->info('Fixing image paths in blog post content...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        $this->newLine();

        try {
            $query = BlogPost::whereNotNull('content')->where('content', '!=', '');
            if ($slugFilter) {
                $query->where('slug', $slugFilter);
            }
            $posts = $query->get();
        } catch (\Exception $e) {
            $this->error('Failed to connect to database: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("Checking {$posts->count()} post(s)");
        $this->newLine();

        $bar = $this->output->createProgressBar($posts->count());
        $bar->start();

        foreach ($posts as $post) {
            $this->checked++;
            $originalContent = $post->content;
            $fixedContent = $this->fixImagePathsInContent($originalContent);

            if ($fixedContent !== $originalContent) {
                // Count how many instances were fixed
                $originalCount = substr_count($originalContent, 'images/images/');
                $fixedCount = substr_count($fixedContent, 'images/images/');
                
                if (!$dryRun) {
                    $post->content = $fixedContent;
                    $post->save();
                    
                    // Clear cache for this post
                    Cache::forget("blog_post.published.slug.{$post->slug}");
                    Cache::forget("blog_post.id.{$post->id}");
                    Cache::forget("seo.blog_post.{$post->id}");
                } else {
                    // Show what would be fixed in dry-run mode
                    if ($this->output->isVerbose()) {
                        $this->newLine();
                        $this->line("  Would fix: {$post->slug}");
                        $this->line("    Found {$originalCount} instances of 'images/images/'");
                    }
                }
                $this->fixed++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Summary:');
        $this->line("  Checked: {$this->checked}");
        if ($this->fixed > 0) {
            $this->info("  ✓ Fixed: {$this->fixed}");
        } else {
            $this->info("  ✓ No fixes needed");
        }

        return Command::SUCCESS;
    }

    protected function fixImagePathsInContent(string $content): string
    {
        // Most aggressive approach: replace all instances of images/images/ with images/
        // Do this first as a catch-all, then refine specific cases
        
        // Fix in all HTML attributes (src, srcset, data-src, data-srcset, href, etc.)
        // Pattern: attribute="...images/images/blog/..." or attribute="/images/images/blog/..."
        $content = preg_replace_callback(
            '/(\w+)=["\']([^"\']*?)(\/?)images\/images\/(blog\/[^"\']+)([^"\']*?)["\']/i',
            function ($matches) {
                $attr = $matches[1];
                $before = $matches[2];
                $slash = $matches[3];
                $path = $matches[4];
                $after = $matches[5];
                return $attr . '="' . $before . $slash . 'images/' . $path . $after . '"';
            },
            $content
        );

        // Fix in srcset specifically (can have multiple URLs separated by commas)
        $content = preg_replace_callback(
            '/(srcset|data-srcset)=["\']([^"\']+)["\']/i',
            function ($matches) {
                $srcset = $matches[2];
                // Replace all instances of images/images/ in the srcset
                $fixedSrcset = preg_replace(
                    '#(/?)images/images/(blog/[^\s,]+)#i',
                    '$1images/$2',
                    $srcset
                );
                return $matches[1] . '="' . $fixedSrcset . '"';
            },
            $content
        );

        // Fix in style attributes (background-image: url(...))
        $content = preg_replace_callback(
            '/(style=["\'][^"\']*url\(["\']?)(\/?)images\/images\/(blog\/[^"\']+)(["\']?\)[^"\']*["\'])/i',
            function ($matches) {
                return $matches[1] . $matches[2] . 'images/' . $matches[3] . $matches[4];
            },
            $content
        );

        // Fix standalone URLs in content (not in attributes)
        $content = preg_replace(
            '#(/?)images/images/(blog/[^"\'>\s<,]+)#i',
            '$1images/$2',
            $content
        );

        // Fix absolute URLs
        $content = preg_replace(
            '#(https?://[^"\'>\s]+)images/images/(blog/[^"\'>\s]+)#i',
            '$1images/$2',
            $content
        );

        // Fix storage paths
        $content = preg_replace(
            '#(/storage/images/blog/)([^"\'>\s]+)#i',
            '/images/blog/$2',
            $content
        );

        // Final catch-all: replace any remaining images/images/ with images/
        // Do this multiple times to catch nested cases
        $iterations = 0;
        while (str_contains($content, 'images/images/') && $iterations < 10) {
            $content = str_replace('images/images/', 'images/', $content);
            $iterations++;
        }

        return $content;
    }
}
