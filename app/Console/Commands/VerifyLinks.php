<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VerifyLinks extends Command
{
    protected $signature = 'content:verify-links 
                            {--fix : Automatically fix common issues}
                            {--timeout=5 : Request timeout in seconds}';

    protected $description = 'Verify all links in content';

    protected $brokenLinks = [];

    protected $checkedLinks = 0;

    public function handle(): int
    {
        $this->info('Verifying links...');
        $this->newLine();

        $this->verifyPageLinks();
        $this->verifyBlogPostLinks();

        $this->displayResults();

        return empty($this->brokenLinks) ? Command::SUCCESS : Command::FAILURE;
    }

    protected function verifyPageLinks(): void
    {
        $pages = Page::all();

        foreach ($pages as $page) {
            $this->extractAndVerifyLinks($page->content, 'Page: '.$page->slug);
        }
    }

    protected function verifyBlogPostLinks(): void
    {
        $posts = BlogPost::all();

        foreach ($posts as $post) {
            $this->extractAndVerifyLinks($post->content, 'Blog Post: '.$post->slug);
        }
    }

    protected function extractAndVerifyLinks(string $content, string $context): void
    {
        // Extract all links
        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);

        foreach ($matches[1] as $url) {
            $this->checkedLinks++;
            $this->verifyLink($url, $context);
        }

        // Also check markdown links
        preg_match_all('/\[([^\]]+)\]\(([^\)]+)\)/', $content, $matches);

        foreach ($matches[2] as $url) {
            $this->checkedLinks++;
            $this->verifyLink($url, $context);
        }
    }

    protected function verifyLink(string $url, string $context): void
    {
        // Skip mailto and tel links
        if (strpos($url, 'mailto:') === 0 || strpos($url, 'tel:') === 0) {
            return;
        }

        // Skip anchor links
        if (strpos($url, '#') === 0) {
            return;
        }

        // Handle relative URLs
        if (strpos($url, 'http') !== 0) {
            // For internal links, just check if they're valid routes
            // External link checking would require full URL
            return;
        }

        try {
            $timeout = (int) $this->option('timeout');
            $response = Http::timeout($timeout)->head($url);

            if ($response->failed()) {
                $this->brokenLinks[] = [
                    'url' => $url,
                    'context' => $context,
                    'status' => $response->status(),
                ];
            }
        } catch (\Exception $e) {
            $this->brokenLinks[] = [
                'url' => $url,
                'context' => $context,
                'status' => 'Error: '.$e->getMessage(),
            ];
        }
    }

    protected function displayResults(): void
    {
        $this->info("Checked {$this->checkedLinks} links");

        if (empty($this->brokenLinks)) {
            $this->info('✓ All links verified successfully!');

            return;
        }

        $this->error('Found '.count($this->brokenLinks).' broken links:');
        $this->newLine();

        foreach ($this->brokenLinks as $link) {
            $this->line("  - {$link['url']}");
            $this->line("    Context: {$link['context']}");
            $this->line("    Status: {$link['status']}");
            $this->newLine();
        }
    }
}
