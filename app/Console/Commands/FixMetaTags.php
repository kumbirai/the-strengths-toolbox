<?php

namespace App\Console\Commands;

use App\Helpers\SEOValidator;
use App\Models\Page;
use App\Services\SEOService;
use Illuminate\Console\Command;

class FixMetaTags extends Command
{
    protected $signature = 'seo:fix-meta-tags {--dry-run : Show what would be fixed without making changes}';

    protected $description = 'Fix meta tag content issues (title and description lengths)';

    protected SEOService $seoService;

    public function __construct(SEOService $seoService)
    {
        parent::__construct();
        $this->seoService = $seoService;
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('Fixing meta tag content issues...');
        $this->newLine();

        $fixed = 0;
        $skipped = 0;

        // Get all published pages
        $pages = Page::where('is_published', true)->get();

        foreach ($pages as $page) {
            $needsFix = false;
            $updates = [];
            $seoUpdates = [];

            // Use SEOService to get what will actually be used (same as validation)
            $meta = $this->seoService->getPageMeta($page);
            $title = $meta['title'];
            $description = $meta['description'];

            $seo = $page->seo;

            // Check and fix title
            $titleValidation = SEOValidator::validateTitle($title);

            if (! $titleValidation['valid']) {
                $needsFix = true;
                if ($titleValidation['length'] > 60) {
                    // Truncate to 60 characters
                    $newTitle = mb_substr($title, 0, 57).'...';
                    $updates['meta_title'] = $newTitle;
                    if ($seo) {
                        $seoUpdates['og_title'] = $newTitle;
                    }
                    $this->line("  Title: {$title} → {$newTitle}");
                } elseif ($titleValidation['length'] < 30) {
                    // Extend title (add site name)
                    $newTitle = $title.' - The Strengths Toolbox';
                    if (mb_strlen($newTitle) > 60) {
                        $newTitle = mb_substr($title, 0, 40).' - The Strengths Toolbox';
                    }
                    $updates['meta_title'] = $newTitle;
                    if ($seo) {
                        $seoUpdates['og_title'] = $newTitle;
                    }
                    $this->line("  Title: {$title} → {$newTitle}");
                }
            }

            // Check and fix description
            $descValidation = SEOValidator::validateDescription($description);

            if (! $descValidation['valid']) {
                $needsFix = true;
                if ($descValidation['length'] < 120) {
                    // Extend description from content
                    $newDescription = $this->generateDescription($page->content, 155);
                    if (mb_strlen($newDescription) < 120) {
                        // If still too short, add generic text
                        $newDescription = $description.' Learn more about our strengths-based development programs and services.';
                        if (mb_strlen($newDescription) > 160) {
                            $newDescription = mb_substr($newDescription, 0, 157).'...';
                        }
                    }
                    // Update both page and seo record
                    $updates['meta_description'] = $newDescription;
                    if ($seo) {
                        $seoUpdates['og_description'] = $newDescription;
                    }
                    $this->line("  Description: {$description} → {$newDescription}");
                } elseif ($descValidation['length'] > 160) {
                    // Truncate to 160 characters
                    $newDescription = mb_substr($description, 0, 157).'...';
                    $updates['meta_description'] = $newDescription;
                    if ($seo) {
                        $seoUpdates['og_description'] = $newDescription;
                    }
                    $this->line("  Description: {$description} → {$newDescription}");
                }
            }

            if ($needsFix) {
                $this->info("Page: {$page->slug}");

                if (! $dryRun && (! empty($updates) || ! empty($seoUpdates))) {
                    if (! empty($updates)) {
                        $page->update($updates);
                    }
                    // Also update PageSEO if it exists
                    if ($seo && ! empty($seoUpdates)) {
                        $seo->update($seoUpdates);
                    } elseif (! empty($seoUpdates) && ! $seo) {
                        // Create PageSEO record if it doesn't exist
                        \App\Models\PageSEO::create(array_merge([
                            'page_id' => $page->id,
                        ], $seoUpdates));
                    }
                    $fixed++;
                    $this->info('  ✅ Fixed');
                } else {
                    $this->warn('  ⚠️  Would fix (dry run)');
                    $fixed++;
                }
                $this->newLine();
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->info("Would fix: {$fixed} pages");
            $this->info("Would skip: {$skipped} pages");
            $this->warn('Run without --dry-run to apply fixes');
        } else {
            $this->info("✅ Fixed: {$fixed} pages");
            $this->info("⏭️  Skipped: {$skipped} pages");
        }

        return 0;
    }

    protected function generateDescription(string $content, int $length = 160): string
    {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - 3).'...';
    }
}
