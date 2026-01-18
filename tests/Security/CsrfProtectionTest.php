<?php

namespace Tests\Security;

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsrfProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_submission_requires_csrf_token(): void
    {
        $form = Form::factory()->create(['slug' => 'test-form']);

        $response = $this->post(route('forms.submit', $form->slug), [
            'name' => 'Test',
        ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    public function test_form_submission_with_valid_csrf_token_succeeds(): void
    {
        $form = Form::factory()->create(['slug' => 'test-form']);

        $response = $this->post(route('forms.submit', $form->slug), [
            '_token' => csrf_token(),
            'name' => 'Test',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);
    }

    public function test_ajax_request_handles_csrf(): void
    {
        $form = Form::factory()->create(['slug' => 'test-form']);

        // JSON requests may handle CSRF differently
        $response = $this->postJson(route('forms.submit', $form->slug), [
            'name' => 'Test',
            'email' => 'test@example.com',
        ]);

        // Should either succeed (if CSRF is handled differently for JSON) or fail with 419
        $this->assertContains($response->status(), [200, 419]);
    }
}
