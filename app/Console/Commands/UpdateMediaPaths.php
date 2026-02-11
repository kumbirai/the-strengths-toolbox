<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Media;
use Illuminate\Console\Command;

class UpdateMediaPaths extends Command
{
    protected $signature = 'media:update-paths 
                            {--dry-run : Preview changes without updating}';

    protected $description = 'Update media paths to remove strengthstoolbox/tsa prefixes';

    public function handle(): int
    {
        $this->info('Updating media paths to remove strengthstoolbox/tsa prefixes...');
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        $this->newLine();

        $mediaUpdated = $this->updateMediaTable();
        $blogPostsUpdated = $this->updateBlogPostsTable();
        $contentUpdated = $this->updateContentFields();

        $this->newLine();
        $this->info('Summary:');
        $this->line("  Media records updated: {$mediaUpdated}");
        $this->line("  Blog posts (featured_image) updated: {$blogPostsUpdated}");
        $this->line("  Content fields updated: {$contentUpdated}");

        return Command::SUCCESS;
    }

    protected function updateMediaTable(): int
    {
        $this->info('Updating media table...');

        $updated = 0;
        $skipped = 0;

        // Update path column
        $mediaRecords = Media::where(function ($query) {
            $query->where('path', 'like', 'media/strengthstoolbox/%')
                ->orWhere('path', 'like', 'media/tsa/%');
        })->get();

        foreach ($mediaRecords as $media) {
            $oldPath = $media->path;
            $newPath = $this->removePrefix($oldPath);

            if ($oldPath !== $newPath) {
                if (! $this->option('dry-run')) {
                    $media->path = $newPath;
                    $media->save();
                }
                $this->line("  ✓ Updated: {$oldPath} → {$newPath}");
                $updated++;
            } else {
                $skipped++;
            }
        }

        // Update thumbnail_path column
        $thumbnailRecords = Media::where(function ($query) {
            $query->where('thumbnail_path', 'like', 'media/strengthstoolbox/%')
                ->orWhere('thumbnail_path', 'like', 'media/tsa/%');
        })->get();

        foreach ($thumbnailRecords as $media) {
            $oldPath = $media->thumbnail_path;
            $newPath = $this->removePrefix($oldPath);

            if ($oldPath !== $newPath) {
                if (! $this->option('dry-run')) {
                    $media->thumbnail_path = $newPath;
                    $media->save();
                }
                $this->line("  ✓ Updated thumbnail: {$oldPath} → {$newPath}");
                $updated++;
            } else {
                $skipped++;
            }
        }

        $this->line("  Media: {$updated} updated, {$skipped} skipped");

        return $updated;
    }

    protected function updateBlogPostsTable(): int
    {
        $this->info('Updating blog posts table...');

        $updated = 0;
        $skipped = 0;

        $blogPosts = BlogPost::where(function ($query) {
            $query->where('featured_image', 'like', 'media/strengthstoolbox/%')
                ->orWhere('featured_image', 'like', 'media/tsa/%');
        })->get();

        foreach ($blogPosts as $post) {
            $oldPath = $post->featured_image;
            $newPath = $this->removePrefix($oldPath);

            if ($oldPath !== $newPath) {
                if (! $this->option('dry-run')) {
                    $post->featured_image = $newPath;
                    $post->save();
                }
                $this->line("  ✓ Updated: {$post->title}");
                $this->line("    {$oldPath} → {$newPath}");
                $updated++;
            } else {
                $skipped++;
            }
        }

        $this->line("  Blog posts: {$updated} updated, {$skipped} skipped");

        return $updated;
    }

    protected function updateContentFields(): int
    {
        $this->info('Updating content fields (pages and blog posts)...');

        $updated = 0;
        $skipped = 0;

        // Update page content
        $pages = \App\Models\Page::all();
        foreach ($pages as $page) {
            $originalContent = $page->content;
            $updatedContent = $this->replacePathsInContent($originalContent);

            if ($originalContent !== $updatedContent) {
                if (! $this->option('dry-run')) {
                    $page->content = $updatedContent;
                    $page->save();
                }
                $this->line("  ✓ Updated page: {$page->title}");
                $updated++;
            } else {
                $skipped++;
            }
        }

        // Update blog post content
        $blogPosts = BlogPost::all();
        foreach ($blogPosts as $post) {
            $originalContent = $post->content;
            $updatedContent = $this->replacePathsInContent($originalContent);

            if ($originalContent !== $updatedContent) {
                if (! $this->option('dry-run')) {
                    $post->content = $updatedContent;
                    $post->save();
                }
                $this->line("  ✓ Updated post: {$post->title}");
                $updated++;
            } else {
                $skipped++;
            }
        }

        $this->line("  Content: {$updated} updated, {$skipped} skipped");

        return $updated;
    }

    protected function replacePathsInContent(string $content): string
    {
        // Replace paths in img src attributes
        $content = preg_replace(
            '#(src=["\'])(/storage/)?media/(strengthstoolbox|tsa)/#',
            '$1$2media/',
            $content
        );

        // Replace paths in href attributes
        $content = preg_replace(
            '#(href=["\'])(/storage/)?media/(strengthstoolbox|tsa)/#',
            '$1$2media/',
            $content
        );

        // Replace paths in background-image styles
        $content = preg_replace(
            '#(background-image:\s*url\(["\']?)(/storage/)?media/(strengthstoolbox|tsa)/#',
            '$1$2media/',
            $content
        );

        return $content;
    }

    protected function removePrefix(string $path): string
    {
        // Remove strengthstoolbox/ and tsa/ prefixes from media paths
        return preg_replace('#^media/(strengthstoolbox|tsa)/#', 'media/', $path);
    }
}
