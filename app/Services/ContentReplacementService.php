<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Service for replacing content in blog posts
 * Handles brand names, URLs, emails, and image URLs
 */
class ContentReplacementService
{
    /**
     * Process content with all replacements
     */
    public function processContent(string $content, array $imageUrlMap = []): string
    {
        $content = $this->replaceBrandNames($content);
        $content = $this->replaceUrls($content);
        $content = $this->replaceEmails($content);
        $content = $this->replaceLocalhostHrefs($content);
        $content = $this->removeFigureTagsWithSpecificImages($content);
        
        if (! empty($imageUrlMap)) {
            $content = $this->replaceImageUrls($content, $imageUrlMap);
        }

        return $content;
    }

    /**
     * Replace brand names
     */
    public function replaceBrandNames(string $content): string
    {
        // Replace TSA Business School with The Strengths Toolbox (case-insensitive)
        $content = preg_replace(
            '/TSA Business School/i',
            'The Strengths Toolbox',
            $content
        );

        return $content;
    }

    /**
     * Replace URLs
     */
    public function replaceUrls(string $content): string
    {
        $homeUrl = Route::has('home') ? route('home') : '/';
        $bookingUrl = Route::has('booking') ? route('booking') : '/booking';

        // Replace "Contact Eberhard <a href="https://tsabusinessschool.co.za/contact/">TODAY</a>" with booking route
        // Handles variations with or without www, and with optional spacing/br tags
        $content = preg_replace(
            '/(Contact Eberhard\s*(?:<br\s*\/?>)?\s*<a\s+href=["\'])https?:\/\/(www\.)?tsabusinessschool\.co\.za\/contact\/(["\']>TODAY<\/a>)/i',
            '$1'.$bookingUrl.'$3',
            $content
        );

        // Replace thestrengthstoolbox.com/calendar/ URLs with booking route
        $content = preg_replace(
            '#https?://(www\.)?thestrengthstoolbox\.com/calendar/?[^\s"\'<>]*#i',
            $bookingUrl,
            $content
        );

        // Replace relative /calendar/ URLs with booking route
        $content = preg_replace(
            '#(?<![a-zA-Z0-9])/calendar/?[^\s"\'<>]*#i',
            $bookingUrl,
            $content
        );

        // Replace tsabusinessschool.co.za URLs with homepage
        $content = preg_replace(
            '#https?://(www\.)?tsabusinessschool\.co\.za[^\s"\'<>]*#i',
            $homeUrl,
            $content
        );

        // Replace http://www.tsabusinessschool.co.za with homepage
        $content = preg_replace(
            '#http://www\.tsabusinessschool\.co\.za[^\s"\'<>]*#i',
            $homeUrl,
            $content
        );

        return $content;
    }

    /**
     * Replace email addresses
     */
    public function replaceEmails(string $content): string
    {
        // Replace specific email
        $content = preg_replace(
            '/eberhard@thesuccessacademy\.co\.za/i',
            'welcome@eberhardniklaus.co.za',
            $content
        );

        // Remove all other email addresses (except the allowed one)
        // This regex matches email addresses but excludes the allowed one
        $content = preg_replace_callback(
            '/\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}\b/',
            function ($matches) {
                $email = $matches[0];
                // Keep the allowed email
                if (strtolower($email) === 'welcome@eberhardniklaus.co.za') {
                    return $email;
                }
                // Remove all others
                return '';
            },
            $content
        );

        // Clean up any double spaces or punctuation left behind
        $content = preg_replace('/\s{2,}/', ' ', $content);
        $content = preg_replace('/\s+([.,;:!?])/', '$1', $content);

        return $content;
    }

    /**
     * Replace localhost:8000 hrefs with appropriate routes
     * Defaults to /booking when ambiguous
     */
    public function replaceLocalhostHrefs(string $content): string
    {
        $bookingUrl = Route::has('booking') ? route('booking') : '/booking';

        // Replace href="https://localhost:8000" and href="https://localhost:8000/..." patterns
        // Default to /booking route when ambiguous
        $content = preg_replace(
            '/href=["\']https?:\/\/localhost:8000[^"\']*["\']/i',
            'href="'.$bookingUrl.'"',
            $content
        );

        return $content;
    }

    /**
     * Replace image URLs with local paths
     */
    public function replaceImageUrls(string $content, array $urlMap): string
    {
        // Replace in src attributes
        foreach ($urlMap as $originalUrl => $localPath) {
            // Escape special regex characters in URL
            $escapedUrl = preg_quote($originalUrl, '/');
            
            // Determine the correct asset URL based on path format
            $assetUrl = $this->getImageAssetUrl($localPath);
            
            // Replace in src attributes
            $content = preg_replace(
                '/(<img[^>]+src=["\'])'.$escapedUrl.'(["\'])/i',
                '$1'.$assetUrl.'$2',
                $content
            );

            // Replace in srcset attributes
            $content = preg_replace_callback(
                '/(<img[^>]+srcset=["\'])([^"\']*?)'.$escapedUrl.'([^"\']*?)(["\'])/i',
                function ($matches) use ($assetUrl) {
                    return $matches[1].str_replace($matches[0], $assetUrl, $matches[0]).$matches[4];
                },
                $content
            );

            // Replace standalone URLs in content
            $content = str_replace($originalUrl, $assetUrl, $content);
        }

        return $content;
    }

    /**
     * Get the correct asset URL for an image path
     * Returns relative path (not absolute URL) to avoid double prefix issues
     */
    protected function getImageAssetUrl(string $localPath): string
    {
        // Remove any double prefix first
        if (str_starts_with($localPath, 'images/images/')) {
            $localPath = substr($localPath, 7); // Remove 'images/'
        }

        // If path already starts with images/blog/, return it as-is (relative path)
        // The view will handle it correctly
        if (str_starts_with($localPath, 'images/blog/')) {
            return '/'.$localPath; // Return absolute path starting with /
        }

        // If path starts with blog/, it's in storage - return storage path
        if (str_starts_with($localPath, 'blog/')) {
            return '/storage/'.$localPath;
        }

        // If path already starts with /, return as-is
        if (str_starts_with($localPath, '/')) {
            // But still check for double prefix
            if (str_contains($localPath, '/images/images/')) {
                return str_replace('/images/images/', '/images/', $localPath);
            }
            return $localPath;
        }

        // Default: assume it's in storage
        return '/storage/'.$localPath;
    }

    /**
     * Remove figure tags containing office-rentals-in-pretoria or tsa-business-school images
     * Matches case-insensitive pattern: <figure[^>]*>.*?src=.*?(office-rentals-in-pretoria|tsa-business-school).*?</figure>
     */
    public function removeFigureTagsWithSpecificImages(string $content): string
    {
        // Remove figure tags containing office-rentals-in-pretoria or tsa-business-school images (case-insensitive)
        // Pattern matches: <figure[^>]*>.*?src=.*?(office-rentals-in-pretoria|tsa-business-school).*?</figure>
        $content = preg_replace(
            '/<figure[^>]*>.*?src=.*?(office-rentals-in-pretoria|tsa-business-school).*?<\/figure>/is',
            '',
            $content
        );

        return $content;
    }

    /**
     * Clean HTML content
     */
    public function cleanHtml(string $content): string
    {
        // Remove empty paragraphs
        $content = preg_replace('/<p[^>]*>\s*<\/p>/i', '', $content);
        
        // Remove empty divs
        $content = preg_replace('/<div[^>]*>\s*<\/div>/i', '', $content);

        // Clean up whitespace
        $content = preg_replace('/\s{3,}/', ' ', $content);

        return trim($content);
    }
}
