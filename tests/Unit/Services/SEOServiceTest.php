<?php

namespace Tests\Unit\Services;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\PageSEO;
use App\Services\SchemaService;
use App\Services\SEOService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class SEOServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SEOService $service;

    protected SchemaService $schemaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schemaService = new SchemaService;
        $this->service = new SEOService($this->schemaService);
        Cache::flush();
    }

    public function test_get_page_meta_returns_correct_structure(): void
    {
        $page = Page::factory()->create([
            'title' => 'Test Page',
            'content' => 'Test content',
        ]);

        $meta = $this->service->getPageMeta($page);

        $this->assertArrayHasKey('title', $meta);
        $this->assertArrayHasKey('description', $meta);
        $this->assertArrayHasKey('canonical', $meta);
        $this->assertArrayHasKey('og_title', $meta);
        $this->assertArrayHasKey('og_description', $meta);
        $this->assertArrayHasKey('schema', $meta);
    }

    public function test_get_page_meta_uses_page_seo_when_available(): void
    {
        $page = Page::factory()->create([
            'title' => 'Test Page',
        ]);

        $seo = PageSEO::factory()->create([
            'page_id' => $page->id,
            'og_title' => 'Custom OG Title',
            'og_description' => 'Custom OG Description',
        ]);

        $meta = $this->service->getPageMeta($page);

        $this->assertEquals('Custom OG Title', $meta['og_title']);
        $this->assertEquals('Custom OG Description', $meta['og_description']);
    }

    public function test_get_page_meta_falls_back_to_page_data(): void
    {
        $page = Page::factory()->create([
            'title' => 'Test Page',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
        ]);

        $meta = $this->service->getPageMeta($page);

        $this->assertEquals('Meta Title', $meta['title']);
        $this->assertEquals('Meta Description', $meta['description']);
    }

    public function test_get_blog_post_meta_returns_correct_structure(): void
    {
        $post = BlogPost::factory()->create([
            'title' => 'Test Post',
            'content' => 'Test content',
        ]);

        $meta = $this->service->getBlogPostMeta($post);

        $this->assertArrayHasKey('title', $meta);
        $this->assertArrayHasKey('description', $meta);
        $this->assertArrayHasKey('og_type', $meta);
        $this->assertEquals('article', $meta['og_type']);
    }

    public function test_get_blog_post_meta_includes_article_meta(): void
    {
        $post = BlogPost::factory()->create([
            'title' => 'Test Post',
            'published_at' => now(),
        ]);

        $meta = $this->service->getBlogPostMeta($post);

        $this->assertArrayHasKey('article_published_time', $meta);
    }

    public function test_get_default_meta_returns_default_structure(): void
    {
        $meta = $this->service->getDefaultMeta();

        $this->assertArrayHasKey('title', $meta);
        $this->assertArrayHasKey('description', $meta);
        $this->assertArrayHasKey('og_type', $meta);
        $this->assertEquals('website', $meta['og_type']);
    }

    public function test_generate_breadcrumb_schema_creates_correct_structure(): void
    {
        $items = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'About', 'url' => '/about'],
        ];

        $schema = $this->service->generateBreadcrumbSchema($items);

        $this->assertEquals('BreadcrumbList', $schema['@type']);
        $this->assertCount(2, $schema['itemListElement']);
        $this->assertEquals('Home', $schema['itemListElement'][0]['name']);
    }

    public function test_clear_page_cache_clears_cache(): void
    {
        $page = Page::factory()->create();

        // Populate cache
        $this->service->getPageMeta($page);
        $this->assertTrue(Cache::has("seo.page.{$page->id}"));

        $this->service->clearPageCache($page);

        // Cache should be cleared
        Cache::shouldReceive('forget')->with("seo.page.{$page->id}");
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
