<?php

namespace App\Repositories;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PageRepository extends BaseRepository
{
    public function __construct(Page $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a page by ID
     */
    public function findById(int $id): ?Page
    {
        return $this->model->with('seo')->find($id);
    }

    /**
     * Find a published page by slug
     */
    public function findPublishedBySlug(string $slug): ?Page
    {
        return $this->model
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with('seo')
            ->first();
    }

    /**
     * Find a page by slug (including unpublished)
     */
    public function findBySlug(string $slug): ?Page
    {
        return $this->model
            ->where('slug', $slug)
            ->with('seo')
            ->first();
    }

    /**
     * Get all published pages
     */
    public function getAllPublished(): Collection
    {
        return $this->model
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->with('seo')
            ->get();
    }

    /**
     * Get all pages (including unpublished)
     */
    public function getAll(): Collection
    {
        return $this->model
            ->orderBy('created_at', 'desc')
            ->with('seo')
            ->get();
    }

    /**
     * Get paginated pages
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with('seo');

        // Apply filters
        if (isset($filters['is_published'])) {
            $query->where('is_published', $filters['is_published']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('content', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new page
     */
    public function create(array $data): Page
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing page by ID
     */
    public function updateById(int $id, array $data): bool
    {
        $page = $this->model->find($id);

        if (! $page) {
            return false;
        }

        return $page->update($data);
    }

    /**
     * Delete a page by ID (soft delete)
     */
    public function deleteById(int $id): bool
    {
        $page = $this->model->find($id);

        if (! $page) {
            return false;
        }

        return $page->delete();
    }

    /**
     * Check if a slug exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = $this->model->where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get pages by multiple slugs
     */
    public function getBySlugs(array $slugs): Collection
    {
        return $this->model
            ->whereIn('slug', $slugs)
            ->where('is_published', true)
            ->with('seo')
            ->get();
    }

    /**
     * Get featured pages
     */
    public function getFeatured(int $limit = 5): Collection
    {
        return $this->model
            ->where('is_published', true)
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->with('seo')
            ->get();
    }
}
