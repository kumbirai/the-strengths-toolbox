<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\BlogPost;

/**
 * Migrate blog images from storage/app/public/blog/ to public/images/blog/
 * 
 * This command:
 * - Scans storage/app/public/blog/ for images
 * - Moves non-duplicate files to public/images/blog/
 * - Reports duplicates (keeps public version)
 * - Updates database records pointing to storage paths
 */
class MigrateBlogImagesToPublic extends Command
{
    protected $signature = 'blog:migrate-images-to-public
                            {--dry-run : Preview changes without making them}';

    protected $description = 'Migrate blog images from storage to public/images/blog/';

    protected int $moved = 0;
    protected int $skipped = 0;
    protected int $duplicates = 0;
    protected int $errors = 0;

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No files will be moved');
            $this->newLine();
        }

        $storageDir = storage_path('app/public/blog');
        $publicDir = public_path('images/blog');

        // Ensure public directory exists
        if (!File::exists($publicDir)) {
            File::makeDirectory($publicDir, 0755, true);
            $this->line("Created directory: {$publicDir}");
        }

        if (!File::isDirectory($storageDir)) {
            $this->warn("Storage directory does not exist: {$storageDir}");
            return self::SUCCESS;
        }

        $this->info("Scanning storage directory: {$storageDir}");
        $files = File::files($storageDir);

        if (empty($files)) {
            $this->info('No files found in storage directory.');
            return self::SUCCESS;
        }

        $this->info("Found " . count($files) . " file(s) to process");
        $this->newLine();

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $storagePath = $file->getPathname();
            $publicPath = $publicDir . '/' . $filename;

            // Check if file already exists in public
            if (File::exists($publicPath)) {
                $this->duplicates++;
                $bar->advance();
                continue;
            }

            // Move file
            try {
                if (!$dryRun) {
                    File::move($storagePath, $publicPath);
                }
                $this->moved++;
            } catch (\Exception $e) {
                $this->errors++;
                $this->error("Failed to move {$filename}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Migration Summary:');
        $this->line("  Moved: {$this->moved}");
        $this->line("  Skipped (duplicates): {$this->duplicates}");
        $this->line("  Errors: {$this->errors}");

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a dry run. Run without --dry-run to perform the migration.');
        } else {
            // Update database records
            $this->newLine();
            $this->info('Updating database records...');
            $this->updateDatabasePaths();
        }

        return self::SUCCESS;
    }

    protected function updateDatabasePaths(): void
    {
        $posts = BlogPost::where('featured_image', 'like', 'blog/%')
            ->where('featured_image', 'not like', 'images/blog/%')
            ->get();

        $updated = 0;
        foreach ($posts as $post) {
            $oldPath = $post->featured_image;
            $filename = basename($oldPath);
            $newPath = 'images/blog/' . $filename;

            // Verify file exists in new location
            if (file_exists(public_path($newPath))) {
                $post->featured_image = $newPath;
                $post->save();
                $updated++;
            }
        }

        $this->line("  Updated {$updated} database record(s)");
    }
}
