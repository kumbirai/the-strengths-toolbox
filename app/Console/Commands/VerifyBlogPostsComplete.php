<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VerifyBlogPostsComplete extends Command
{
    protected $signature = 'blog:verify-complete';

    protected $description = 'Complete verification of all blog posts from website';

    public function handle(): int
    {
        $this->info('Complete Blog Post Verification');
        $this->info('================================');
        $this->newLine();

        // Get all posts from website
        $websitePosts = $this->getWebsitePosts();
        $this->info('Found '.count($websitePosts).' posts on website');
        $this->newLine();

        // Get all posts from database
        $dbPosts = BlogPost::all()->keyBy('slug');
        $this->info('Found '.$dbPosts->count().' posts in database');
        $this->newLine();

        $this->info('Verification Results:');
        $this->newLine();

        $verified = [];
        $missing = [];
        $noImage = [];
        $hasImage = [];

        foreach ($websitePosts as $slug => $data) {
            $post = $dbPosts->get($slug);

            if (! $post) {
                $missing[] = ['slug' => $slug, 'title' => $data['title']];

                continue;
            }

            $imageStatus = $this->checkImage($post);

            $verified[] = [
                'post' => $post,
                'website_title' => $data['title'],
                'has_image' => $imageStatus['has_image'],
                'image_path' => $post->featured_image,
                'image_exists' => $imageStatus['file_exists'],
            ];

            if ($imageStatus['has_image'] && $imageStatus['file_exists']) {
                $hasImage[] = $post;
            } else {
                $noImage[] = ['post' => $post, 'reason' => $imageStatus['reason']];
            }
        }

        // Display results
        $this->table(
            ['Status', 'Title', 'Slug', 'Image'],
            array_map(function ($v) {
                $status = $v['has_image'] && $v['image_exists'] ? '✓' : '✗';
                $image = $v['has_image'] ? ($v['image_exists'] ? 'Yes' : 'Missing file') : 'No image';

                return [$status, $v['post']->title, $v['post']->slug, $image];
            }, $verified)
        );

        $this->newLine();

        // Summary
        $this->info('Summary:');
        $this->line('  Total website posts: '.count($websitePosts));
        $this->line('  Posts in database: '.count($verified));
        $this->line('  Posts with images: '.count($hasImage));
        $this->line('  Posts without images: '.count($noImage));
        $this->line('  Missing from database: '.count($missing));

        if (! empty($missing)) {
            $this->newLine();
            $this->error('Missing posts:');
            foreach ($missing as $m) {
                $this->line("  ✗ {$m['title']} ({$m['slug']})");
            }
        }

        if (! empty($noImage)) {
            $this->newLine();
            $this->warn('Posts without images:');
            foreach ($noImage as $n) {
                $this->line("  ⊘ {$n['post']->title} - {$n['reason']}");
            }
        }

        if (empty($missing) && empty($noImage)) {
            $this->newLine();
            $this->info('✓ All website posts exist and have images!');

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    protected function getWebsitePosts(): array
    {
        $posts = [];

        for ($page = 1; $page <= 4; $page++) {
            try {
                $url = "https://www.thestrengthstoolbox.com/blog/page/{$page}/";
                $response = Http::timeout(30)->get($url);

                if (! $response->successful()) {
                    continue;
                }

                $html = $response->body();
                libxml_use_internal_errors(true);
                $dom = new DOMDocument;
                @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
                libxml_clear_errors();

                $xpath = new DOMXPath($dom);

                // Find all blog post links
                $links = $xpath->query('//a[contains(@href, "/2023/")]');

                foreach ($links as $link) {
                    $href = $link->getAttribute('href');
                    if (preg_match('/\/(\d{4})\/(\d{2})\/([^\/]+)\/$/', $href, $matches)) {
                        $slug = $matches[3];
                        $title = trim($link->textContent);

                        if (! empty($title) && ! isset($posts[$slug])) {
                            $posts[$slug] = ['title' => $title, 'url' => $href];
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->warn("Error fetching page {$page}: ".$e->getMessage());
            }
        }

        return $posts;
    }

    protected function checkImage(BlogPost $post): array
    {
        if (! $post->featured_image) {
            return ['has_image' => false, 'file_exists' => false, 'reason' => 'No featured_image field set'];
        }

        $filePath = storage_path('app/public/'.$post->featured_image);
        $exists = file_exists($filePath);

        return [
            'has_image' => true,
            'file_exists' => $exists,
            'reason' => $exists ? 'OK' : 'File not found on disk',
        ];
    }
}
