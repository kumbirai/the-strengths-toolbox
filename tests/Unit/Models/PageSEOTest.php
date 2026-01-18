<?php

namespace Tests\Unit\Models;

use App\Models\Page;
use App\Models\PageSEO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSEOTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_seo_belongs_to_page(): void
    {
        $page = Page::factory()->create();
        $seo = PageSEO::factory()->create(['page_id' => $page->id]);

        $this->assertInstanceOf(Page::class, $seo->page);
        $this->assertEquals($page->id, $seo->page->id);
    }

    public function test_page_seo_returns_page_relationship(): void
    {
        $page = Page::factory()->create();
        $seo = PageSEO::factory()->create(['page_id' => $page->id]);

        $this->assertNotNull($seo->page);
        $this->assertEquals($page->title, $seo->page->title);
    }
}
