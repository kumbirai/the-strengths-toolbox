<?php

namespace Tests\Unit\Repositories;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Repositories\BlogPostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected BlogPostRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new BlogPostRepository(new BlogPost);
    }

    public function test_find_by_slug_returns_post(): void
    {
        $post = BlogPost::factory()->create(['slug' => 'test-post']);

        $result = $this->repository->findBySlug('test-post');

        $this->assertNotNull($result);
        $this->assertEquals($post->id, $result->id);
    }

    public function test_find_published_by_slug_returns_only_published(): void
    {
        $published = BlogPost::factory()->create([
            'slug' => 'published-post',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $unpublished = BlogPost::factory()->create([
            'slug' => 'unpublished-post',
            'is_published' => false,
        ]);

        $result = $this->repository->findPublishedBySlug('published-post');
        $this->assertNotNull($result);

        $result = $this->repository->findPublishedBySlug('unpublished-post');
        $this->assertNull($result);
    }

    public function test_get_all_published_returns_only_published(): void
    {
        BlogPost::factory()->count(3)->create([
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
        BlogPost::factory()->count(2)->create(['is_published' => false]);

        $result = $this->repository->getPublishedPaginated(10);

        $this->assertEquals(3, $result->total());
    }

    public function test_get_paginated_filters_by_category(): void
    {
        $category = Category::factory()->create();
        $post = BlogPost::factory()->create(['is_published' => true]);
        $post->categories()->attach($category);

        $result = $this->repository->getByCategory($category->id);

        $this->assertGreaterThan(0, $result->total());
        $result->each(function ($post) use ($category) {
            $this->assertTrue($post->categories->contains($category));
        });
    }

    public function test_get_paginated_filters_by_tag(): void
    {
        $tag = Tag::factory()->create();
        $post = BlogPost::factory()->create(['is_published' => true]);
        $post->tags()->attach($tag);

        $result = $this->repository->getByTag($tag->id);

        $this->assertGreaterThan(0, $result->total());
    }

    public function test_get_recent_returns_recent_posts(): void
    {
        BlogPost::factory()->count(10)->create([
            'is_published' => true,
            'published_at' => now()->subDays(rand(1, 10)),
        ]);

        $result = $this->repository->getRecent(5);

        $this->assertCount(5, $result);
    }

    public function test_create_creates_new_post(): void
    {
        $user = User::factory()->create();
        $data = [
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'excerpt' => 'Test excerpt',
            'author_id' => $user->id,
            'is_published' => true,
        ];

        $post = $this->repository->create($data);

        $this->assertInstanceOf(BlogPost::class, $post);
        $this->assertDatabaseHas('blog_posts', [
            'title' => 'Test Post',
        ]);
    }

    public function test_update_updates_post(): void
    {
        $post = BlogPost::factory()->create(['title' => 'Original Title']);

        $this->repository->updateById($post->id, ['title' => 'Updated Title']);

        $this->assertDatabaseHas('blog_posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_delete_soft_deletes_post(): void
    {
        $post = BlogPost::factory()->create();

        $result = $this->repository->deleteById($post->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('blog_posts', ['id' => $post->id]);
    }

    public function test_search_returns_matching_posts(): void
    {
        BlogPost::factory()->create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        $result = $this->repository->search('Test');

        $this->assertGreaterThan(0, $result->total());
    }

    public function test_get_related_returns_related_posts(): void
    {
        $category = Category::factory()->create();
        $post1 = BlogPost::factory()->create(['is_published' => true]);
        $post1->categories()->attach($category);

        $post2 = BlogPost::factory()->create(['is_published' => true]);
        $post2->categories()->attach($category);

        $result = $this->repository->getRelated($post1, 5);

        $this->assertGreaterThan(0, $result->count());
        $this->assertFalse($result->contains($post1));
    }

    public function test_find_by_id_loads_relationships(): void
    {
        $post = BlogPost::factory()->create();

        $result = $this->repository->findById($post->id);

        $this->assertTrue($result->relationLoaded('author'));
        $this->assertTrue($result->relationLoaded('categories'));
        $this->assertTrue($result->relationLoaded('tags'));
    }
}
