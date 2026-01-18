<?php

namespace App\Console\Commands;

use App\Helpers\SEOValidator;
use App\Models\BlogPost;
use App\Models\Page;
use App\Services\SEOService;
use Illuminate\Console\Command;

class ValidateMetaTags extends Command
{
    protected $signature = 'seo:validate-meta-tags';

    protected $description = 'Validate SEO meta tags for all pages and blog posts';

    protected SEOService $seoService;

    public function __construct(SEOService $seoService)
    {
        parent::__construct();
        $this->seoService = $seoService;
    }

    public function handle(): int
    {
        $this->info('Validating meta tags...');
        $this->newLine();

        $issues = [];

        // Validate pages
        $this->info('Validating pages...');
        $pages = Page::where('is_published', true)->get();

        foreach ($pages as $page) {
            $meta = $this->seoService->getPageMeta($page);

            $titleValidation = SEOValidator::validateTitle($meta['title']);
            if (! $titleValidation['valid']) {
                $issues[] = [
                    'type' => 'Page',
                    'slug' => $page->slug,
                    'field' => 'Title',
                    'issue' => $titleValidation['message'],
                    'length' => $titleValidation['length'],
                ];
            }

            $descValidation = SEOValidator::validateDescription($meta['description']);
            if (! $descValidation['valid']) {
                $issues[] = [
                    'type' => 'Page',
                    'slug' => $page->slug,
                    'field' => 'Description',
                    'issue' => $descValidation['message'],
                    'length' => $descValidation['length'],
                ];
            }
        }

        // Validate blog posts
        $this->info('Validating blog posts...');
        $posts = BlogPost::where('is_published', true)->get();

        foreach ($posts as $post) {
            $meta = $this->seoService->getBlogPostMeta($post);

            $titleValidation = SEOValidator::validateTitle($meta['title']);
            if (! $titleValidation['valid']) {
                $issues[] = [
                    'type' => 'Blog Post',
                    'slug' => $post->slug,
                    'field' => 'Title',
                    'issue' => $titleValidation['message'],
                    'length' => $titleValidation['length'],
                ];
            }

            $descValidation = SEOValidator::validateDescription($meta['description']);
            if (! $descValidation['valid']) {
                $issues[] = [
                    'type' => 'Blog Post',
                    'slug' => $post->slug,
                    'field' => 'Description',
                    'issue' => $descValidation['message'],
                    'length' => $descValidation['length'],
                ];
            }
        }

        // Display results
        if (empty($issues)) {
            $this->info('✓ All meta tags are valid!');

            return 0;
        }

        $this->error('Found '.count($issues).' issues:');
        $this->newLine();

        $this->table(
            ['Type', 'Slug', 'Field', 'Issue', 'Length'],
            array_map(function ($issue) {
                return [
                    $issue['type'],
                    $issue['slug'],
                    $issue['field'],
                    $issue['issue'],
                    $issue['length'],
                ];
            }, $issues)
        );

        return 1;
    }
}
