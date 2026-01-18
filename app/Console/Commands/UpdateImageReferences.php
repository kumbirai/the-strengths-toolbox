<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class UpdateImageReferences extends Command
{
    protected $signature = 'content:update-image-references 
                            {--dry-run : Preview changes without updating}';

    protected $description = 'Update image references in content to point to media library';

    protected $mapping = [];

    protected $updated = 0;

    public function handle(): int
    {
        $this->loadImageMapping();

        $this->info('Updating image references...');
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        $this->newLine();

        $pagesUpdated = $this->updatePageImages();
        $postsUpdated = $this->updateBlogPostImages();

        $this->newLine();
        $this->info("Pages updated: {$pagesUpdated}");
        $this->info("Blog posts updated: {$postsUpdated}");

        return Command::SUCCESS;
    }

    protected function loadImageMapping(): void
    {
        $mappingFile = base_path('content-migration/images/media-library-mapping.json');

        if (file_exists($mappingFile)) {
            $this->mapping = json_decode(file_get_contents($mappingFile), true) ?? [];
        }
    }

    protected function updatePageImages(): int
    {
        $pages = Page::all();
        $updated = 0;

        foreach ($pages as $page) {
            $originalContent = $page->content;
            $updatedContent = $this->replaceImageReferences($originalContent);

            if ($originalContent !== $updatedContent) {
                if (! $this->option('dry-run')) {
                    $page->content = $updatedContent;
                    $page->save();
                }
                $updated++;
                $this->line("  ✓ Updated: {$page->title}");
            }
        }

        return $updated;
    }

    protected function updateBlogPostImages(): int
    {
        $posts = BlogPost::all();
        $updated = 0;

        foreach ($posts as $post) {
            $originalContent = $post->content;
            $updatedContent = $this->replaceImageReferences($originalContent);

            if ($originalContent !== $updatedContent) {
                if (! $this->option('dry-run')) {
                    $post->content = $updatedContent;
                    $post->save();
                }
                $updated++;
                $this->line("  ✓ Updated: {$post->title}");
            }
        }

        return $updated;
    }

    protected function replaceImageReferences(string $content): string
    {
        // Find all img tags
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);

        foreach ($matches[1] as $oldUrl) {
            $newUrl = $this->findNewImageUrl($oldUrl);

            if ($newUrl) {
                $content = str_replace($oldUrl, $newUrl, $content);
            }
        }

        // Also handle markdown image syntax: ![alt](url)
        preg_match_all('/!\[([^\]]*)\]\(([^\)]+)\)/', $content, $matches);

        foreach ($matches[2] as $index => $oldUrl) {
            $newUrl = $this->findNewImageUrl($oldUrl);

            if ($newUrl) {
                $altText = $matches[1][$index] ?? '';
                $content = str_replace(
                    "![{$altText}]({$oldUrl})",
                    "![{$altText}]({$newUrl})",
                    $content
                );
            }
        }

        return $content;
    }

    protected function findNewImageUrl(string $oldUrl): ?string
    {
        // Try to find in mapping
        foreach ($this->mapping as $oldPath => $data) {
            if (isset($data['url']) && (strpos($oldUrl, $oldPath) !== false || strpos($oldUrl, basename($oldPath)) !== false)) {
                return $data['url'];
            }
        }

        // Try to find by filename in media library
        $filename = basename($oldUrl);
        $media = Media::where('filename', $filename)->first();

        if ($media) {
            return Storage::disk('public')->url($media->path);
        }

        // Try to find by partial filename match
        $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        $media = Media::where('filename', 'like', $filenameWithoutExt.'%')->first();

        if ($media) {
            return Storage::disk('public')->url($media->path);
        }

        return null;
    }
}
