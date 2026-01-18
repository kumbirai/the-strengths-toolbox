<?php

namespace Tests\Unit\Repositories;

use App\Models\Page;
use App\Models\PageSEO;
use App\Repositories\PageRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected PageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PageRepository(new Page);
    }

    public function test_find_by_id_returns_model_when_exists(): void
    {
        $page = Page::factory()->create();

        $result = $this->repository->findById($page->id);

        $this->assertNotNull($result);
        $this->assertEquals($page->id, $result->id);
    }

    public function test_find_by_id_returns_null_when_not_exists(): void
    {
        $result = $this->repository->findById(99999);

        $this->assertNull($result);
    }

    public function test_find_by_slug_returns_page(): void
    {
        $page = Page::factory()->create(['slug' => 'test-page']);

        $result = $this->repository->findBySlug('test-page');

        $this->assertNotNull($result);
        $this->assertEquals($page->id, $result->id);
    }

    public function test_find_published_by_slug_returns_only_published(): void
    {
        $published = Page::factory()->create([
            'slug' => 'published-page',
            'is_published' => true,
        ]);

        $unpublished = Page::factory()->create([
            'slug' => 'unpublished-page',
            'is_published' => false,
        ]);

        $result = $this->repository->findPublishedBySlug('published-page');
        $this->assertNotNull($result);

        $result = $this->repository->findPublishedBySlug('unpublished-page');
        $this->assertNull($result);
    }

    public function test_get_all_published_returns_only_published(): void
    {
        Page::factory()->count(3)->create(['is_published' => true]);
        Page::factory()->count(2)->create(['is_published' => false]);

        $result = $this->repository->getAllPublished();

        $this->assertCount(3, $result);
        $result->each(function ($page) {
            $this->assertTrue($page->is_published);
        });
    }

    public function test_get_paginated_returns_paginated_results(): void
    {
        Page::factory()->count(25)->create(['is_published' => true]);

        $result = $this->repository->getPaginated(10);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_get_paginated_filters_by_status(): void
    {
        Page::factory()->count(5)->create(['is_published' => true]);
        Page::factory()->count(3)->create(['is_published' => false]);

        $result = $this->repository->getPaginated(10, ['is_published' => true]);

        $this->assertEquals(5, $result->total());
    }

    public function test_create_creates_new_page(): void
    {
        $data = [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'Test content',
            'is_published' => true,
        ];

        $page = $this->repository->create($data);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertDatabaseHas('pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
        ]);
    }

    public function test_update_updates_existing_page(): void
    {
        $page = Page::factory()->create(['title' => 'Original Title']);

        $this->repository->updateById($page->id, ['title' => 'Updated Title']);

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_delete_soft_deletes_page(): void
    {
        $page = Page::factory()->create();

        $result = $this->repository->deleteById($page->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_scope_published_filters_published_pages(): void
    {
        $published = Page::factory()->create([
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
        $unpublished = Page::factory()->create(['is_published' => false]);

        $results = Page::published()->get();

        $this->assertTrue($results->contains($published));
        $this->assertFalse($results->contains($unpublished));
    }

    public function test_find_by_id_loads_seo_relationship(): void
    {
        $page = Page::factory()->create();
        PageSEO::factory()->create(['page_id' => $page->id]);

        $result = $this->repository->findById($page->id);

        $this->assertTrue($result->relationLoaded('seo'));
    }

    public function test_slug_exists_returns_true_when_exists(): void
    {
        Page::factory()->create(['slug' => 'existing-slug']);

        $this->assertTrue($this->repository->slugExists('existing-slug'));
        $this->assertFalse($this->repository->slugExists('non-existing-slug'));
    }

    public function test_slug_exists_excludes_id_when_provided(): void
    {
        $page = Page::factory()->create(['slug' => 'test-slug']);

        $this->assertFalse($this->repository->slugExists('test-slug', $page->id));
    }
}
