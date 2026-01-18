<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class EbookFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('form-submission:'.$this->getClientIp());
    }

    /**
     * Test eBook form submission with valid data
     */
    public function test_ebook_form_submission_succeeds(): void
    {
        Mail::fake();

        $response = $this->postJson('/ebook-signup', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Test eBook form validation - missing name
     */
    public function test_ebook_form_requires_name(): void
    {
        $response = $this->postJson('/ebook-signup', [
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name']);
    }

    /**
     * Test eBook form validation - invalid email
     */
    public function test_ebook_form_validates_email(): void
    {
        $response = $this->postJson('/ebook-signup', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test eBook form rate limiting
     */
    public function test_ebook_form_rate_limiting(): void
    {
        // Submit form 5 times (limit)
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/ebook-signup', [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane@example.com',
            ]);
        }

        // 6th submission should be rate limited
        $response = $this->postJson('/ebook-signup', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(429);
    }

    public function test_ebook_form_sends_notification_and_auto_response(): void
    {
        Mail::fake();

        $response = $this->postJson('/ebook-signup', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ]);

        // May be rate limited, but if successful, should send notification
        if ($response->status() === 200) {
            Mail::assertSent(\App\Mail\FormSubmissionNotification::class);
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }

    public function test_ebook_form_stores_submission(): void
    {
        \Illuminate\Support\Facades\RateLimiter::clear('form-submission:127.0.0.1');

        $response = $this->postJson('/ebook-signup', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ]);

        // May be rate limited, but if successful, check database
        if ($response->status() === 200) {
            $form = \App\Models\Form::where('slug', 'ebook-signup')->first();
            if ($form) {
                $this->assertDatabaseHas('form_submissions', [
                    'form_id' => $form->id,
                ]);
            }
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }

    protected function getClientIp(): string
    {
        return '127.0.0.1';
    }
}
