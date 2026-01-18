<?php

namespace Tests\Unit\Models;

use App\Models\BlogPost;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_blog_posts_relationship(): void
    {
        $category = Category::factory()->create();
        $post = BlogPost::factory()->create();
        $post->categories()->attach($category);

        $this->assertTrue($category->blogPosts->contains($post));
        $this->assertEquals(1, $category->blogPosts->count());
    }
}
