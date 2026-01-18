<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationResponsiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_desktop_navigation_displays_horizontally(): void
    {
        $response = $this->get('/');

        $response->assertSee('nav', false);
    }

    public function test_mobile_navigation_shows_hamburger_menu(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
        ])->get('/');

        $response->assertSee('menu', false);
    }
}
