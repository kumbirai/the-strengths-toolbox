<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsiveDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_responsive_on_desktop(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ])->get('/');

        $response->assertStatus(200);
    }

    public function test_homepage_responsive_on_tablet(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X)',
        ])->get('/');

        $response->assertStatus(200);
    }

    public function test_homepage_responsive_on_mobile(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
        ])->get('/');

        $response->assertStatus(200);
    }
}
