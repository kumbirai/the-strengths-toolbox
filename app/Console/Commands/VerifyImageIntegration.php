<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class VerifyImageIntegration extends Command
{
    protected $signature = 'images:verify-integration 
                            {--check-urls : Verify image URLs are accessible}';

    protected $description = 'Verify that all images are properly integrated into the application';

    protected array $issues = [];

    protected array $warnings = [];

    public function handle(): int
    {
        $checkUrls = $this->option('check-urls');

        $this->info('Verifying image integration...');
        $this->newLine();

        // Check 1: Media library has images
        $this->checkMediaLibrary();

        // Check 2: Eberhard's image exists
        $this->checkEberhardImage();

        // Check 3: Blog posts have featured images
        $this->checkBlogPostImages();

        // Check 4: Image files exist on disk
        $this->checkImageFiles();

        // Check 5: Alt text present
        $this->checkAltText();

        // Check 6: URLs accessible (optional)
        if ($checkUrls) {
            $this->checkImageUrls();
        }

        // Summary
        $this->displaySummary();

        return count($this->issues) > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    protected function checkMediaLibrary(): void
    {
        $this->line('Checking media library...');

        $totalMedia = Media::count();
        $imageMedia = Media::where('mime_type', 'like', 'image/%')->count();

        if ($totalMedia === 0) {
            $this->issues[] = 'No media files found in media library';
            $this->error('  ✗ No media files in library');
        } else {
            $this->line("  ✓ Found {$totalMedia} media files ({$imageMedia} images)");
        }

        $this->newLine();
    }

    protected function checkEberhardImage(): void
    {
        $this->line('Checking Eberhard\'s image...');

        $eberhardImage = Media::where(function ($query) {
            $query->where('filename', 'like', '%eberhard%')
                ->orWhere('original_filename', 'like', '%eberhard%')
                ->orWhere('alt_text', 'like', '%eberhard%');
        })->first();

        if (! $eberhardImage) {
            $this->issues[] = 'Eberhard\'s image not found in media library';
            $this->error('  ✗ Eberhard\'s image not found');
        } else {
            $this->line("  ✓ Found: {$eberhardImage->filename}");

            if (! $eberhardImage->alt_text) {
                $this->warnings[] = 'Eberhard\'s image missing alt text';
                $this->warn('  ⚠ Missing alt text');
            }

            // Check file exists
            if (! Storage::disk($eberhardImage->disk)->exists($eberhardImage->path)) {
                $this->issues[] = "Eberhard's image file not found on disk: {$eberhardImage->path}";
                $this->error("  ✗ File not found: {$eberhardImage->path}");
            } else {
                $this->line('  ✓ File exists on disk');
            }
        }

        $this->newLine();
    }

    protected function checkBlogPostImages(): void
    {
        $this->line('Checking blog post featured images...');

        $blogPosts = BlogPost::where('is_published', true)->get();
        $totalPosts = $blogPosts->count();
        $postsWithImages = $blogPosts->filter(fn ($post) => ! empty($post->featured_image))->count();
        $postsWithoutImages = $totalPosts - $postsWithImages;

        $this->line("  Total published posts: {$totalPosts}");
        $this->line("  Posts with featured images: {$postsWithImages}");

        if ($postsWithoutImages > 0) {
            $this->warnings[] = "{$postsWithoutImages} blog post(s) missing featured images";
            $this->warn("  ⚠ {$postsWithoutImages} post(s) without featured images:");

            foreach ($blogPosts as $post) {
                if (empty($post->featured_image)) {
                    $this->line("    - {$post->title} (slug: {$post->slug})");
                }
            }
        } else {
            $this->line('  ✓ All published posts have featured images');
        }

        // Verify featured image files exist
        $missingFiles = 0;
        foreach ($blogPosts as $post) {
            if ($post->featured_image) {
                if (! Storage::disk('public')->exists($post->featured_image)) {
                    $missingFiles++;
                    $this->issues[] = "Blog post '{$post->title}' featured image file not found: {$post->featured_image}";
                    $this->error("  ✗ Missing file for '{$post->title}': {$post->featured_image}");
                }
            }
        }

        if ($missingFiles === 0 && $postsWithImages > 0) {
            $this->line('  ✓ All featured image files exist');
        }

        $this->newLine();
    }

    protected function checkImageFiles(): void
    {
        $this->line('Checking image files on disk...');

        $mediaFiles = Media::where('mime_type', 'like', 'image/%')->get();
        $missingFiles = 0;
        $totalFiles = $mediaFiles->count();

        foreach ($mediaFiles as $media) {
            if (! Storage::disk($media->disk)->exists($media->path)) {
                $missingFiles++;
                $this->issues[] = "Media file not found: {$media->path} (ID: {$media->id})";
            }
        }

        if ($missingFiles > 0) {
            $this->error("  ✗ {$missingFiles} of {$totalFiles} image files missing on disk");
        } else {
            $this->line("  ✓ All {$totalFiles} image files exist on disk");
        }

        $this->newLine();
    }

    protected function checkAltText(): void
    {
        $this->line('Checking alt text...');

        $mediaWithoutAlt = Media::where('mime_type', 'like', 'image/%')
            ->whereNull('alt_text')
            ->orWhere('alt_text', '')
            ->get();

        $totalImages = Media::where('mime_type', 'like', 'image/%')->count();
        $imagesWithAlt = $totalImages - $mediaWithoutAlt->count();

        if ($mediaWithoutAlt->count() > 0) {
            $this->warnings[] = "{$mediaWithoutAlt->count()} image(s) missing alt text";
            $this->warn("  ⚠ {$mediaWithoutAlt->count()} of {$totalImages} images missing alt text");

            if ($this->output->isVerbose()) {
                foreach ($mediaWithoutAlt->take(10) as $media) {
                    $this->line("    - {$media->filename}");
                }
                if ($mediaWithoutAlt->count() > 10) {
                    $this->line('    ... and '.($mediaWithoutAlt->count() - 10).' more');
                }
            }
        } else {
            $this->line("  ✓ All {$totalImages} images have alt text");
        }

        $this->newLine();
    }

    protected function checkImageUrls(): void
    {
        $this->line('Checking image URL accessibility...');
        $this->warn('  This may take a while...');

        $mediaFiles = Media::where('mime_type', 'like', 'image/%')->take(20)->get();
        $totalChecked = 0;
        $inaccessible = 0;

        $bar = $this->output->createProgressBar($mediaFiles->count());
        $bar->start();

        foreach ($mediaFiles as $media) {
            try {
                $url = $media->url;
                $response = Http::timeout(5)->head($url);

                if (! $response->successful()) {
                    $inaccessible++;
                    $this->issues[] = "Image URL not accessible: {$url} (Status: {$response->status()})";
                }

                $totalChecked++;
            } catch (\Exception $e) {
                $inaccessible++;
                $this->issues[] = "Error checking image URL: {$media->url} - ".$e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($inaccessible > 0) {
            $this->error("  ✗ {$inaccessible} of {$totalChecked} image URLs not accessible");
        } else {
            $this->line("  ✓ All {$totalChecked} checked image URLs are accessible");
        }

        $this->newLine();
    }

    protected function displaySummary(): void
    {
        $this->newLine();
        $this->info('Verification Summary:');
        $this->newLine();

        if (count($this->issues) === 0 && count($this->warnings) === 0) {
            $this->info('  ✓ All checks passed! Images are properly integrated.');

            return;
        }

        if (count($this->issues) > 0) {
            $this->error('Issues found:');
            foreach ($this->issues as $issue) {
                $this->line("  ✗ {$issue}");
            }
            $this->newLine();
        }

        if (count($this->warnings) > 0) {
            $this->warn('Warnings:');
            foreach ($this->warnings as $warning) {
                $this->line("  ⚠ {$warning}");
            }
            $this->newLine();
        }

        $this->line('Recommendations:');
        $this->line('  1. Download images: php artisan images:download --source=tsa --url=https://www.tsabusinessschool.co.za');
        $this->line('  2. Download images: php artisan images:download --source=strengthstoolbox --url=https://www.thestrengthstoolbox.com');
        $this->line('  3. Optimize images: php artisan images:optimize --path=content-migration/images/original');
        $this->line('  4. Upload images: php artisan images:upload-migrated');
        $this->line('  5. Assign blog images: php artisan blog:assign-featured-images');
    }
}
