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
     * Replace image URLs with local paths
     */
    public function replaceImageUrls(string $content, array $urlMap): string
    {
        // Replace in src attributes
        foreach ($urlMap as $originalUrl => $localPath) {
            // Escape special regex characters in URL
            $escapedUrl = preg_quote($originalUrl, '/');
            
            // Replace in src attributes
            $content = preg_replace(
                '/(<img[^>]+src=["\'])'.$escapedUrl.'(["\'])/i',
                '$1'.asset('storage/'.$localPath).'$2',
                $content
            );

            // Replace in srcset attributes
            $content = preg_replace_callback(
                '/(<img[^>]+srcset=["\'])([^"\']*?)'.$escapedUrl.'([^"\']*?)(["\'])/i',
                function ($matches) use ($localPath) {
                    return $matches[1].str_replace($matches[0], asset('storage/'.$localPath), $matches[0]).$matches[4];
                },
                $content
            );

            // Replace standalone URLs in content
            $content = str_replace($originalUrl, asset('storage/'.$localPath), $content);
        }

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
