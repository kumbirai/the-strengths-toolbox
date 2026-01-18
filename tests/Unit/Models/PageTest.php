<?php

namespace Tests\Unit\Models;

use App\Models\Page;
use App\Models\PageSEO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_has_seo_relationship(): void
    {
        $page = Page::factory()->create();
        $seo = PageSEO::factory()->create(['page_id' => $page->id]);

        $this->assertInstanceOf(PageSEO::class, $page->seo);
        $this->assertEquals($seo->id, $page->seo->id);
    }

    public function test_scope_published_returns_only_published_pages(): void
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

    public function test_scope_unpublished_returns_only_unpublished_pages(): void
    {
        $published = Page::factory()->create([
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
        $unpublished = Page::factory()->create(['is_published' => false]);

        $results = Page::where('is_published', false)->get();

        $this->assertFalse($results->contains($published));
        $this->assertTrue($results->contains($unpublished));
    }
}
