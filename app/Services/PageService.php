<?php

namespace App\Services;

use App\Models\Page;
use App\Repositories\PageRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PageService
{
    protected PageRepository $pageRepository;

    protected int $cacheTtl;

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
        $this->cacheTtl = config('cache.ttl.pages', 3600); // 1 hour default
    }

    /**
     * Get a published page by slug
     */
    public function getBySlug(string $slug): ?Page
    {
        return Cache::remember(
            "page.slug.{$slug}",
            $this->cacheTtl,
            fn () => $this->pageRepository->findPublishedBySlug($slug)
                ?->load('seo') // Ensure seo relationship is loaded
        );
    }

    /**
     * Get a page by ID (including unpublished)
     */
    public function getById(int $id): ?Page
    {
        return Cache::remember(
            "page.id.{$id}",
            $this->cacheTtl,
            fn () => $this->pageRepository->findById($id)
        );
    }

    /**
     * Get all published pages
     */
    public function getAllPublished(): Collection
    {
        return Cache::remember(
            'pages.published.all',
            $this->cacheTtl,
            fn () => $this->pageRepository->getAllPublished()
        );
    }

    /**
     * Get paginated pages
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->pageRepository->getPaginated($perPage, $filters);
    }

    /**
     * Create a new page
     */
    public function create(array $data): Page
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        $page = $this->pageRepository->create($data);

        // Clear relevant caches
        $this->clearPageCache($page);

        return $page;
    }

    /**
     * Update an existing page
     */
    public function update(int $id, array $data): Page
    {
        $page = $this->pageRepository->findById($id);

        if (! $page) {
            throw new \Exception("Page with ID {$id} not found");
        }

        // Update slug if title changed
        if (isset($data['title']) && $data['title'] !== $page->title) {
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['title'], $id);
            }
        }

        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        $this->pageRepository->updateById($id, $data);
        $page->refresh();

        // Clear relevant caches
        $this->clearPageCache($page);

        return $page;
    }

    /**
     * Delete a page (soft delete)
     */
    public function delete(int $id): bool
    {
        $page = $this->pageRepository->findById($id);

        if (! $page) {
            throw new \Exception("Page with ID {$id} not found");
        }

        $result = $this->pageRepository->deleteById($id);

        // Clear relevant caches
        $this->clearPageCache($page);
        Cache::forget('pages.published.all');

        return $result;
    }

    /**
     * Generate a unique slug from title
     */
    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->pageRepository->slugExists($slug, $excludeId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Clear cache for a specific page
     */
    protected function clearPageCache(Page $page): void
    {
        Cache::forget("page.slug.{$page->slug}");
        Cache::forget("page.id.{$page->id}");
        Cache::forget('pages.published.all');
    }

    /**
     * Clear all page caches
     */
    public function clearAllCache(): void
    {
        Cache::forget('pages.published.all');
        // Note: Individual page caches will expire naturally
    }
}
