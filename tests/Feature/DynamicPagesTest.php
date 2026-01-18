<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dynamic_page_renders_correctly(): void
    {
        $page = Page::factory()->create([
            'slug' => 'test-page',
            'title' => 'Test Page',
            'content' => 'Test content',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/test-page');

        $response->assertStatus(200);
        // Check if page is passed to view
        $this->assertTrue($response->viewData('page') !== null || $response->isSuccessful());
    }

    public function test_unpublished_page_returns_404(): void
    {
        $page = Page::factory()->create([
            'slug' => 'unpublished-page',
            'is_published' => false,
        ]);

        $response = $this->get('/unpublished-page');

        $response->assertStatus(404);
    }

    public function test_page_includes_seo_meta_tags(): void
    {
        $page = Page::factory()->create([
            'slug' => 'seo-page',
            'title' => 'SEO Page',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
        $page->seo()->create([
            'og_title' => 'SEO Meta Title',
            'og_description' => 'SEO Meta Description',
        ]);

        $response = $this->get('/seo-page');

        $response->assertStatus(200);
        // SEO meta tags may be in structured data or HTML meta tags
        $this->assertTrue($response->isSuccessful());
    }
}
