<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssignBlogImagesFromFolder extends Command
{
    protected $signature = 'blog:assign-images-from-folder {--dry-run : Preview without making changes}';

    protected $description = 'Assign featured images to blog posts by matching slugs with files in storage/app/public/blog/';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info('Assigning featured images from blog folder...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        $this->newLine();

        $blogFolder = storage_path('app/public/blog');
        if (! File::exists($blogFolder)) {
            $this->error("Blog folder not found: {$blogFolder}");

            return Command::FAILURE;
        }

        // Get all image files in blog folder
        $imageFiles = File::files($blogFolder);
        $imageMap = [];

        foreach ($imageFiles as $file) {
            $filename = $file->getFilename();
            // Extract slug from filename (e.g., "blog-harnessing-your-unique-strengths.webp" -> "harnessing-your-unique-strengths")
            if (preg_match('/^blog-(.+)\.(jpg|jpeg|png|webp|gif)$/i', $filename, $matches)) {
                $slug = $matches[1];
                $imageMap[$slug] = 'blog/'.$filename;
            }
        }

        $this->info('Found '.count($imageMap).' images in blog folder');
        $this->newLine();

        $blogPosts = BlogPost::all();
        $assigned = 0;
        $skipped = 0;
        $errors = 0;
        $missing = [];

        foreach ($blogPosts as $post) {
            // Skip if already has an image that exists
            if ($post->featured_image) {
                $filePath = storage_path('app/public/'.$post->featured_image);
                if (file_exists($filePath)) {
                    $skipped++;
                    continue;
                } else {
                    $this->warn("  ⊘ File missing for existing image: {$post->title}");
                }
            }

            // Try to find matching image by slug
            $slug = $post->slug;
            $imagePath = null;

            // Direct slug match
            if (isset($imageMap[$slug])) {
                $imagePath = $imageMap[$slug];
            } else {
                // Try partial matches
                foreach ($imageMap as $imageSlug => $path) {
                    if (str_contains($slug, $imageSlug) || str_contains($imageSlug, $slug)) {
                        $imagePath = $path;
                        break;
                    }
                }
            }

            if ($imagePath) {
                $filePath = storage_path('app/public/'.$imagePath);
                if (file_exists($filePath)) {
                    if ($dryRun) {
                        $this->line("  Would assign: {$imagePath} → {$post->title}");
                    } else {
                        try {
                            $post->featured_image = $imagePath;
                            $post->save();
                            $this->line("  ✓ Assigned: {$imagePath} → {$post->title}");
                            $assigned++;
                        } catch (\Exception $e) {
                            $this->error("  ✗ Error assigning to {$post->title}: ".$e->getMessage());
                            $errors++;
                        }
                    }
                } else {
                    $this->warn("  ⊘ Image file not found: {$imagePath}");
                    $errors++;
                }
            } else {
                $missing[] = $post->slug.' - '.$post->title;
                $this->warn("  ⊘ No matching image found for: {$post->title} (slug: {$slug})");
            }
        }

        $this->newLine();
        $this->info('Assignment Summary:');
        $this->line("  Assigned: {$assigned}");
        $this->line("  Skipped: {$skipped}");
        if ($errors > 0) {
            $this->error("  Errors: {$errors}");
        }
        if (count($missing) > 0) {
            $this->warn("  Missing images: ".count($missing));
            $this->newLine();
            $this->comment('Attempting to download missing images from TSA inventory...');
            $this->newLine();

            // Try to download missing images from TSA inventory
            $inventoryPath = base_path('content-migration/tsa-blog-inventory.json');
            if (file_exists($inventoryPath)) {
                $inventory = json_decode(file_get_contents($inventoryPath), true);
                $inventoryMap = [];
                foreach ($inventory as $item) {
                    if (isset($item['slug']) && isset($item['featured_image_url'])) {
                        $inventoryMap[$item['slug']] = $item['featured_image_url'];
                    }
                }

                $disk = Storage::disk('public');
                foreach ($missing as $item) {
                    $parts = explode(' - ', $item, 2);
                    $slug = $parts[0];
                    $title = $parts[1] ?? '';

                    $post = BlogPost::where('slug', $slug)->first();
                    if (! $post) {
                        continue;
                    }

                    if (isset($inventoryMap[$slug]) && ! empty($inventoryMap[$slug])) {
                        $imageUrl = $inventoryMap[$slug];
                        $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                        $ext = strtolower($ext) === 'jpeg' ? 'jpg' : strtolower($ext);
                        $filename = 'blog-'.preg_replace('/[^a-z0-9-]/', '-', strtolower($slug)).'.'.$ext;
                        $relativePath = 'blog/'.$filename;

                        if ($dryRun) {
                            $this->line("  Would download: {$imageUrl}");
                            $this->line("  Would assign to: {$title} ({$relativePath})");
                        } else {
                            try {
                                $response = Http::timeout(30)->get($imageUrl);
                                if ($response->successful()) {
                                    $disk->put($relativePath, $response->body());
                                    $post->featured_image = $relativePath;
                                    $post->save();
                                    $this->line("  ✓ Downloaded and assigned: {$relativePath} → {$title}");
                                    $assigned++;
                                } else {
                                    $this->warn("  ⊘ HTTP {$response->status()} for {$slug}");
                                    $errors++;
                                }
                            } catch (\Throwable $e) {
                                $this->error("  ✗ Error downloading for {$slug}: ".$e->getMessage());
                                $errors++;
                            }
                        }
                    } else {
                        $this->warn("  ⊘ No image URL in inventory for: {$title} (slug: {$slug})");
                    }
                }
            } else {
                $this->warn('TSA inventory file not found. Cannot download missing images.');
                $this->newLine();
                $this->comment('Blog posts without images:');
                foreach ($missing as $item) {
                    $this->line("    - {$item}");
                }
            }
        }

        $this->newLine();
        $this->info('Final Summary:');
        $this->line("  Assigned: {$assigned}");
        $this->line("  Skipped: {$skipped}");
        if ($errors > 0) {
            $this->error("  Errors: {$errors}");
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
