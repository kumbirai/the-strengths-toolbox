<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;

class FixLawOfConnectionImage extends Command
{
    protected $signature = 'blog:fix-law-of-connection-image';

    protected $description = 'Fix the featured image for the Law of Connection blog post';

    public function handle(): int
    {
        $slug = 'unlocking-success-law-of-connection';
        
        $this->info("Fixing featured image for: {$slug}");
        $this->newLine();

        $post = BlogPost::where('slug', $slug)->first();

        if (!$post) {
            $this->error("Post not found with slug: {$slug}");
            return Command::FAILURE;
        }

        $this->line("Found post: {$post->title}");
        $this->line("Current featured_image: " . ($post->featured_image ?: '(empty)'));
        $this->newLine();

        // The correct image file exists at: storage/app/public/blog/unlocking-success-law-of-connection-1-400x250.webp
        $correctPath = 'blog/unlocking-success-law-of-connection-1-400x250.webp';
        $fullPath = storage_path('app/public/' . $correctPath);

        if (!file_exists($fullPath)) {
            $this->error("Image file not found at: {$fullPath}");
            $this->line("Checking for alternative sizes...");
            
            // Try other sizes
            $alternatives = [
                'blog/unlocking-success-law-of-connection-1-1080x675.webp',
                'blog/unlocking-success-law-of-connection-1-980x980.webp',
                'blog/unlocking-success-law-of-connection-1-480x480.webp',
            ];
            
            $found = false;
            foreach ($alternatives as $alt) {
                $altPath = storage_path('app/public/' . $alt);
                if (file_exists($altPath)) {
                    $this->info("Found alternative: {$alt}");
                    $correctPath = $alt;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                return Command::FAILURE;
            }
        } else {
            $this->info("✓ Image file found at: {$fullPath}");
        }

        $post->featured_image = $correctPath;
        $post->save();

        $this->newLine();
        $this->info("✓ Updated featured_image to: {$correctPath}");
        $this->line("New URL: {$post->featured_image_url}");

        return Command::SUCCESS;
    }
}
