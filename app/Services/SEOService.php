<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;

class SEOService
{
    protected int $cacheTtl;

    protected SchemaService $schemaService;

    public function __construct(SchemaService $schemaService)
    {
        $this->cacheTtl = config('cache.ttl.seo', 7200); // 2 hours
        $this->schemaService = $schemaService;
    }

    /**
     * Get SEO metadata for a page
     */
    public function getPageMeta(Page $page): array
    {
        return Cache::remember(
            "seo.page.{$page->id}",
            $this->cacheTtl,
            function () use ($page) {
                $seo = $page->seo;

                return [
                    'title' => $seo->og_title ?? $page->meta_title ?? $page->title,
                    'description' => $seo->og_description ?? $page->meta_description ?? $this->generateDescription($page->content),
                    'keywords' => $page->meta_keywords ?? '',
                    'canonical' => $seo->canonical_url ?? url('/'.$page->slug),
                    'og_title' => $seo->og_title ?? $page->meta_title ?? $page->title,
                    'og_description' => $seo->og_description ?? $page->meta_description ?? $this->generateDescription($page->content),
                    'og_image' => $seo->og_image ?? config('app.og_default_image'),
                    'og_type' => $seo->og_type ?? 'website',
                    'twitter_card' => $seo->twitter_card ?? 'summary_large_image',
                    'twitter_title' => $seo->og_title ?? $page->meta_title ?? $page->title,
                    'twitter_description' => $seo->og_description ?? $page->meta_description ?? $this->generateDescription($page->content),
                    'twitter_image' => $seo->og_image ?? config('app.og_default_image'),
                    'schema' => $this->schemaService->getWebPageSchema($page),
                ];
            }
        );
    }

    /**
     * Get SEO metadata for a blog post
     */
    public function getBlogPostMeta(BlogPost $post): array
    {
        return Cache::remember(
            "seo.blog_post.{$post->id}",
            $this->cacheTtl,
            function () use ($post) {
                $meta = [
                    'title' => $post->meta_title ?? $post->title,
                    'description' => $post->meta_description ?? $post->excerpt ?? $this->generateDescription($post->content),
                    'keywords' => $post->meta_keywords ?? '',
                    'canonical' => url('/blog/'.$post->slug),
                    'og_title' => $post->meta_title ?? $post->title,
                    'og_description' => $post->meta_description ?? $post->excerpt ?? $this->generateDescription($post->content),
                    'og_image' => $post->featured_image ? asset('storage/'.$post->featured_image) : config('app.og_default_image'),
                    'og_type' => 'article',
                    'og_url' => url('/blog/'.$post->slug),
                    'twitter_card' => 'summary_large_image',
                    'twitter_title' => $post->meta_title ?? $post->title,
                    'twitter_description' => $post->meta_description ?? $post->excerpt ?? $this->generateDescription($post->content),
                    'twitter_image' => $post->featured_image ? asset('storage/'.$post->featured_image) : config('app.og_default_image'),
                    'schema' => $this->schemaService->getArticleSchema($post),
                ];

                // Add article-specific meta tags
                if ($post->published_at) {
                    $meta['article_published_time'] = $post->published_at->toIso8601String();
                }
                if ($post->updated_at) {
                    $meta['article_modified_time'] = $post->updated_at->toIso8601String();
                }
                if ($post->author) {
                    $meta['article_author'] = $post->author->name;
                }
                if ($post->categories && $post->categories->isNotEmpty()) {
                    $meta['article_section'] = $post->categories->first()->name;
                }
                if ($post->tags && $post->tags->isNotEmpty()) {
                    $meta['article_tags'] = $post->tags->pluck('name')->toArray();
                }

                return $meta;
            }
        );
    }

    /**
     * Generate default SEO metadata
     */
    public function getDefaultMeta(): array
    {
        return [
            'title' => config('app.name').' - '.config('app.tagline', ''),
            'description' => config('app.description', ''),
            'keywords' => config('app.keywords', ''),
            'canonical' => url('/'),
            'og_title' => config('app.name'),
            'og_description' => config('app.description', ''),
            'og_image' => config('app.og_default_image'),
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => config('app.name'),
            'twitter_description' => config('app.description', ''),
            'twitter_image' => config('app.og_default_image'),
            'schema' => $this->schemaService->getWebSiteSchema(),
        ];
    }

    /**
     * Generate description from content
     */
    protected function generateDescription(string $content, int $length = 160): string
    {
        // Strip HTML tags
        $text = strip_tags($content);
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        // Trim and truncate
        $text = trim($text);

        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - 3).'...';
    }

    /**
     * Generate breadcrumb schema
     */
    public function generateBreadcrumbSchema(array $items): array
    {
        $breadcrumbList = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [],
        ];

        foreach ($items as $position => $item) {
            $breadcrumbList['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ];
        }

        return $breadcrumbList;
    }

    /**
     * Clear SEO cache for a page
     */
    public function clearPageCache(Page $page): void
    {
        Cache::forget("seo.page.{$page->id}");
    }

    /**
     * Clear SEO cache for a blog post
     */
    public function clearBlogPostCache(BlogPost $post): void
    {
        Cache::forget("seo.blog_post.{$post->id}");
    }
}
