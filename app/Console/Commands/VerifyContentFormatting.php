<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Console\Command;

class VerifyContentFormatting extends Command
{
    protected $signature = 'content:verify-formatting';

    protected $description = 'Verify content formatting';

    public function handle(): int
    {
        $this->info('Verifying content formatting...');
        $this->newLine();

        $issues = [];

        // Check pages
        $pages = Page::all();
        foreach ($pages as $page) {
            $pageIssues = $this->checkFormatting($page->content, $page->slug);
            $issues = array_merge($issues, $pageIssues);
        }

        // Check blog posts
        $posts = BlogPost::all();
        foreach ($posts as $post) {
            $postIssues = $this->checkFormatting($post->content, $post->slug);
            $issues = array_merge($issues, $postIssues);
        }

        $this->displayResults($issues);

        return empty($issues) ? Command::SUCCESS : Command::FAILURE;
    }

    protected function checkFormatting(string $content, string $slug): array
    {
        $issues = [];

        // Check heading hierarchy
        if (! $this->hasProperHeadingHierarchy($content)) {
            $issues[] = [
                'type' => 'Heading Hierarchy',
                'item' => $slug,
                'issue' => 'Heading hierarchy may be incorrect',
            ];
        }

        // Check for encoding issues
        if ($this->hasEncodingIssues($content)) {
            $issues[] = [
                'type' => 'Encoding Issues',
                'item' => $slug,
                'issue' => 'Content may have encoding issues',
            ];
        }

        return $issues;
    }

    protected function hasProperHeadingHierarchy(string $content): bool
    {
        // Extract headings
        preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h[1-6]>/i', $content, $matches);

        if (empty($matches[1])) {
            return true; // No headings to check
        }

        $levels = $matches[1];
        $previousLevel = 0;
        $h1Count = 0;

        foreach ($levels as $level) {
            $level = (int) $level;

            // H1 should only appear once
            if ($level === 1) {
                $h1Count++;
                if ($h1Count > 1) {
                    return false;
                }
            }

            // Levels should not skip (e.g., h1 to h3)
            if ($previousLevel > 0 && $level > $previousLevel + 1) {
                return false;
            }

            $previousLevel = $level;
        }

        return true;
    }

    protected function hasEncodingIssues(string $content): bool
    {
        // Check for common encoding issues
        $issues = [
            'â€™' => 'apostrophe issue',
            'â€œ' => 'quote issue',
            'â€"' => 'quote issue',
            'â€"' => 'quote issue',
        ];

        foreach ($issues as $pattern => $issue) {
            if (strpos($content, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function displayResults(array $issues): void
    {
        if (empty($issues)) {
            $this->info('✓ All formatting verified successfully!');

            return;
        }

        $this->warn('Found '.count($issues).' formatting issues:');
        $this->newLine();

        foreach ($issues as $issue) {
            $this->line("  - {$issue['item']}: {$issue['issue']}");
        }
    }
}
