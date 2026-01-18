<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Console\Command;

class VerifyContentAccuracy extends Command
{
    protected $signature = 'content:verify-accuracy';

    protected $description = 'Verify content accuracy and completeness';

    public function handle(): int
    {
        $this->info('Verifying content accuracy...');
        $this->newLine();

        $results = [
            'pages' => $this->verifyPages(),
            'blog_posts' => $this->verifyBlogPosts(),
        ];

        $this->displayResults($results);

        $totalIssues = count($results['pages']['issues']) + count($results['blog_posts']['issues']);

        return $totalIssues === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    protected function verifyPages(): array
    {
        $pages = Page::all();
        $issues = [];

        foreach ($pages as $page) {
            // Check for placeholder content
            if ($this->isPlaceholderContent($page->content)) {
                $issues[] = [
                    'type' => 'Placeholder Content',
                    'page' => $page->slug,
                    'issue' => 'Content appears to be placeholder',
                ];
            }

            // Check minimum content length
            if (strlen(strip_tags($page->content)) < 100) {
                $issues[] = [
                    'type' => 'Insufficient Content',
                    'page' => $page->slug,
                    'issue' => 'Content is too short (less than 100 characters)',
                ];
            }

            // Check for required sections (homepage)
            if ($page->slug === 'home' && ! $this->hasRequiredHomepageSections($page->content)) {
                $issues[] = [
                    'type' => 'Missing Sections',
                    'page' => $page->slug,
                    'issue' => 'Homepage missing required sections',
                ];
            }

            // Check contact information accuracy
            $contactIssues = $this->verifyContactInformation($page->content, $page->slug);
            if (! empty($contactIssues)) {
                $issues = array_merge($issues, $contactIssues);
            }
        }

        return [
            'total' => $pages->count(),
            'issues' => $issues,
        ];
    }

    protected function verifyBlogPosts(): array
    {
        $posts = BlogPost::all();
        $issues = [];

        foreach ($posts as $post) {
            // Check for placeholder content
            if ($this->isPlaceholderContent($post->content)) {
                $issues[] = [
                    'type' => 'Placeholder Content',
                    'post' => $post->slug,
                    'issue' => 'Content appears to be placeholder',
                ];
            }

            // Check minimum content length
            if (strlen(strip_tags($post->content)) < 200) {
                $issues[] = [
                    'type' => 'Insufficient Content',
                    'post' => $post->slug,
                    'issue' => 'Content is too short (less than 200 characters)',
                ];
            }

            // Check for required metadata
            if (empty($post->excerpt)) {
                $issues[] = [
                    'type' => 'Missing Metadata',
                    'post' => $post->slug,
                    'issue' => 'Missing excerpt',
                ];
            }
        }

        return [
            'total' => $posts->count(),
            'issues' => $issues,
        ];
    }

    protected function isPlaceholderContent(string $content): bool
    {
        $placeholders = [
            'Lorem ipsum',
            'Placeholder',
            'Sample content',
            'Content goes here',
            'TODO',
            'FIXME',
            'Content coming soon',
        ];

        foreach ($placeholders as $placeholder) {
            if (stripos($content, $placeholder) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function hasRequiredHomepageSections(string $content): bool
    {
        $requiredSections = [
            'hero',
            'power-of-strengths',
            'three-pillars',
            'testimonials',
        ];

        foreach ($requiredSections as $section) {
            if (stripos($content, $section) === false) {
                return false;
            }
        }

        return true;
    }

    protected function verifyContactInformation(string $content, string $slug): array
    {
        $issues = [];

        // Check for correct phone number
        $correctPhone = '+27 83 294 8033';
        if (stripos($content, $correctPhone) === false) {
            // Check for old phone numbers
            $oldPhones = [
                '083 294 8033',
                '0832948033',
                'info@tsabusinessschool.co.za',
            ];

            foreach ($oldPhones as $oldPhone) {
                if (stripos($content, $oldPhone) !== false) {
                    $issues[] = [
                        'type' => 'Incorrect Contact Info',
                        'page' => $slug,
                        'issue' => "Found old contact information: {$oldPhone}",
                    ];
                }
            }
        }

        // Check for correct email
        $correctEmail = 'welcome@eberhardniklaus.co.za';
        if (stripos($content, $correctEmail) === false) {
            // Check for old emails
            $oldEmails = [
                'info@tsabusinessschool.co.za',
                'contact@tsabusinessschool.co.za',
            ];

            foreach ($oldEmails as $oldEmail) {
                if (stripos($content, $oldEmail) !== false) {
                    $issues[] = [
                        'type' => 'Incorrect Contact Info',
                        'page' => $slug,
                        'issue' => "Found old email: {$oldEmail}",
                    ];
                }
            }
        }

        return $issues;
    }

    protected function displayResults(array $results): void
    {
        $totalIssues = count($results['pages']['issues']) + count($results['blog_posts']['issues']);

        if ($totalIssues === 0) {
            $this->info('✓ All content verified successfully!');

            return;
        }

        $this->warn("Found {$totalIssues} issues:");
        $this->newLine();

        if (! empty($results['pages']['issues'])) {
            $this->line('Pages:');
            foreach ($results['pages']['issues'] as $issue) {
                $this->line("  - {$issue['page']}: {$issue['issue']}");
            }
            $this->newLine();
        }

        if (! empty($results['blog_posts']['issues'])) {
            $this->line('Blog Posts:');
            foreach ($results['blog_posts']['issues'] as $issue) {
                $this->line("  - {$issue['post']}: {$issue['issue']}");
            }
        }
    }
}
