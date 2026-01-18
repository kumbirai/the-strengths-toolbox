<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;

class VerifyBlogPosts extends Command
{
    protected $signature = 'blog:verify {--website-slugs= : Path to file with website slugs}';

    protected $description = 'Verify all blog posts from website exist in database and have images';

    public function handle(): int
    {
        $slugsFile = $this->option('website-slugs') ?: '/tmp/website_slugs.txt';

        if (! file_exists($slugsFile)) {
            $this->error("Slugs file not found: {$slugsFile}");

            return Command::FAILURE;
        }

        $websiteSlugs = array_filter(array_map('trim', file($slugsFile)));
        $this->info('Found '.count($websiteSlugs).' blog posts on website');
        $this->newLine();

        $dbPosts = BlogPost::all()->keyBy('slug');
        $this->info('Found '.$dbPosts->count().' blog posts in database');
        $this->newLine();

        $this->info('Verification Report:');
        $this->newLine();

        $missing = [];
        $hasImage = [];
        $noImage = [];
        $extra = [];

        // Check website posts against database
        foreach ($websiteSlugs as $slug) {
            if ($dbPosts->has($slug)) {
                $post = $dbPosts->get($slug);
                if ($post->featured_image && file_exists(storage_path('app/public/'.$post->featured_image))) {
                    $hasImage[] = $post;
                } else {
                    $noImage[] = $post;
                }
            } else {
                $missing[] = $slug;
            }
        }

        // Check for extra posts in database
        foreach ($dbPosts as $slug => $post) {
            if (! in_array($slug, $websiteSlugs)) {
                $extra[] = $post;
            }
        }

        // Report missing posts
        if (! empty($missing)) {
            $this->error('Missing from database ('.count($missing).'):');
            foreach ($missing as $slug) {
                $this->line("  ✗ {$slug}");
            }
            $this->newLine();
        } else {
            $this->info('✓ All website posts exist in database');
            $this->newLine();
        }

        // Report posts without images
        if (! empty($noImage)) {
            $this->warn('Posts without images ('.count($noImage).'):');
            foreach ($noImage as $post) {
                $this->line("  ⊘ {$post->title}");
                $this->line("     Slug: {$post->slug}");
                if ($post->featured_image) {
                    $this->line("     Featured image set but file missing: {$post->featured_image}");
                } else {
                    $this->line('     No featured image set');
                }
            }
            $this->newLine();
        }

        // Report posts with images
        $this->info('Posts with images ('.count($hasImage).'):');
        foreach ($hasImage as $post) {
            $this->line("  ✓ {$post->title}");
        }
        $this->newLine();

        // Report extra posts
        if (! empty($extra)) {
            $this->comment('Extra posts in database (not on website) ('.count($extra).'):');
            foreach ($extra as $post) {
                $this->line("  + {$post->title} ({$post->slug})");
            }
            $this->newLine();
        }

        // Summary
        $this->info('Summary:');
        $this->line('  Website posts: '.count($websiteSlugs));
        $this->line('  Database posts: '.$dbPosts->count());
        $this->line('  Posts with images: '.count($hasImage));
        $this->line('  Posts without images: '.count($noImage));
        $this->line('  Missing from database: '.count($missing));
        $this->line('  Extra in database: '.count($extra));

        if (empty($missing) && empty($noImage)) {
            $this->newLine();
            $this->info('✓ All website posts exist and have images!');

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}
