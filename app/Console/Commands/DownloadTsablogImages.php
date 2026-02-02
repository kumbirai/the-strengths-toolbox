<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Download TSA blog featured images from image-mapping.json source_url
 * and assign them to the matching blog posts (featured_image path).
 */
class DownloadTsablogImages extends Command
{
    protected $signature = 'blog:download-tsa-images
                            {--mapping=content-migration/images/image-mapping.json : Path to image mapping file}
                            {--dry-run : Preview without downloading or assigning}';

    protected $description = 'Download TSA blog images from image-mapping source_url and assign to blog posts';

    public function handle(): int
    {
        $mappingPath = $this->option('mapping');
        $dryRun = $this->option('dry-run');

        $fullPath = str_starts_with($mappingPath, '/') ? $mappingPath : base_path($mappingPath);

        if (! file_exists($fullPath)) {
            $this->error("Image mapping file not found: {$fullPath}");

            return Command::FAILURE;
        }

        $data = json_decode(file_get_contents($fullPath), true);

        if (! isset($data['images'])) {
            $this->error("Invalid mapping file structure. Expected 'images' key.");

            return Command::FAILURE;
        }

        $this->info('Downloading TSA blog images and assigning to posts...');
        if ($dryRun) {
            $this->warn('DRY RUN – no files will be written or posts updated.');
        }
        $this->newLine();

        $storageDir = 'blog';
        $disk = Storage::disk('public');

        if (! $dryRun && ! $disk->exists($storageDir)) {
            $disk->makeDirectory($storageDir);
        }

        $assigned = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($data['images'] as $originalPath => $imageData) {
            $sourceUrl = $imageData['source_url'] ?? null;
            $blogPostSlug = $imageData['blog_post_slug'] ?? null;
            $newFilename = $imageData['new_filename'] ?? null;

            if (! $sourceUrl || ! $blogPostSlug || ! $newFilename) {
                continue;
            }

            $post = BlogPost::where('slug', $blogPostSlug)->first();

            if (! $post) {
                $this->warn("  ⊘ Post not found: {$blogPostSlug}");
                $skipped++;

                continue;
            }

            $relativePath = $storageDir.'/'.$newFilename;

            if ($dryRun) {
                $this->line("  Would download: {$sourceUrl}");
                $this->line("  Would assign to: {$post->title} ({$relativePath})");
                $assigned++;

                continue;
            }

            try {
                $response = Http::timeout(30)->get($sourceUrl);

                if (! $response->successful()) {
                    $this->warn("  ⊘ HTTP {$response->status()} for {$blogPostSlug}");
                    $errors++;

                    continue;
                }

                $disk->put($relativePath, $response->body());

                $post->featured_image = $relativePath;
                $post->save();

                $this->line("  ✓ Assigned: {$newFilename} → {$post->title}");
                $assigned++;
            } catch (\Throwable $e) {
                $this->error("  ✗ {$blogPostSlug}: ".$e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->info('Summary:');
        $this->line("  Assigned: {$assigned}");
        $this->line("  Skipped: {$skipped}");
        if ($errors > 0) {
            $this->error("  Errors: {$errors}");
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
