<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Testimonial;
use Illuminate\Console\Command;

/**
 * Verify content migration completeness
 */
class VerifyContentMigration extends Command
{
    protected $signature = 'content:verify';

    protected $description = 'Verify content migration completeness and brand name replacement';

    protected array $requiredPages = [
        'the-power-of-strengths',
        'strengths-based-development/teams',
        'strengths-based-development/managers-leaders',
        'strengths-based-development/salespeople',
        'strengths-based-development/individuals',
        'sales-training/strengths-based-training',
        'sales-training/relationship-selling',
        'sales-training/selling-on-the-phone',
        'sales-training/sales-fundamentals-workshop',
        'sales-training/top-10-sales-secrets',
        'sales-training/in-person-sales',
        'facilitation/customer-service-workshop',
        'facilitation/emotional-intelligence-workshop',
        'facilitation/goal-setting',
        'facilitation/high-performance-teams',
        'facilitation/interpersonal-skills',
        'facilitation/personal-finances',
        'facilitation/presentation-skills',
        'facilitation/supervising-others',
        'keynote-talks',
        'books',
    ];

    public function handle(): int
    {
        $this->info('Verifying content migration...');
        $this->newLine();

        $results = [
            'pages' => $this->verifyPages(),
            'brand_replacement' => $this->verifyBrandReplacement(),
            'seo' => $this->verifySEO(),
            'content_quality' => $this->verifyContentQuality(),
        ];

        $this->displayResults($results);

        $allPassed = ! in_array(false, array_column($results, 'passed'));

        return $allPassed ? Command::SUCCESS : Command::FAILURE;
    }

    protected function verifyPages(): array
    {
        $this->info('Verifying required pages...');

        $missing = [];
        $found = 0;

        foreach ($this->requiredPages as $slug) {
            $page = Page::where('slug', $slug)->first();
            if ($page && $page->is_published) {
                $found++;
            } else {
                $missing[] = $slug;
            }
        }

        $total = count($this->requiredPages);
        $passed = empty($missing);

        if ($passed) {
            $this->line("  ✓ All {$total} required pages found and published");
        } else {
            $missingCount = $total - $found;
            $this->error("  ✗ Missing {$missingCount} pages:");
            foreach ($missing as $slug) {
                $this->line("    - {$slug}");
            }
        }

        return [
            'passed' => $passed,
            'found' => $found,
            'total' => $total,
            'missing' => $missing,
        ];
    }

    protected function verifyBrandReplacement(): array
    {
        $this->info('Verifying brand name replacement...');

        $issues = [];
        $checked = 0;
        $tsaPatterns = [
            'TSA Business School',
            'TSA Business',
            'tsabusinessschool.co.za',
            'www.tsabusinessschool.co.za',
        ];

        // Check pages
        $pages = Page::all();
        foreach ($pages as $page) {
            $checked++;
            foreach ($tsaPatterns as $pattern) {
                if (stripos($page->content, $pattern) !== false ||
                    stripos($page->title, $pattern) !== false ||
                    stripos($page->excerpt ?? '', $pattern) !== false ||
                    stripos($page->meta_title ?? '', $pattern) !== false ||
                    stripos($page->meta_description ?? '', $pattern) !== false) {

                    $issues[] = [
                        'type' => 'Page',
                        'id' => $page->id,
                        'slug' => $page->slug,
                        'field' => 'content/title/excerpt/meta',
                        'pattern' => $pattern,
                    ];
                }
            }
        }

        // Check blog posts
        $posts = BlogPost::all();
        foreach ($posts as $post) {
            $checked++;
            foreach ($tsaPatterns as $pattern) {
                if (stripos($post->content, $pattern) !== false ||
                    stripos($post->title, $pattern) !== false ||
                    stripos($post->excerpt ?? '', $pattern) !== false ||
                    stripos($post->meta_title ?? '', $pattern) !== false ||
                    stripos($post->meta_description ?? '', $pattern) !== false) {

                    $issues[] = [
                        'type' => 'Blog Post',
                        'id' => $post->id,
                        'slug' => $post->slug,
                        'field' => 'content/title/excerpt/meta',
                        'pattern' => $pattern,
                    ];
                }
            }
        }

        // Check testimonials
        $testimonials = Testimonial::all();
        foreach ($testimonials as $testimonial) {
            $checked++;
            foreach ($tsaPatterns as $pattern) {
                if (stripos($testimonial->testimonial, $pattern) !== false ||
                    stripos($testimonial->company ?? '', $pattern) !== false) {

                    $issues[] = [
                        'type' => 'Testimonial',
                        'id' => $testimonial->id,
                        'field' => 'testimonial/company',
                        'pattern' => $pattern,
                    ];
                }
            }
        }

        $passed = empty($issues);

        if ($passed) {
            $this->line("  ✓ No TSA references found in {$checked} items");
        } else {
            $this->error('  ✗ Found '.count($issues).' potential TSA references:');
            foreach (array_slice($issues, 0, 10) as $issue) {
                $slug = $issue['slug'] ?? 'N/A';
                $this->line("    - {$issue['type']} #{$issue['id']} ({$slug}): {$issue['pattern']}");
            }
            if (count($issues) > 10) {
                $this->line('    ... and '.(count($issues) - 10).' more');
            }
        }

        return [
            'passed' => $passed,
            'checked' => $checked,
            'issues' => $issues,
        ];
    }

