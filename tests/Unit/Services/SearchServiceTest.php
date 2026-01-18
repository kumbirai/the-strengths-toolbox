<?php

namespace Tests\Unit\Services;

use App\Models\BlogPost;
use App\Models\Page;
use App\Services\SearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SearchService;
        Cache::flush();
    }

    public function test_search_returns_results_from_all_content_types(): void
    {
        Page::factory()->create([
            'title' => 'Test Page',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        BlogPost::factory()->create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        $results = $this->service->search('Test');

        $this->assertArrayHasKey('pages', $results);
        $this->assertArrayHasKey('posts', $results);
        $this->assertArrayHasKey('total', $results);
        $this->assertGreaterThan(0, $results['total']);
    }

    public function test_search_returns_empty_for_short_query(): void
    {
        $results = $this->service->search('T');

        $this->assertEquals(0, $results['total']);
        $this->assertCount(0, $results['pages']);
        $this->assertCount(0, $results['posts']);
    }

    public function test_search_pages_returns_matching_pages(): void
    {
        Page::factory()->create([
            'title' => 'Test Page',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        Page::factory()->create([
            'title' => 'Other Page',
            'content' => 'Other content',
            'is_published' => true,
        ]);

        $results = $this->service->search('Test');

        $this->assertGreaterThan(0, $results['pages']->count());
        $results['pages']->each(function ($page) {
            $this->assertArrayHasKey('title', $page);
            $this->assertArrayHasKey('url', $page);
            $this->assertStringContainsString('Test', $page['title']);
        });
    }

    public function test_search_blog_posts_returns_matching_posts(): void
    {
        BlogPost::factory()->create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        $results = $this->service->search('Test');

        $this->assertGreaterThan(0, $results['posts']->count());
    }

    public function test_search_highlights_query_in_results(): void
    {
        Page::factory()->create([
            'title' => 'Test Page',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        $results = $this->service->search('Test');

        $this->assertStringContainsString('<mark>', $results['pages']->first()['title']);
    }

    public function test_search_caches_results(): void
    {
        Page::factory()->create([
            'title' => 'Test Page',
            'is_published' => true,
        ]);

        // First search
        $results1 = $this->service->search('Test');

        // Delete page
        Page::where('title', 'Test Page')->delete();

        // Second search should return cached results
        $results2 = $this->service->search('Test');

        $this->assertEquals($results1['total'], $results2['total']);
    }

    public function test_get_popular_searches_returns_collection(): void
    {
        $popular = $this->service->getPopularSearches(5);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $popular);
        $this->assertLessThanOrEqual(5, $popular->count());
    }
}
