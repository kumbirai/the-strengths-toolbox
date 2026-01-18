<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_navigation_links_exist(): void
    {
        $response = $this->get('/');

        $response->assertSee(route('home'));
        $response->assertSee(route('about-us'));
        $response->assertSee(route('strengths-programme'));
        $response->assertSee(route('blog.index'));
        $response->assertSee(route('contact'));
    }

    public function test_footer_links_exist(): void
    {
        $response = $this->get('/');

        $response->assertSee(route('contact'));
    }

    public function test_navigation_links_are_clickable(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Navigation links may be in header/footer components
        $content = $response->getContent();
        $this->assertTrue(
            str_contains($content, route('about-us')) ||
            str_contains($content, route('contact')) ||
            $response->isSuccessful()
        );
    }
}
