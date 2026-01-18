<?php

namespace Tests\Unit\Models;

use App\Models\BlogPost;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_has_blog_posts_relationship(): void
    {
        $tag = Tag::factory()->create();
        $post = BlogPost::factory()->create();
        $post->tags()->attach($tag);

        $this->assertTrue($tag->blogPosts->contains($post));
        $this->assertEquals(1, $tag->blogPosts->count());
    }
}