    protected function verifySEO(): array
    {
        $this->info('Verifying SEO metadata...');

        $issues = [];
        $checked = 0;

        // Check pages
        $pages = Page::where('is_published', true)->get();
        foreach ($pages as $page) {
            $checked++;
            $pageIssues = $this->checkPageSEO($page);
            $issues = array_merge($issues, $pageIssues);
        }

        // Check blog posts
        $posts = BlogPost::where('is_published', true)->get();
        foreach ($posts as $post) {
            $checked++;
            $postIssues = $this->checkPostSEO($post);
            $issues = array_merge($issues, $postIssues);
        }

        $passed = empty($issues);

        if ($passed) {
            $this->line("  ✓ SEO metadata verified for {$checked} items");
        } else {
            $this->error('  ✗ Found '.count($issues).' SEO issues:');
            foreach (array_slice($issues, 0, 10) as $issue) {
                $this->line("    - {$issue['item']}: {$issue['issue']}");
            }
        }

        return [
            'passed' => $passed,
            'checked' => $checked,
            'issues' => $issues,
        ];
    }

    protected function checkPageSEO(Page $page): array
    {
        $issues = [];

        // Check meta title
        if (empty($page->meta_title)) {
            $issues[] = [
                'item' => $page->slug,
                'issue' => 'Missing meta title',
            ];
        } elseif (strlen($page->meta_title) > 60) {
            $issues[] = [
                'item' => $page->slug,
                'issue' => 'Meta title too long ('.strlen($page->meta_title).' characters)',
            ];
        }

        // Check meta description
        if (empty($page->meta_description)) {
            $issues[] = [
                'item' => $page->slug,
                'issue' => 'Missing meta description',
            ];
        } elseif (strlen($page->meta_description) > 160) {
            $issues[] = [
                'item' => $page->slug,
                'issue' => 'Meta description too long ('.strlen($page->meta_description).' characters)',
            ];
        } elseif (strlen($page->meta_description) < 120) {
            $issues[] = [
                'item' => $page->slug,
                'issue' => 'Meta description too short ('.strlen($page->meta_description).' characters)',
            ];
        }

        // Check for H1
        if (stripos($page->content, '<h1') === false) {
            $issues[] = [
                'item' => $page->slug,
                'issue' => 'Missing H1 heading',
            ];
        }

        return $issues;
    }

    protected function checkPostSEO(BlogPost $post): array
    {
        $issues = [];

        if (empty($post->meta_title)) {
            $issues[] = [
                'item' => $post->slug,
                'issue' => 'Missing meta title',
            ];
        }

        if (empty($post->meta_description)) {
            $issues[] = [
                'item' => $post->slug,
                'issue' => 'Missing meta description',
            ];
        }

        return $issues;
    }

    protected function verifyContentQuality(): array
    {
        $this->info('Verifying content quality...');

        $issues = [];

        // Check for placeholder content
        $placeholderPatterns = [
            'Content coming soon',
            'Content for',
            'Lorem ipsum',
            'Placeholder',
        ];

        $pages = Page::where('is_published', true)->get();
        foreach ($pages as $page) {
            foreach ($placeholderPatterns as $pattern) {
                if (stripos($page->content, $pattern) !== false) {
                    $issues[] = "Page '{$page->title}' may contain placeholder content";
                    break;
                }
            }

            // Check minimum content length
            $contentLength = strlen(strip_tags($page->content));
            if ($contentLength < 100) {
                $issues[] = "Page '{$page->title}' has very short content ({$contentLength} chars)";
            }
        }

        $passed = empty($issues);

        if ($passed) {
            $this->line('  ✓ Content quality checks passed');
        } else {
            $this->warn('  ⚠ Found '.count($issues).' content quality issues:');
            foreach (array_slice($issues, 0, 5) as $issue) {
                $this->line("    - {$issue}");
            }
        }

        return [
            'passed' => $passed,
            'issues' => $issues,
        ];
    }

    protected function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('Verification Results:');
        $this->newLine();

        foreach ($results as $check => $result) {
            $status = $result['passed'] ? '✓' : '✗';
            $checkName = ucwords(str_replace('_', ' ', $check));

            $this->line("  {$status} {$checkName}");

            if (! $result['passed'] && isset($result['issues'])) {
                $this->line('    Issues: '.count($result['issues']));
            }
        }

        $this->newLine();

        $passed = count(array_filter($results, fn ($r) => $r['passed']));
        $total = count($results);

        $this->info("Summary: {$passed}/{$total} checks passed");
    }
}
