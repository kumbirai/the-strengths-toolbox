<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Download Sales Courses images from TSA to local storage (storage/app/public/sales-courses/).
 * Run after deployment or when setting up; ContentMigrationSeeder references these paths.
 */
class DownloadSalesCoursesImages extends Command
{
    protected $signature = 'content:download-sales-courses-images
                            {--dry-run : Preview without downloading}';

    protected $description = 'Download Sales Courses images from TSA to local storage';

    protected string $tsaBase = 'https://www.tsabusinessschool.co.za/wp-content/uploads/2025/08';

    /** @var array<string, string> source URL path (after base) => local filename */
    protected array $images = [
        'stacking-wooden-blocks-is-risk-creating-business-growth-ideas-scaled.jpg' => 'stacking-wooden-blocks.jpg',
        'person-plays-chess-closed-up-bar-chart-with-arrow-up-life-planning-concept-generative-ai-scaled.jpg' => 'person-plays-chess.jpg',
        'people-working-elegant-cozy-office-space-scaled.jpg' => 'people-working-office.jpg',
        'brain-writes-with-white-chalk-is-hand-draw-concept-scaled.jpg' => 'brain-mindset-concept.jpg',
        'ecommerce-black-woman-smartphone-warehouse-sales-shopping-checkout-with-credit-card-female-entrepreneur-stock-small-business-with-payment-orders-logistics-trade-online-scaled.jpg' => 'selling-on-phone.jpg',
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $disk = Storage::disk('public');
        $dir = 'sales-courses';

        $this->info('Downloading Sales Courses images to local storage...');
        if ($dryRun) {
            $this->warn('DRY RUN – no files will be written.');
        }
        $this->newLine();

        if (! $dryRun && ! $disk->exists($dir)) {
            $disk->makeDirectory($dir);
        }

        $downloaded = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($this->images as $sourcePath => $localFilename) {
            $url = $this->tsaBase.'/'.$sourcePath;
            $relativePath = $dir.'/'.$localFilename;

            if ($dryRun) {
                $this->line("  Would download: {$url} → {$relativePath}");
                $downloaded++;

                continue;
            }

            // Check if file already exists
            if ($disk->exists($relativePath)) {
                $this->line("  ⊘ Skipped (already exists): {$localFilename}");
                $skipped++;
                continue;
            }

            try {
                $response = Http::timeout(30)->get($url);

                if (! $response->successful()) {
                    $this->warn("  ⊘ HTTP {$response->status()}: {$localFilename}");
                    $errors++;

                    continue;
                }

                $disk->put($relativePath, $response->body());
                $this->line("  ✓ {$localFilename}");
                $downloaded++;
            } catch (\Throwable $e) {
                $this->error("  ✗ {$localFilename}: ".$e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->info("Downloaded: {$downloaded}");
        if ($skipped > 0) {
            $this->line("Skipped (already exists): {$skipped}");
        }
        if ($errors > 0) {
            $this->error("Errors: {$errors}");
        }
        if (! $dryRun && $downloaded > 0) {
            $this->line('Images are in storage/app/public/sales-courses/ (served at /storage/sales-courses/).');
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
