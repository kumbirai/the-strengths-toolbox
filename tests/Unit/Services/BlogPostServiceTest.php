<?php

namespace Tests\Unit\Services;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Repositories\BlogPostRepository;
use App\Services\BlogPostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class BlogPostServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BlogPostService $service;

    protected BlogPostRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->repository = new BlogPostRepository(new BlogPost);
        $this->service = new BlogPostService($this->repository);
        Cache::flush();
    }

    public function test_get_published_by_slug_returns_published_post(): void
    {
        $post = BlogPost::factory()->create([
            'slug' => 'test-post',
            'is_published' => true,
        ]);

        $result = $this->service->getPublishedBySlug('test-post');

        $this->assertNotNull($result);
        $this->assertEquals($post->id, $result->id);
        $this->assertTrue($result->is_published);
    }

    public function test_get_published_by_slug_returns_null_for_unpublished(): void
    {
        BlogPost::factory()->create([
            'slug' => 'unpublished-post',
            'is_published' => false,
        ]);

        $result = $this->service->getPublishedBySlug('unpublished-post');

        $this->assertNull($result);
    }

    public function test_get_by_id_returns_post(): void
    {
        $post = BlogPost::factory()->create();

        $result = $this->service->getById($post->id);

        $this->assertNotNull($result);
        $this->assertEquals($post->id, $result->id);
    }

    public function test_create_generates_slug_automatically(): void
    {
        $user = User::factory()->create();
        $post = $this->service->create([
            'title' => 'Test Blog Post Title',
            'content' => 'Test content',
            'author_id' => $user->id,
            'is_published' => true,
        ]);

        $this->assertNotNull($post);
        $this->assertEquals('test-blog-post-title', $post->slug);
        $this->assertDatabaseHas('blog_posts', [
            'title' => 'Test Blog Post Title',
            'slug' => 'test-blog-post-title',
        ]);
    }

    public function test_create_sets_published_at_when_published(): void
    {
        $user = User::factory()->create();
        $post = $this->service->create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'author_id' => $user->id,
            'is_published' => true,
        ]);

        $this->assertNotNull($post->published_at);
    }

    public function test_create_does_not_set_published_at_when_unpublished(): void
    {
        $user = User::factory()->create();
        $post = $this->service->create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'author_id' => $user->id,
            'is_published' => false,
        ]);

        $this->assertNull($post->published_at);
    }

    public function test_create_attaches_categories(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $post = $this->service->create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'author_id' => $user->id,
            'category_ids' => [$category->id],
        ]);

        $this->assertTrue($post->categories->contains($category));
    }

    public function test_create_attaches_tags(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $post = $this->service->create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'author_id' => $user->id,
            'tag_ids' => [$tag->id],
        ]);

        $this->assertTrue($post->tags->contains($tag));
    }

    public function test_update_updates_post(): void
    {
        $post = BlogPost::factory()->create([
            'title' => 'Original Title',
        ]);

        $updated = $this->service->update($post->id, [
            'title' => 'Updated Title',
        ]);

        $this->assertEquals('Updated Title', $updated->title);
    }

    public function test_update_throws_exception_when_post_not_found(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Blog post with ID 99999 not found');

        $this->service->update(99999, ['title' => 'Test']);
    }

    public function test_delete_soft_deletes_post(): void
    {
        $post = BlogPost::factory()->create();

        $result = $this->service->delete($post->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('blog_posts', ['id' => $post->id]);
    }

    public function test_get_recent_returns_recent_posts(): void
    {
        BlogPost::factory()->count(10)->create(['is_published' => true]);

        $result = $this->service->getRecent(5);

        $this->assertCount(5, $result);
    }

    public function test_get_by_category_returns_posts_in_category(): void
    {
        $category = Category::factory()->create();
        $post = BlogPost::factory()->create(['is_published' => true]);
        $post->categories()->attach($category);

        $result = $this->service->getByCategory($category->slug);

        $this->assertNotNull($result);
        $this->assertGreaterThan(0, $result->total());
    }

    public function test_get_by_tag_returns_posts_with_tag(): void
    {
        $tag = Tag::factory()->create();
        $post = BlogPost::factory()->create(['is_published' => true]);
        $post->tags()->attach($tag);

        $result = $this->service->getByTag($tag->slug);

        $this->assertNotNull($result);
        $this->assertGreaterThan(0, $result->total());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
