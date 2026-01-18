<?php

namespace App\Repositories;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BlogPostRepository extends BaseRepository
{
    public function __construct(BlogPost $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a blog post by ID
     */
    public function findById(int $id): ?BlogPost
    {
        return $this->model
            ->with(['author', 'categories', 'tags'])
            ->find($id);
    }

    /**
     * Find a published blog post by slug
     */
    public function findPublishedBySlug(string $slug): ?BlogPost
    {
        return $this->model
            ->where('slug', $slug)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->with(['author', 'categories', 'tags'])
            ->first();
    }

    /**
     * Find a blog post by slug (including unpublished)
     */
    public function findBySlug(string $slug): ?BlogPost
    {
        return $this->model
            ->where('slug', $slug)
            ->with(['author', 'categories', 'tags'])
            ->first();
    }

    /**
     * Get paginated published blog posts
     */
    public function getPublishedPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->with(['author', 'categories', 'tags'])
            ->paginate($perPage);
    }

    /**
     * Get recent published blog posts
     */
    public function getRecent(int $limit = 5): Collection
    {
        return $this->model
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->with(['author', 'categories'])
            ->get();
    }

    /**
     * Get blog posts by category
     */
    public function getByCategory(int $categoryId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            })
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->with(['author', 'categories', 'tags'])
            ->paginate($perPage);
    }

    /**
     * Get blog posts by tag
     */
    public function getByTag(int $tagId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->whereHas('tags', function ($query) use ($tagId) {
                $query->where('tags.id', $tagId);
            })
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->with(['author', 'categories', 'tags'])
            ->paginate($perPage);
    }

    /**
     * Search blog posts
     */
    public function search(string $query, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%'.$query.'%')
                    ->orWhere('excerpt', 'like', '%'.$query.'%')
                    ->orWhere('content', 'like', '%'.$query.'%');
            })
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->with(['author', 'categories', 'tags'])
            ->paginate($perPage);
    }

    /**
     * Get all blog posts (including unpublished)
     */
    public function getAll(): Collection
    {
        return $this->model
            ->orderBy('created_at', 'desc')
            ->with(['author', 'categories', 'tags'])
            ->get();
    }

    /**
     * Get paginated blog posts with filters
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['author', 'categories', 'tags']);

        // Apply filters
        if (isset($filters['is_published'])) {
            $query->where('is_published', $filters['is_published']);
        }

        if (isset($filters['author_id'])) {
            $query->where('author_id', $filters['author_id']);
        }

        if (isset($filters['category_id'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('categories.id', $filters['category_id']);
            });
        }

        if (isset($filters['tag_id'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('tags.id', $filters['tag_id']);
            });
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('excerpt', 'like', '%'.$filters['search'].'%')
                    ->orWhere('content', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new blog post
     */
    public function create(array $data): BlogPost
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing blog post by ID
     */
    public function updateById(int $id, array $data): bool
    {
        $post = $this->model->find($id);

        if (! $post) {
            return false;
        }

        return $post->update($data);
    }

    /**
     * Delete a blog post by ID (soft delete)
     */
    public function deleteById(int $id): bool
    {
        $post = $this->model->find($id);

        if (! $post) {
            return false;
        }

        return $post->delete();
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
     * Get related blog posts
     *
     * Uses a multi-tier approach to find relevant posts:
     * 1. Posts with matching categories AND tags (highest relevance)
     * 2. Posts with matching categories OR tags
     * 3. Posts with matching categories only
     * Adds randomization for variety while maintaining relevance.
     */
    public function getRelated(BlogPost $post, int $limit = 3): Collection
    {
        $categoryIds = $post->categories->pluck('id')->toArray();
        $tagIds = $post->tags->pluck('id')->toArray();

        $baseQuery = $this->model
            ->where('id', '!=', $post->id)
            ->where('is_published', true)
            ->where('published_at', '<=', now());

        $relatedPosts = collect();

        // Tier 1: Posts with matching categories AND tags (highest relevance)
        if (! empty($categoryIds) && ! empty($tagIds)) {
            $tier1 = (clone $baseQuery)
                ->whereHas('categories', function ($query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                })
                ->whereHas('tags', function ($query) use ($tagIds) {
                    $query->whereIn('tags.id', $tagIds);
                })
                ->with(['author', 'categories', 'tags'])
                ->get();

            if ($tier1->isNotEmpty()) {
                $relatedPosts = $relatedPosts->merge($tier1);
            }
        }

        // Tier 2: Posts with matching categories OR tags (if we need more)
        if ($relatedPosts->count() < $limit) {
            $excludeIds = $relatedPosts->pluck('id')->toArray();
            $excludeIds[] = $post->id;

            $tier2 = (clone $baseQuery)
                ->whereNotIn('id', $excludeIds)
                ->where(function ($query) use ($categoryIds, $tagIds) {
                    if (! empty($categoryIds)) {
                        $query->whereHas('categories', function ($q) use ($categoryIds) {
                            $q->whereIn('categories.id', $categoryIds);
                        });
                    }
                    if (! empty($tagIds)) {
                        $query->orWhereHas('tags', function ($q) use ($tagIds) {
                            $q->whereIn('tags.id', $tagIds);
                        });
                    }
                })
                ->with(['author', 'categories', 'tags'])
                ->get();

            if ($tier2->isNotEmpty()) {
                $relatedPosts = $relatedPosts->merge($tier2);
            }
        }

        // Tier 3: Posts with matching categories only (fallback)
        if ($relatedPosts->count() < $limit && ! empty($categoryIds)) {
            $excludeIds = $relatedPosts->pluck('id')->toArray();
            $excludeIds[] = $post->id;

            $tier3 = (clone $baseQuery)
                ->whereNotIn('id', $excludeIds)
                ->whereHas('categories', function ($query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                })
                ->with(['author', 'categories', 'tags'])
                ->get();

            if ($tier3->isNotEmpty()) {
                $relatedPosts = $relatedPosts->merge($tier3);
            }
        }

        // Shuffle for variety, then take the limit
        // Get IDs, shuffle them, then query again to maintain Eloquent Collection type
        $shuffledIds = $relatedPosts
            ->pluck('id')
            ->shuffle()
            ->take($limit)
            ->toArray();

        if (empty($shuffledIds)) {
            return $this->model->newCollection();
        }

        // Query with shuffled IDs, then sort in PHP to preserve shuffled order
        $posts = $this->model
            ->whereIn('id', $shuffledIds)
            ->with(['author', 'categories', 'tags'])
            ->get();

        // Sort by the shuffled ID order
        $sortedPosts = $posts->sortBy(function ($post) use ($shuffledIds) {
            return array_search($post->id, $shuffledIds);
        })->values();

        return $sortedPosts;
    }

    /**
     * Get blog posts by author
     */
    public function getByAuthor(int $authorId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->where('author_id', $authorId)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->with(['author', 'categories', 'tags'])
            ->paginate($perPage);
    }

    /**
     * Get featured blog posts
     */
    public function getFeatured(int $limit = 5): Collection
    {
        return $this->model
            ->where('is_published', true)
            ->where('is_featured', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->with(['author', 'categories'])
            ->get();
    }
}
