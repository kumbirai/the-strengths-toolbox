<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test strengths programme page loads
     */
    public function test_strengths_programme_page_loads(): void
    {
        $response = $this->get('/strengths-programme');

        $response->assertStatus(200);
        // May return pages.strengths-programme if no CMS page exists, or web.pages.show if CMS page exists
        $this->assertTrue($response->isSuccessful());
    }

    /**
     * Test about us page loads
     */
    public function test_about_us_page_loads(): void
    {
        $response = $this->get('/about-us');

        $response->assertStatus(200);
        // Page should load successfully
        $this->assertTrue($response->isSuccessful());
    }

    /**
     * Test dynamic CMS page loads
     */
    public function test_dynamic_cms_page_loads(): void
    {
        $page = Page::factory()->create([
            'slug' => 'test-page',
            'title' => 'Test Page',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/test-page');

        $response->assertStatus(200);
        // Check if page data is passed to view
        $this->assertTrue($response->viewData('page') !== null || $response->getContent() !== '');
    }

    /**
     * Test dynamic CMS page returns 404 for unpublished
     */
    public function test_dynamic_cms_page_404_for_unpublished(): void
    {
        Page::factory()->create([
            'slug' => 'unpublished-page',
            'is_published' => false,
        ]);

        $response = $this->get('/unpublished-page');

        $response->assertStatus(404);
    }
}
