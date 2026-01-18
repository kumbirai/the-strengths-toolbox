<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SearchService
{
    protected int $cacheTtl = 3600; // 1 hour

    /**
     * Perform site-wide search
     */
    public function search(string $query, int $perPage = 10): array
    {
        $query = trim($query);

        if (strlen($query) < 2) {
            return [
                'pages' => collect(),
                'posts' => collect(),
                'total' => 0,
            ];
        }

        $cacheKey = "search.{$query}.{$perPage}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($query, $perPage) {
            return [
                'pages' => $this->searchPages($query, $perPage),
                'posts' => $this->searchBlogPosts($query, $perPage),
                'total' => $this->getTotalCount($query),
            ];
        });
    }

    /**
     * Search pages
     */
    protected function searchPages(string $query, int $perPage): Collection
    {
        $pages = Page::where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->orderByRaw('
                CASE 
                    WHEN title LIKE ? THEN 1
                    WHEN excerpt LIKE ? THEN 2
                    WHEN content LIKE ? THEN 3
                    ELSE 4
                END
            ', ["%{$query}%", "%{$query}%", "%{$query}%"])
            ->limit($perPage)
            ->get()
            ->map(function ($page) use ($query) {
                return [
                    'type' => 'page',
                    'id' => $page->id,
                    'title' => $this->highlightQuery($page->title, $query),
                    'excerpt' => $this->extractExcerpt($page->content, $query),
                    'url' => route('pages.show', $page->slug),
                    'slug' => $page->slug,
                    'relevance' => $this->calculateRelevance($page, $query),
                ];
            });

        return $pages->sortByDesc('relevance')->values();
    }

    /**
     * Search blog posts
     */
    protected function searchBlogPosts(string $query, int $perPage): Collection
    {
        $posts = BlogPost::where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->orderByRaw('
                CASE 
                    WHEN title LIKE ? THEN 1
                    WHEN excerpt LIKE ? THEN 2
                    WHEN content LIKE ? THEN 3
                    ELSE 4
                END
            ', ["%{$query}%", "%{$query}%", "%{$query}%"])
            ->orderBy('published_at', 'desc')
            ->limit($perPage)
            ->get()
            ->map(function ($post) use ($query) {
                return [
                    'type' => 'blog_post',
                    'id' => $post->id,
                    'title' => $this->highlightQuery($post->title, $query),
                    'excerpt' => $this->extractExcerpt($post->content, $query),
                    'url' => route('blog.show', $post->slug),
                    'slug' => $post->slug,
                    'published_at' => $post->published_at,
                    'relevance' => $this->calculatePostRelevance($post, $query),
                ];
            });

        return $posts->sortByDesc('relevance')->values();
    }

    /**
     * Get total count of results
     */
    protected function getTotalCount(string $query): int
    {
        $pageCount = Page::where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->count();

        $postCount = BlogPost::where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->count();

        return $pageCount + $postCount;
    }

    /**
     * Extract excerpt with highlighted query
     */
    protected function extractExcerpt(string $content, string $query, int $length = 200): string
    {
        $content = strip_tags($content);
        $content = preg_replace('/\s+/', ' ', $content);

        $position = stripos($content, $query);

        if ($position === false) {
            return substr($content, 0, $length).'...';
        }

        $start = max(0, $position - 50);
        $excerpt = substr($content, $start, $length);

        // Highlight query in excerpt
        $excerpt = preg_replace('/('.preg_quote($query, '/').')/i', '<mark>$1</mark>', $excerpt);

        if ($start > 0) {
            $excerpt = '...'.$excerpt;
        }

        if (strlen($content) > $start + $length) {
            $excerpt .= '...';
        }

        return $excerpt;
    }

    /**
     * Highlight query in text
     */
    protected function highlightQuery(string $text, string $query): string
    {
        return preg_replace('/('.preg_quote($query, '/').')/i', '<mark>$1</mark>', $text);
    }

    /**
     * Calculate relevance score for page
     */
    protected function calculateRelevance(Page $page, string $query): int
    {
        $relevance = 0;
        $queryLower = strtolower($query);

        // Title match (highest weight)
        if (stripos($page->title, $query) !== false) {
            $relevance += 10;
        }

        // Exact title match
        if (strtolower($page->title) === $queryLower) {
            $relevance += 20;
        }

        // Excerpt match
        if (stripos($page->excerpt ?? '', $query) !== false) {
            $relevance += 5;
        }

        // Content match
        if (stripos($page->content, $query) !== false) {
            $relevance += 1;
        }

        return $relevance;
    }

    /**
     * Calculate relevance score for blog post
     */
    protected function calculatePostRelevance(BlogPost $post, string $query): int
    {
        $relevance = 0;
        $queryLower = strtolower($query);

        // Title match (highest weight)
        if (stripos($post->title, $query) !== false) {
            $relevance += 10;
        }

        // Exact title match
        if (strtolower($post->title) === $queryLower) {
            $relevance += 20;
        }

        // Excerpt match
        if (stripos($post->excerpt ?? '', $query) !== false) {
            $relevance += 5;
        }

        // Content match
        if (stripos($post->content, $query) !== false) {
            $relevance += 1;
        }

        // Recent posts get slight boost
        if ($post->published_at && $post->published_at->isAfter(now()->subMonths(6))) {
            $relevance += 2;
        }

        return $relevance;
    }

    /**
     * Get popular searches
     */
    public function getPopularSearches(int $limit = 5): Collection
    {
        // Could implement search analytics here
        // For now, return predefined popular searches
        return collect([
            'strengths-based development',
            'team building',
            'sales training',
            'leadership development',
            'strengths programme',
        ])->take($limit);
    }
}
