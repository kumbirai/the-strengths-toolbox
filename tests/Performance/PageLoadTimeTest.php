<?php

namespace Tests\Performance;

use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageLoadTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_within_3_seconds(): void
    {
        $startTime = microtime(true);
        $response = $this->get('/');
        $endTime = microtime(true);

        $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);
        $this->assertLessThan(3000, $loadTime, "Homepage loaded in {$loadTime}ms, expected < 3000ms");
    }

    public function test_static_page_loads_within_3_seconds(): void
    {
        $page = Page::factory()->create([
            'slug' => 'test-page',
            'is_published' => true,
        ]);

        $startTime = microtime(true);
        $response = $this->get('/test-page');
        $endTime = microtime(true);

        $loadTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(3000, $loadTime, "Page loaded in {$loadTime}ms, expected < 3000ms");
    }

    public function test_blog_post_loads_within_3_seconds(): void
    {
        $post = BlogPost::factory()->create([
            'slug' => 'test-post',
            'is_published' => true,
        ]);

        $startTime = microtime(true);
        $response = $this->get("/blog/{$post->slug}");
        $endTime = microtime(true);

        $loadTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(3000, $loadTime, "Blog post loaded in {$loadTime}ms, expected < 3000ms");
    }
}
