<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Page;
use App\Services\SchemaService;
use Illuminate\Console\Command;

class ValidateSchema extends Command
{
    protected $signature = 'seo:validate-schema {--url= : Specific URL to validate}';

    protected $description = 'Validate Schema.org structured data';

    protected SchemaService $schemaService;

    public function __construct(SchemaService $schemaService)
    {
        parent::__construct();
        $this->schemaService = $schemaService;
    }

    public function handle(): int
    {
        $this->info('Validating Schema.org structured data...');
        $this->newLine();

        $errors = [];
        $warnings = [];

        // Validate Organization schema
        $this->info('Validating Organization schema...');
        $orgSchema = $this->schemaService->getOrganizationSchema();
        $orgValidation = $this->validateSchema($orgSchema, 'Organization');
        $errors = array_merge($errors, $orgValidation['errors']);
        $warnings = array_merge($warnings, $orgValidation['warnings']);

        // Validate WebSite schema
        $this->info('Validating WebSite schema...');
        $siteSchema = $this->schemaService->getWebSiteSchema();
        $siteValidation = $this->validateSchema($siteSchema, 'WebSite');
        $errors = array_merge($errors, $siteValidation['errors']);
        $warnings = array_merge($warnings, $siteValidation['warnings']);

        // Validate sample pages
        $this->info('Validating page schemas...');
        $pages = Page::where('is_published', true)->limit(5)->get();
        foreach ($pages as $page) {
            $pageSchema = $this->schemaService->getWebPageSchema($page);
            $pageValidation = $this->validateSchema($pageSchema, "WebPage: {$page->slug}");
            $errors = array_merge($errors, $pageValidation['errors']);
            $warnings = array_merge($warnings, $pageValidation['warnings']);
        }

        // Validate sample blog posts
        $this->info('Validating article schemas...');
        $posts = BlogPost::where('is_published', true)->limit(5)->get();
        foreach ($posts as $post) {
            $articleSchema = $this->schemaService->getArticleSchema($post);
            $articleValidation = $this->validateSchema($articleSchema, "Article: {$post->slug}");
            $errors = array_merge($errors, $articleValidation['errors']);
            $warnings = array_merge($warnings, $articleValidation['warnings']);
        }

        // Display results
        if (empty($errors) && empty($warnings)) {
            $this->info('✓ All schemas are valid!');

            return 0;
        }

        if (! empty($errors)) {
            $this->error('Errors found:');
            foreach ($errors as $error) {
                $this->line("  ✗ {$error}");
            }
            $this->newLine();
        }

        if (! empty($warnings)) {
            $this->warn('Warnings:');
            foreach ($warnings as $warning) {
                $this->line("  ⚠ {$warning}");
            }
        }

        return empty($errors) ? 0 : 1;
    }

    protected function validateSchema(array $schema, string $type): array
    {
        $errors = [];
        $warnings = [];

        // Check required @context
        if (! isset($schema['@context']) || $schema['@context'] !== 'https://schema.org') {
            $errors[] = "{$type}: Missing or invalid @context";
        }

        // Check required @type
        if (! isset($schema['@type'])) {
            $errors[] = "{$type}: Missing @type";
        }

        // Type-specific validation
        switch ($schema['@type'] ?? '') {
            case 'Organization':
                if (! isset($schema['name'])) {
                    $errors[] = "{$type}: Missing required field 'name'";
                }
                if (! isset($schema['url'])) {
                    $errors[] = "{$type}: Missing required field 'url'";
                }
                break;

            case 'WebSite':
                if (! isset($schema['name'])) {
                    $errors[] = "{$type}: Missing required field 'name'";
                }
                if (! isset($schema['url'])) {
                    $errors[] = "{$type}: Missing required field 'url'";
                }
                break;

            case 'WebPage':
                if (! isset($schema['name'])) {
                    $errors[] = "{$type}: Missing required field 'name'";
                }
                if (! isset($schema['url'])) {
                    $errors[] = "{$type}: Missing required field 'url'";
                }
                break;

            case 'Article':
                if (! isset($schema['headline'])) {
                    $errors[] = "{$type}: Missing required field 'headline'";
                }
                if (! isset($schema['datePublished'])) {
                    $errors[] = "{$type}: Missing required field 'datePublished'";
                }
                if (! isset($schema['author'])) {
                    $errors[] = "{$type}: Missing required field 'author'";
                }
                if (! isset($schema['publisher'])) {
                    $errors[] = "{$type}: Missing required field 'publisher'";
                }
                break;

            case 'BreadcrumbList':
                if (! isset($schema['itemListElement']) || empty($schema['itemListElement'])) {
                    $errors[] = "{$type}: Missing or empty 'itemListElement'";
                }
                break;
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }
}
