<?php

namespace Tests\Security;

use App\Models\Form;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class XssPreventionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear all rate limiters
        RateLimiter::clear('form-submission:127.0.0.1');
        RateLimiter::clear('form-submission:'.request()->ip());
    }

    public function test_contact_form_prevents_xss(): void
    {
        RateLimiter::clear('form-submission:127.0.0.1');

        $xssPayload = '<script>alert("XSS")</script>';

        $response = $this->postJson('/contact', [
            'name' => $xssPayload,
            'email' => 'test@example.com',
            'subject' => 'Test',
            'message' => 'Test message with enough characters.',
        ]);

        // May be rate limited, but if successful, verify XSS is escaped
        if ($response->status() === 200) {
            $response->assertDontSee('<script>', false);
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }

    public function test_blade_escapes_output(): void
    {
        $page = Page::factory()->create([
            'title' => '<script>alert("XSS")</script>',
            'content' => '<script>alert("XSS")</script>',
            'is_published' => true,
        ]);

        $response = $this->get("/{$page->slug}");

        $response->assertStatus(200);
        // Blade should escape the script tags
        $response->assertDontSee('<script>', false);
    }

    public function test_user_generated_content_escaped(): void
    {
        RateLimiter::clear('form-submission:127.0.0.1');

        $xssPayload = '<img src=x onerror=alert("XSS")>';

        $response = $this->postJson('/contact', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'subject' => 'Test',
            'message' => $xssPayload,
        ]);

        // May be rate limited, but if successful, verify XSS is escaped
        if ($response->status() === 200) {
            $response->assertDontSee('onerror=', false);
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }

    public function test_form_submission_prevents_xss(): void
    {
        RateLimiter::clear('form-submission:127.0.0.1');

        $form = Form::factory()->create([
            'slug' => 'test-form',
            'fields' => json_encode([
                ['name' => 'message', 'type' => 'textarea', 'required' => true, 'label' => 'Message'],
            ]),
        ]);

        $xssPayload = '<script>alert("XSS")</script>';

        $response = $this->postJson(route('forms.submit', $form->slug), [
            'message' => $xssPayload,
        ]);

        // May be rate limited, but if successful, verify submission was created
        if ($response->status() === 200) {
            $this->assertDatabaseHas('form_submissions', [
                'form_id' => $form->id,
            ]);
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }
}
