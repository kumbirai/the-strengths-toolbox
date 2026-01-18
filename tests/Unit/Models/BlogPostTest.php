<?php

namespace Tests\Unit\Models;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_post_has_categories_relationship(): void
    {
        $post = BlogPost::factory()->create();
        $category = Category::factory()->create();
        $post->categories()->attach($category);

        $this->assertTrue($post->categories->contains($category));
        $this->assertEquals(1, $post->categories->count());
    }

    public function test_blog_post_has_tags_relationship(): void
    {
        $post = BlogPost::factory()->create();
        $tag = Tag::factory()->create();
        $post->tags()->attach($tag);

        $this->assertTrue($post->tags->contains($tag));
        $this->assertEquals(1, $post->tags->count());
    }

    public function test_blog_post_belongs_to_author(): void
    {
        $user = User::factory()->create();
        $post = BlogPost::factory()->create(['author_id' => $user->id]);

        $this->assertInstanceOf(User::class, $post->author);
        $this->assertEquals($user->id, $post->author->id);
    }

    public function test_scope_published_returns_only_published_posts(): void
    {
        $published = BlogPost::factory()->create([
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
        $unpublished = BlogPost::factory()->create(['is_published' => false]);

        $results = BlogPost::published()->get();

        $this->assertTrue($results->contains($published));
        $this->assertFalse($results->contains($unpublished));
    }

    public function test_increment_views_increments_views_count(): void
    {
        $post = BlogPost::factory()->create(['views_count' => 0]);

        $post->incrementViews();

        $this->assertEquals(1, $post->fresh()->views_count);
    }
}
