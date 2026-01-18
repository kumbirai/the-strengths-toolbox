<?php

namespace Tests\Unit\Services;

use App\Models\BlogPost;
use App\Models\Page;
use App\Services\SchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SchemaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SchemaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SchemaService;
        Cache::flush();
    }

    public function test_generate_organization_schema_returns_valid_json_ld(): void
    {
        $schema = $this->service->getOrganizationSchema();

        $this->assertEquals('https://schema.org', $schema['@context']);
        $this->assertEquals('Organization', $schema['@type']);
        $this->assertArrayHasKey('name', $schema);
        $this->assertArrayHasKey('url', $schema);
        $this->assertArrayHasKey('logo', $schema);
    }

    public function test_generate_website_schema_returns_valid_structure(): void
    {
        $schema = $this->service->getWebSiteSchema();

        $this->assertEquals('https://schema.org', $schema['@context']);
        $this->assertEquals('WebSite', $schema['@type']);
        $this->assertArrayHasKey('name', $schema);
        $this->assertArrayHasKey('potentialAction', $schema);
    }

    public function test_generate_webpage_schema_includes_required_fields(): void
    {
        $page = Page::factory()->create([
            'title' => 'Test Page',
            'content' => 'Test content',
        ]);

        $schema = $this->service->getWebPageSchema($page);

        $this->assertEquals('https://schema.org', $schema['@context']);
        $this->assertEquals('WebPage', $schema['@type']);
        $this->assertEquals('Test Page', $schema['name']);
        $this->assertArrayHasKey('url', $schema);
    }

    public function test_generate_article_schema_includes_required_fields(): void
    {
        $post = BlogPost::factory()->create([
            'title' => 'Test Post',
            'content' => 'Test content',
        ]);

        $schema = $this->service->getArticleSchema($post);

        $this->assertEquals('https://schema.org', $schema['@context']);
        $this->assertEquals('Article', $schema['@type']);
        $this->assertEquals('Test Post', $schema['headline']);
        $this->assertArrayHasKey('author', $schema);
        $this->assertArrayHasKey('publisher', $schema);
    }

    public function test_generate_breadcrumb_schema_creates_correct_structure(): void
    {
        $items = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'About', 'url' => '/about'],
        ];

        $schema = $this->service->getBreadcrumbSchema($items);

        $this->assertEquals('https://schema.org', $schema['@context']);
        $this->assertEquals('BreadcrumbList', $schema['@type']);
        $this->assertCount(2, $schema['itemListElement']);
        $this->assertEquals(1, $schema['itemListElement'][0]['position']);
    }

    public function test_organization_schema_is_cached(): void
    {
        $schema1 = $this->service->getOrganizationSchema();
        $this->assertTrue(Cache::has('schema.organization'));

        $schema2 = $this->service->getOrganizationSchema();
        $this->assertEquals($schema1, $schema2);
    }

    public function test_clear_cache_clears_schema_cache(): void
    {
        $this->service->getOrganizationSchema();
        $this->assertTrue(Cache::has('schema.organization'));

        $this->service->clearCache();

        Cache::shouldReceive('forget')->with('schema.organization');
        Cache::shouldReceive('forget')->with('schema.website');
    }
}
