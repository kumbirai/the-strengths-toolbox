<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;

class InspectBlogContent extends Command
{
    protected $signature = 'blog:inspect-content {slug : Blog post slug to inspect}';

    protected $description = 'Inspect blog post content to see image paths';

    public function handle(): int
    {
        $slug = $this->argument('slug');

        try {
            $post = BlogPost::where('slug', $slug)->first();

            if (!$post) {
                $this->error("Post not found: {$slug}");
                return Command::FAILURE;
            }

            $this->info("Inspecting content for: {$post->title}");
            $this->newLine();

            // Find all image references
            preg_match_all('/<img[^>]+>/i', $post->content, $imgMatches);
            
            $this->line("Found " . count($imgMatches[0]) . " image tag(s)");
            $this->newLine();

            foreach ($imgMatches[0] as $index => $imgTag) {
                $this->line("Image " . ($index + 1) . ":");
                
                // Extract src
                if (preg_match('/src=["\']([^"\']+)["\']/i', $imgTag, $srcMatch)) {
                    $src = $srcMatch[1];
                    $this->line("  src: {$src}");
                    if (str_contains($src, 'images/images/')) {
                        $this->warn("    ⚠ Contains double prefix!");
                    }
                }

                // Extract srcset
                if (preg_match('/srcset=["\']([^"\']+)["\']/i', $imgTag, $srcsetMatch)) {
                    $srcset = $srcsetMatch[1];
                    $this->line("  srcset: {$srcset}");
                    if (str_contains($srcset, 'images/images/')) {
                        $this->warn("    ⚠ Contains double prefix!");
                    }
                }

                $this->newLine();
            }

            // Count instances of double prefix
            $doublePrefixCount = substr_count($post->content, 'images/images/');
            if ($doublePrefixCount > 0) {
                $this->warn("Found {$doublePrefixCount} instance(s) of 'images/images/' in content");
            } else {
                $this->info("✓ No double prefix found in content");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
