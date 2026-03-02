<?php

namespace App\Helpers;

use App\Models\Page;

class NavigationHelper
{
    public static function getStrengthsBasedDevelopmentItems(): array
    {
        $pages = Page::where('slug', 'like', 'strengths-based-development/%')
            ->where('is_published', true)
            ->orderBy('title')
            ->get();

        return $pages->map(function ($page) {
            return [
                'label' => $page->title,
                'url' => route('pages.show', $page->slug),
            ];
        })->toArray();
    }

    public static function getSalesCoursesItems(): array
    {
        $pages = Page::where('slug', 'like', 'sales-courses/%')
            ->where('is_published', true)
            ->orderBy('title')
            ->get();

        return $pages->map(function ($page) {
            return [
                'label' => $page->title,
                'url' => route('pages.show', $page->slug),
            ];
        })->toArray();
    }

    public static function getFacilitationItems(): array
    {
        $pages = Page::where('slug', 'like', 'facilitation/%')
            ->where('is_published', true)
            ->orderBy('title')
            ->get();

        return $pages->map(function ($page) {
            return [
                'label' => $page->title,
                'url' => route('pages.show', $page->slug),
            ];
        })->toArray();
    }
}
