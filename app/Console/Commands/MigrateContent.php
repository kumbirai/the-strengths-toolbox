<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Testimonial;
use Illuminate\Console\Command;

/**
 * Migrate content from existing sources
 */
class MigrateContent extends Command
{
    protected $signature = 'content:migrate 
                            {--source= : Source type (cms, csv, json)}
                            {--file= : Path to content file}
                            {--dry-run : Show what would be migrated without making changes}';

    protected $description = 'Migrate content from existing sources to CMS';

    public function handle(): int
    {
        $source = $this->option('source') ?? $this->choice(
            'Select content source:',
            ['seeder', 'csv', 'json', 'manual'],
            'seeder'
        );

        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        return match ($source) {
            'seeder' => $this->migrateFromSeeder($dryRun),
            'csv' => $this->migrateFromCsv($dryRun),
            'json' => $this->migrateFromJson($dryRun),
            'manual' => $this->showManualInstructions(),
            default => $this->error("Invalid source: {$source}"),
        };
    }

    protected function migrateFromSeeder(bool $dryRun): int
    {
        $this->info('Migrating content using seeders...');
        $this->newLine();

        if ($dryRun) {
            $this->info('Would run: php artisan db:seed --class=ContentMigrationSeeder');
            $this->info('Would run: php artisan db:seed --class=ProductionContentSeeder');

            return Command::SUCCESS;
        }

        $this->call('db:seed', ['--class' => 'ContentMigrationSeeder']);
        $this->call('db:seed', ['--class' => 'ProductionContentSeeder']);

        $this->newLine();
        $this->info('✓ Content migration completed!');
        $this->displayMigrationSummary();

        return Command::SUCCESS;
    }

    protected function migrateFromCsv(bool $dryRun): int
    {
        $file = $this->option('file');

        if (! $file || ! file_exists($file)) {
            $this->error('CSV file not found. Please provide --file option with path to CSV file.');

            return Command::FAILURE;
        }

        $this->info("Migrating content from CSV: {$file}");
        $this->newLine();

        // CSV migration logic would go here
        $this->warn('CSV migration not yet implemented. Use seeder method instead.');

        return Command::FAILURE;
    }

    protected function migrateFromJson(bool $dryRun): int
    {
        $file = $this->option('file');

        if (! $file || ! file_exists($file)) {
            $this->error('JSON file not found. Please provide --file option with path to JSON file.');

            return Command::FAILURE;
        }

        $this->info("Migrating content from JSON: {$file}");
        $this->newLine();

        $data = json_decode(file_get_contents($file), true);

        if (! $data) {
            $this->error('Invalid JSON file.');

            return Command::FAILURE;
        }

        if ($dryRun) {
            $this->info('Would migrate:');
            $this->displayJsonPreview($data);

            return Command::SUCCESS;
        }

        $migrated = 0;

        // Migrate pages
        if (isset($data['pages'])) {
            foreach ($data['pages'] as $pageData) {
                $page = Page::firstOrNew(['slug' => $pageData['slug']]);
                $page->fill($pageData);
                $page->save();
                $migrated++;
            }
        }

        // Migrate blog posts
        if (isset($data['blog_posts'])) {
            foreach ($data['blog_posts'] as $postData) {
                $post = BlogPost::firstOrNew(['slug' => $postData['slug']]);
                $post->fill($postData);
                $post->save();
                $migrated++;
            }
        }

        // Migrate testimonials
        if (isset($data['testimonials'])) {
            foreach ($data['testimonials'] as $testimonialData) {
                $testimonial = Testimonial::firstOrCreate(
                    [
                        'name' => $testimonialData['name'],
                        'company' => $testimonialData['company'] ?? null,
                    ],
                    $testimonialData
                );
                $migrated++;
            }
        }

        $this->info("✓ Migrated {$migrated} items from JSON file.");

        return Command::SUCCESS;
    }

    protected function showManualInstructions(): int
    {
        $this->info('Manual Content Migration Instructions');
        $this->newLine();
        $this->line('1. Use the admin panel to create pages manually');
        $this->line('2. Or use the seeder: php artisan db:seed --class=ContentMigrationSeeder');
        $this->line('3. Or import via JSON: php artisan content:migrate --source=json --file=path/to/content.json');
        $this->newLine();
        $this->line('For bulk content, use the ContentMigrationSeeder which creates all required pages.');

        return Command::SUCCESS;
    }

    protected function displayMigrationSummary(): void
    {
        $this->newLine();
        $this->info('Migration Summary:');
        $this->table(
            ['Type', 'Count'],
            [
                ['Pages', Page::count()],
                ['Blog Posts', BlogPost::count()],
                ['Testimonials', Testimonial::count()],
            ]
        );
    }

    protected function displayJsonPreview(array $data): void
    {
        if (isset($data['pages'])) {
            $this->line('Pages: '.count($data['pages']));
            foreach (array_slice($data['pages'], 0, 5) as $page) {
                $this->line("  - {$page['title']} ({$page['slug']})");
            }
        }

        if (isset($data['blog_posts'])) {
            $this->line('Blog Posts: '.count($data['blog_posts']));
        }

        if (isset($data['testimonials'])) {
            $this->line('Testimonials: '.count($data['testimonials']));
        }
    }
}
