<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_route_works(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_about_us_route_works(): void
    {
        Page::factory()->create(['slug' => 'about-us', 'is_published' => true]);
        $response = $this->get('/about-us');
        $response->assertStatus(200);
    }

    public function test_blog_listing_route_works(): void
    {
        $response = $this->get('/blog');
        $response->assertStatus(200);
    }

    public function test_blog_post_route_works(): void
    {
        $post = BlogPost::factory()->create([
            'slug' => 'test-post',
            'is_published' => true,
        ]);
        $response = $this->get("/blog/{$post->slug}");
        $response->assertStatus(200);
    }

    public function test_invalid_route_returns_404(): void
    {
        $response = $this->get('/non-existent-page');
        $response->assertStatus(404);
    }
}
