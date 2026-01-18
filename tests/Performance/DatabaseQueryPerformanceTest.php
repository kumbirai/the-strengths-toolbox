<?php

namespace Tests\Performance;

use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseQueryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_listing_uses_eager_loading(): void
    {
        DB::enableQueryLog();

        Page::factory()->count(10)->create();
        $this->get('/pages');

        $queries = DB::getQueryLog();

        // Should use eager loading, not N+1 queries
        $this->assertLessThan(5, count($queries), 'Too many queries executed: '.count($queries));

        DB::disableQueryLog();
    }

    public function test_blog_post_with_relationships_uses_eager_loading(): void
    {
        DB::enableQueryLog();

        $post = BlogPost::factory()->create();
        $post->categories()->attach(\App\Models\Category::factory()->create());
        $post->tags()->attach(\App\Models\Tag::factory()->create());

        $this->get("/blog/{$post->slug}");

        $queries = DB::getQueryLog();

        // Should use eager loading
        $this->assertLessThan(10, count($queries), 'Too many queries executed: '.count($queries));

        DB::disableQueryLog();
    }

    public function test_query_execution_time_acceptable(): void
    {
        Page::factory()->count(100)->create();

        $startTime = microtime(true);
        $pages = Page::with('seo')->get();
        $endTime = microtime(true);

        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertLessThan(500, $executionTime, "Query took {$executionTime}ms, expected < 500ms");
    }
}
