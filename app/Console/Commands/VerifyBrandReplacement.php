<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Testimonial;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class VerifyBrandReplacement extends Command
{
    protected $signature = 'content:verify-brand-replacement 
                            {--path=content-transformation : Path to content files}
                            {--fix : Automatically fix issues found}';

    protected $description = 'Verify that all TSA Business School references have been replaced';

    protected $tsaPatterns = [
        'TSA Business School',
        'TSA Business',
        'tsabusinessschool.co.za',
        'www.tsabusinessschool.co.za',
    ];

    protected $issues = [];

    protected $exceptions = [];

    public function handle()
    {
        $path = $this->option('path');

        // Check file system
        if (is_dir($path)) {
            $this->info('Scanning content files for TSA references...');
            $this->scanDirectory($path);
        }

        // Check database
        $this->info('Scanning database content for TSA references...');
        $this->scanDatabase();

        if (empty($this->issues)) {
            $this->info('✓ No TSA references found. Brand replacement verified!');

            return Command::SUCCESS;
        }

        $this->warn('Found '.count($this->issues).' potential TSA references:');
        $this->newLine();

        foreach ($this->issues as $issue) {
            $this->line("  File/Record: {$issue['location']}");
            $this->line("  Line/Field: {$issue['field']}");
            $this->line('  Text: '.substr($issue['text'], 0, 100).'...');
            $this->newLine();
        }

        if ($this->option('fix')) {
            $this->info('Attempting to fix issues...');
            // Auto-fix logic could be added here
        }

        return Command::FAILURE;
    }

    protected function scanDirectory($dir)
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = File::allFiles($dir);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            $content = File::get($file->getPathname());
            $lines = explode("\n", $content);

            foreach ($lines as $lineNum => $line) {
                foreach ($this->tsaPatterns as $pattern) {
                    if (stripos($line, $pattern) !== false) {
                        // Check if it's in an exception list
                        if (! $this->isException($file->getPathname(), $lineNum + 1)) {
                            $this->issues[] = [
                                'location' => $file->getRelativePathname(),
                                'field' => 'Line '.($lineNum + 1),
                                'text' => trim($line),
                                'pattern' => $pattern,
                            ];
                        }
                    }
                }
            }
        }
    }

    protected function scanDatabase()
    {
        // Check pages
        $pages = Page::all();
        foreach ($pages as $page) {
            $this->checkContent($page->title, 'Page: '.$page->slug.' (title)', $page->id);
            $this->checkContent($page->excerpt, 'Page: '.$page->slug.' (excerpt)', $page->id);
            $this->checkContent($page->content, 'Page: '.$page->slug.' (content)', $page->id);
            $this->checkContent($page->meta_title, 'Page: '.$page->slug.' (meta_title)', $page->id);
            $this->checkContent($page->meta_description, 'Page: '.$page->slug.' (meta_description)', $page->id);
        }

        // Check blog posts
        $posts = BlogPost::all();
        foreach ($posts as $post) {
            $this->checkContent($post->title, 'Blog Post: '.$post->slug.' (title)', $post->id);
            $this->checkContent($post->excerpt, 'Blog Post: '.$post->slug.' (excerpt)', $post->id);
            $this->checkContent($post->content, 'Blog Post: '.$post->slug.' (content)', $post->id);
            $this->checkContent($post->meta_title, 'Blog Post: '.$post->slug.' (meta_title)', $post->id);
            $this->checkContent($post->meta_description, 'Blog Post: '.$post->slug.' (meta_description)', $post->id);
        }

        // Check testimonials
        $testimonials = Testimonial::all();
        foreach ($testimonials as $testimonial) {
            $this->checkContent($testimonial->testimonial, 'Testimonial: '.$testimonial->id.' (testimonial)', $testimonial->id);
            $this->checkContent($testimonial->company, 'Testimonial: '.$testimonial->id.' (company)', $testimonial->id);
        }
    }

    protected function checkContent($content, $location, $id)
    {
        if (empty($content)) {
            return;
        }

        foreach ($this->tsaPatterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                $this->issues[] = [
                    'location' => $location,
                    'field' => 'ID: '.$id,
                    'text' => substr($content, 0, 200),
                    'pattern' => $pattern,
                ];
            }
        }
    }

    protected function isException($file, $line)
    {
        // Check if this is a documented exception
        $exceptionsFile = base_path('content-transformation/brand-replacement-exceptions.md');

        if (file_exists($exceptionsFile)) {
            // Could parse exceptions file and check
            // For now, return false
        }

        return false;
    }
}
