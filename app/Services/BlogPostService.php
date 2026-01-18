<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Tag;
use App\Repositories\BlogPostRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BlogPostService
{
    protected BlogPostRepository $blogPostRepository;

    protected int $cacheTtl;

    public function __construct(BlogPostRepository $blogPostRepository)
    {
        $this->blogPostRepository = $blogPostRepository;
        $this->cacheTtl = config('cache.ttl.blog_posts', 3600);
    }

    /**
     * Get a published blog post by slug
     */
    public function getPublishedBySlug(string $slug): ?BlogPost
    {
        return Cache::remember(
            "blog_post.published.slug.{$slug}",
            $this->cacheTtl,
            fn () => $this->blogPostRepository->findPublishedBySlug($slug)
        );
    }

    /**
     * Get a blog post by ID
     */
    public function getById(int $id): ?BlogPost
    {
        return Cache::remember(
            "blog_post.id.{$id}",
            $this->cacheTtl,
            fn () => $this->blogPostRepository->findById($id)
        );
    }

    /**
     * Get paginated published blog posts
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPublishedPaginated(int $perPage = 10)
    {
        $page = request()->get('page', 1);

        return Cache::remember(
            "blog_posts.published.page.{$page}.per_page.{$perPage}",
            $this->cacheTtl,
            fn () => $this->blogPostRepository->getPublishedPaginated($perPage)
        );
    }

    /**
     * Get recent blog posts
     */
    public function getRecent(int $limit = 5): Collection
    {
        return Cache::remember(
            "blog_posts.recent.{$limit}",
            $this->cacheTtl,
            fn () => $this->blogPostRepository->getRecent($limit)
        );
    }

    /**
     * Get blog posts by category
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|null
     */
    public function getByCategory(string $categorySlug, int $perPage = 10)
    {
        $category = Category::where('slug', $categorySlug)->first();

        if (! $category) {
            return null;
        }

        $page = request()->get('page', 1);

        return Cache::remember(
            "blog_posts.category.{$categorySlug}.page.{$page}",
            $this->cacheTtl,
            fn () => $this->blogPostRepository->getByCategory($category->id, $perPage)
        );
    }

    /**
     * Get blog posts by tag
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|null
     */
    public function getByTag(string $tagSlug, int $perPage = 10)
    {
        $tag = Tag::where('slug', $tagSlug)->first();

        if (! $tag) {
            return null;
        }

        $page = request()->get('page', 1);

        return Cache::remember(
            "blog_posts.tag.{$tagSlug}.page.{$page}",
            $this->cacheTtl,
            fn () => $this->blogPostRepository->getByTag($tag->id, $perPage)
        );
    }

    /**
     * Search blog posts
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search(string $query, int $perPage = 10)
    {
        return $this->blogPostRepository->search($query, $perPage);
    }

    /**
     * Get paginated blog posts with filters
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->blogPostRepository->getPaginated($perPage, $filters);
    }

    /**
     * Get related blog posts
     */
    public function getRelated(BlogPost $post, int $limit = 3): Collection
    {
        return $this->blogPostRepository->getRelated($post, $limit);
    }

    /**
     * Create a new blog post
     */
    public function create(array $data): BlogPost
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Handle published_at
        if (isset($data['is_published']) && $data['is_published']) {
            if (empty($data['published_at'])) {
                $data['published_at'] = Carbon::now();
            }
        } else {
            $data['published_at'] = null;
        }

        // Handle featured image upload
        if (isset($data['featured_image']) && $data['featured_image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['featured_image'] = $this->handleImageUpload($data['featured_image']);
        }

        // Extract categories and tags
        $categoryIds = $data['category_ids'] ?? [];
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['category_ids'], $data['tag_ids']);

        $post = $this->blogPostRepository->create($data);

        // Attach categories and tags
        if (! empty($categoryIds)) {
            $post->categories()->sync($categoryIds);
        }

        if (! empty($tagIds)) {
            $post->tags()->sync($tagIds);
        }

        // Clear relevant caches
        $this->clearBlogPostCache($post);

        return $post->load(['categories', 'tags', 'author']);
    }

    /**
     * Update an existing blog post
     */
    public function update(int $id, array $data): BlogPost
    {
        $post = $this->blogPostRepository->findById($id);

        if (! $post) {
            throw new \Exception("Blog post with ID {$id} not found");
        }

        // Update slug if title changed
        if (isset($data['title']) && $data['title'] !== $post->title) {
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['title'], $id);
            }
        }

        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Handle published_at
        if (isset($data['is_published'])) {
            if ($data['is_published'] && empty($data['published_at'])) {
                $data['published_at'] = $post->published_at ?? Carbon::now();
            } elseif (! $data['is_published']) {
                $data['published_at'] = null;
            }
        }

        // Handle featured image upload
        if (isset($data['featured_image']) && $data['featured_image'] instanceof \Illuminate\Http\UploadedFile) {
            // Delete old image if exists
            if ($post->featured_image) {
                $this->deleteImage($post->featured_image);
            }
            $data['featured_image'] = $this->handleImageUpload($data['featured_image']);
        }

        // Extract categories and tags
        $categoryIds = $data['category_ids'] ?? null;
        $tagIds = $data['tag_ids'] ?? null;
        unset($data['category_ids'], $data['tag_ids']);

        $this->blogPostRepository->updateById($id, $data);
        $post->refresh();

        // Sync categories and tags if provided
        if ($categoryIds !== null) {
            $post->categories()->sync($categoryIds);
        }

        if ($tagIds !== null) {
            $post->tags()->sync($tagIds);
        }

        // Clear relevant caches
        $this->clearBlogPostCache($post);

        return $post->load(['categories', 'tags', 'author']);
    }

    /**
     * Delete a blog post (soft delete)
     */
    public function delete(int $id): bool
    {
        $post = $this->blogPostRepository->findById($id);

        if (! $post) {
            throw new \Exception("Blog post with ID {$id} not found");
        }

        // Delete featured image if exists
        if ($post->featured_image) {
            $this->deleteImage($post->featured_image);
        }

        $result = $this->blogPostRepository->deleteById($id);

        // Clear relevant caches
        $this->clearBlogPostCache($post);
        $this->clearAllBlogPostCache();

        return $result;
    }

    /**
     * Handle featured image upload
     */
    protected function handleImageUpload(\Illuminate\Http\UploadedFile $file): string
    {
        $filename = time().'_'.Str::random(10).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('blog/featured-images', $filename, 'public');

        return $path;
    }

    /**
     * Delete image file
     */
    protected function deleteImage(string $path): void
    {
        if (\Storage::disk('public')->exists($path)) {
            \Storage::disk('public')->delete($path);
        }
    }

    /**
     * Generate a unique slug from title
     */
    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->blogPostRepository->slugExists($slug, $excludeId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Clear cache for a specific blog post
     */
    protected function clearBlogPostCache(BlogPost $post): void
    {
        Cache::forget("blog_post.published.slug.{$post->slug}");
        Cache::forget("blog_post.id.{$post->id}");
        $this->clearAllBlogPostCache();
    }

    /**
     * Clear all blog post caches
     */
    public function clearAllBlogPostCache(): void
    {
        // Clear paginated blog post caches (clear first few pages)
        for ($page = 1; $page <= 10; $page++) {
            for ($perPage = 5; $perPage <= 20; $perPage += 5) {
                Cache::forget("blog_posts.published.page.{$page}.per_page.{$perPage}");
            }
        }

        // Clear recent posts cache
        for ($limit = 1; $limit <= 20; $limit++) {
            Cache::forget("blog_posts.recent.{$limit}");
        }

        // Note: Individual post caches will expire naturally
    }
}
