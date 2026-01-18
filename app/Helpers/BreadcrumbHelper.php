<?php

namespace App\Helpers;

class BreadcrumbHelper
{
    /**
     * Generate breadcrumb items for a page
     */
    public static function generate(string $currentPageTitle, ?string $currentPageUrl = null): array
    {
        $items = [
            [
                'name' => 'Home',
                'url' => url('/'),
            ],
        ];

        // Add current page
        $items[] = [
            'name' => $currentPageTitle,
            'url' => $currentPageUrl ?? url()->current(),
        ];

        return $items;
    }

    /**
     * Generate breadcrumb items for blog post
     */
    public static function generateForPost(
        string $postTitle,
        string $postUrl,
        ?string $categoryName = null,
        ?string $categoryUrl = null
    ): array {
        $items = [
            [
                'name' => 'Home',
                'url' => url('/'),
            ],
            [
                'name' => 'Blog',
                'url' => route('blog.index'),
            ],
        ];

        // Add category if available
        if ($categoryName && $categoryUrl) {
            $items[] = [
                'name' => $categoryName,
                'url' => $categoryUrl,
            ];
        }

        // Add current post
        $items[] = [
            'name' => $postTitle,
            'url' => $postUrl,
        ];

        return $items;
    }
}
