<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaticPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_us_page_displays(): void
    {
        $page = Page::factory()->create([
            'slug' => 'about-us',
            'title' => 'About Us',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/about-us');

        $response->assertStatus(200);
        // If CMS page exists, it will show; otherwise custom view
        $this->assertTrue($response->isSuccessful());
    }

    public function test_strengths_programme_page_displays(): void
    {
        $page = Page::factory()->create([
            'slug' => 'strengths-programme',
            'title' => 'Strengths Programme',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/strengths-programme');

        $response->assertStatus(200);
        // If CMS page exists, it will show; otherwise custom view
        $this->assertTrue($response->isSuccessful());
    }

    public function test_contact_page_displays_form(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertSee('Contact');
        $response->assertSee('name');
        $response->assertSee('email');
        $response->assertSee('message');
    }
}
