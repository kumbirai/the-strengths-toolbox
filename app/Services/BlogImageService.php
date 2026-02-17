<?php

namespace App\Services;

/**
 * Centralized service for resolving blog post images.
 * All blog images are stored in public/images/blog/ only.
 */
class BlogImageService
{
    protected const BLOG_IMAGE_DIR = 'images/blog';
    protected const BLOG_IMAGE_PATH = 'public/images/blog';

    /**
     * Find image by blog post slug
     */
    public function findBySlug(string $slug): ?string
    {
        $blogFolder = public_path(self::BLOG_IMAGE_DIR);
        if (!is_dir($blogFolder)) {
            return null;
        }

        $patterns = [
            "blog-{$slug}.*",
            "*{$slug}*",
            "*{$slug}-*",  // Match slug followed by dash
        ];

        foreach ($patterns as $pattern) {
            $files = glob($blogFolder.'/'.$pattern);
            if (!empty($files)) {
                // Prefer files with common image extensions
                $imageFiles = array_filter($files, function($file) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                });
                if (!empty($imageFiles)) {
                    return self::BLOG_IMAGE_DIR.'/'.basename($imageFiles[0]);
                }
                return self::BLOG_IMAGE_DIR.'/'.basename($files[0]);
            }
        }

        return null;
    }

    /**
     * Find image by filename (with timestamp/hash stripping)
     */
    public function findByFilename(string $filename): ?string
    {
        if (empty($filename) || $filename === 'placeholder-blog.svg') {
            return null;
        }

        $publicPath = public_path(self::BLOG_IMAGE_DIR.'/'.$filename);
        if (file_exists($publicPath)) {
            return self::BLOG_IMAGE_DIR.'/'.$filename;
        }

        // Try base filename (strip timestamp/hash)
        $baseFilename = $this->extractBaseFilename($filename);
        if ($baseFilename && $baseFilename !== $filename) {
            $publicBasePath = public_path(self::BLOG_IMAGE_DIR.'/'.$baseFilename);
            if (file_exists($publicBasePath)) {
                return self::BLOG_IMAGE_DIR.'/'.$baseFilename;
            }
        }

        return null;
    }

    /**
     * Resolve various URL formats to standardized path
     */
    public function resolveUrl(string $url, string $slug): ?string
    {
        // Skip placeholder images
        if (str_contains($url, 'placeholder-blog.svg')) {
            return null;
        }

        // Handle /storage/blog/... URLs
        if (preg_match('#/storage/(blog/.+)$#', parse_url($url, PHP_URL_PATH) ?? '', $matches)) {
            $extractedPath = $matches[1];
            $filename = basename($extractedPath);

            // Try exact match
            $publicPath = public_path(self::BLOG_IMAGE_DIR.'/'.$filename);
            if (file_exists($publicPath)) {
                return self::BLOG_IMAGE_DIR.'/'.$filename;
            }

            // Try base filename (strip timestamp/hash)
            $baseFilename = $this->extractBaseFilename($filename);
            if ($baseFilename && $baseFilename !== $filename) {
                $publicBasePath = public_path(self::BLOG_IMAGE_DIR.'/'.$baseFilename);
                if (file_exists($publicBasePath)) {
                    return self::BLOG_IMAGE_DIR.'/'.$baseFilename;
                }
            }

            // Fallback to slug-based lookup
            return $this->findBySlug($slug);
        }

        // Handle /images/blog/... URLs
        if (preg_match('#/images/blog/(.+)$#', parse_url($url, PHP_URL_PATH) ?? '', $matches)) {
            $path = self::BLOG_IMAGE_DIR.'/'.$matches[1];
            if (file_exists(public_path($path))) {
                return $path;
            }

            // Try just the filename
            $filename = basename($matches[1]);
            $result = $this->findByFilename($filename);
            if ($result) {
                return $result;
            }

            // Fallback to slug-based lookup
            return $this->findBySlug($slug);
        }

        // Handle relative paths (blog/... or images/blog/...)
        if (str_starts_with($url, 'blog/') || str_starts_with($url, 'images/blog/')) {
            $path = str_starts_with($url, 'blog/') ? self::BLOG_IMAGE_DIR.'/'.basename($url) : $url;
            if (file_exists(public_path($path))) {
                return $path;
            }

            // Fallback to slug-based lookup
            return $this->findBySlug($slug);
        }

        // Extract filename and try to find it
        $filename = basename(parse_url($url, PHP_URL_PATH) ?? '');
        if (!empty($filename)) {
            $result = $this->findByFilename($filename);
            if ($result) {
                return $result;
            }
        }

        // Final fallback to slug-based lookup
        return $this->findBySlug($slug);
    }

    /**
     * Normalize any path to images/blog/ format
     */
    public function getStandardPath(string $path): string
    {
        // Remove any double prefix first
        if (str_starts_with($path, 'images/images/')) {
            $path = substr($path, 7); // Remove 'images/'
        }

        // Already in correct format
        if (str_starts_with($path, 'images/blog/')) {
            return $path;
        }

        // Convert storage path (blog/...) to public path
        if (str_starts_with($path, 'blog/')) {
            return 'images/'.$path;
        }

        // If it's just a filename, add the directory
        if (!str_contains($path, '/')) {
            return self::BLOG_IMAGE_DIR.'/'.$path;
        }

        // Extract filename and standardize
        $filename = basename($path);
        return self::BLOG_IMAGE_DIR.'/'.$filename;
    }

    /**
     * Extract base filename by removing timestamp prefix and hash suffix
     * Example: 1770538660_filename_cFpNuhvK.webp -> filename.webp
     */
    protected function extractBaseFilename(string $filename): ?string
    {
        // Remove timestamp prefix (e.g., 1770538660_)
        $baseFilename = preg_replace('/^\d+_/', '', $filename);
        
        // Remove hash suffix (e.g., _cFpNuhvK.webp -> .webp)
        $baseFilename = preg_replace('/_[a-zA-Z0-9]+\.([^.]+)$/', '.$1', $baseFilename);
        
        return $baseFilename !== $filename ? $baseFilename : null;
    }

    /**
     * Find image by fuzzy matching keywords from slug
     */
    public function findByFuzzyMatch(string $slug): ?string
    {
        // Extract key words from slug (remove common words)
        $slugParts = explode('-', $slug);
        $keyWords = array_filter($slugParts, function($part) {
            return strlen($part) > 3 && !in_array($part, ['the', 'and', 'for', 'with', 'from', 'that', 'this']);
        });

        if (empty($keyWords)) {
            return null;
        }

        $blogFolder = public_path(self::BLOG_IMAGE_DIR);
        if (!is_dir($blogFolder)) {
            return null;
        }

        $files = glob($blogFolder.'/*') ?: [];
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $filename = strtolower(basename($file));
            $matches = 0;
            foreach ($keyWords as $word) {
                if (str_contains($filename, strtolower($word))) {
                    $matches++;
                }
            }

            // If at least 2 key words match, consider it a match
            if ($matches >= 2) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    return self::BLOG_IMAGE_DIR.'/'.basename($file);
                }
            }
        }

        return null;
    }
}
