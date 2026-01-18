<?php

namespace Tests\Unit\Services;

use App\Models\Page;
use App\Repositories\PageRepository;
use App\Services\PageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class PageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PageService $service;

    protected PageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PageRepository(new Page);
        $this->service = new PageService($this->repository);
        Cache::flush();
    }

    public function test_get_by_slug_returns_published_page(): void
    {
        $page = Page::factory()->create([
            'slug' => 'test-page',
            'is_published' => true,
        ]);

        $result = $this->service->getBySlug('test-page');

        $this->assertNotNull($result);
        $this->assertEquals($page->id, $result->id);
        $this->assertTrue($result->is_published);
    }

    public function test_get_by_slug_returns_null_for_unpublished(): void
    {
        Page::factory()->create([
            'slug' => 'unpublished-page',
            'is_published' => false,
        ]);

        $result = $this->service->getBySlug('unpublished-page');

        $this->assertNull($result);
    }

    public function test_get_by_slug_caches_result(): void
    {
        $page = Page::factory()->create([
            'slug' => 'cached-page',
            'is_published' => true,
        ]);

        // First call
        $result1 = $this->service->getBySlug('cached-page');
        $this->assertNotNull($result1);

        // Delete from database
        $page->delete();

        // Second call should return cached result
        $result2 = $this->service->getBySlug('cached-page');
        $this->assertNotNull($result2);
        $this->assertEquals($result1->id, $result2->id);
    }

    public function test_get_by_id_returns_page(): void
    {
        $page = Page::factory()->create();

        $result = $this->service->getById($page->id);

        $this->assertNotNull($result);
        $this->assertEquals($page->id, $result->id);
    }

    public function test_get_by_id_returns_null_when_not_exists(): void
    {
        $result = $this->service->getById(99999);

        $this->assertNull($result);
    }

    public function test_get_all_published_returns_only_published_pages(): void
    {
        Page::factory()->count(3)->create(['is_published' => true]);
        Page::factory()->count(2)->create(['is_published' => false]);

        $result = $this->service->getAllPublished();

        $this->assertCount(3, $result);
        $result->each(function ($page) {
            $this->assertTrue($page->is_published);
        });
    }

    public function test_get_paginated_returns_paginated_results(): void
    {
        Page::factory()->count(25)->create(['is_published' => true]);

        $result = $this->service->getPaginated(10);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_get_paginated_filters_by_status(): void
    {
        Page::factory()->count(5)->create(['is_published' => true]);
        Page::factory()->count(3)->create(['is_published' => false]);

        $result = $this->service->getPaginated(10, ['is_published' => true]);

        $this->assertEquals(5, $result->total());
    }

    public function test_create_generates_slug_automatically(): void
    {
        $page = $this->service->create([
            'title' => 'Test Page Title',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        $this->assertNotNull($page);
        $this->assertEquals('test-page-title', $page->slug);
        $this->assertDatabaseHas('pages', [
            'title' => 'Test Page Title',
            'slug' => 'test-page-title',
        ]);
    }

    public function test_create_uses_provided_slug(): void
    {
        $page = $this->service->create([
            'title' => 'Test Page',
            'slug' => 'custom-slug',
            'content' => 'Test content',
        ]);

        $this->assertEquals('custom-slug', $page->slug);
    }

    public function test_create_requires_title(): void
    {
        // Creating a page without title should fail
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('Undefined array key "title"');

        $this->service->create([]);
    }

    public function test_update_updates_page(): void
    {
        $page = Page::factory()->create([
            'title' => 'Original Title',
            'slug' => 'original-slug',
        ]);

        $updated = $this->service->update($page->id, [
            'title' => 'Updated Title',
        ]);

        $this->assertEquals('Updated Title', $updated->title);
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_update_throws_exception_when_page_not_found(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Page with ID 99999 not found');

        $this->service->update(99999, ['title' => 'Test']);
    }

    public function test_delete_soft_deletes_page(): void
    {
        $page = Page::factory()->create();

        $result = $this->service->delete($page->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_delete_throws_exception_when_page_not_found(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Page with ID 99999 not found');

        $this->service->delete(99999);
    }

    public function test_clear_all_cache_clears_cache(): void
    {
        Page::factory()->create([
            'slug' => 'test-page',
            'is_published' => true,
        ]);

        // Populate cache
        $this->service->getBySlug('test-page');
        $this->assertTrue(Cache::has('page.slug.test-page'));

        $this->service->clearAllCache();

        // Cache should be cleared
        Cache::shouldReceive('forget')->with('pages.published.all');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
