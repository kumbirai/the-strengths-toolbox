<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormResponsiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_responsive_on_mobile(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
        ])->get('/contact');

        $response->assertStatus(200);
    }
}
