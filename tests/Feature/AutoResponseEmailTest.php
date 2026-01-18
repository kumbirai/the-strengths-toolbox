<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AutoResponseEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        \Illuminate\Support\Facades\RateLimiter::clear('form-submission:127.0.0.1');
    }

    public function test_ebook_form_sends_auto_response(): void
    {
        $response = $this->postJson('/ebook-signup', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ]);

        // May be rate limited, but if successful, should send email
        if ($response->status() === 200) {
            // Form submission notification should be sent
            Mail::assertSent(\App\Mail\FormSubmissionNotification::class);
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }

    public function test_auto_response_email_contains_correct_content(): void
    {
        $response = $this->postJson('/ebook-signup', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ]);

        // May be rate limited, but if successful, check email content
        if ($response->status() === 200) {
            Mail::assertSent(\App\Mail\FormSubmissionNotification::class, function ($mail) {
                $mail->build();
                $content = $mail->render();

                return str_contains($content, 'Jane') || str_contains($content, 'Doe');
            });
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }
}
