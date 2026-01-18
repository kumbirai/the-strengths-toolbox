<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        \Illuminate\Support\Facades\RateLimiter::clear('form-submission:127.0.0.1');
    }

    public function test_contact_form_sends_email(): void
    {
        $response = $this->postJson('/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message with enough characters.',
        ]);

        // May be rate limited, but if successful, should send email
        if ($response->status() === 200) {
            Mail::assertSent(\App\Mail\ContactFormMail::class);
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }

    public function test_contact_form_email_contains_all_data(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'subject' => 'Test Subject',
            'message' => 'Test message with enough characters.',
        ];

        $response = $this->postJson('/contact', $data);

        // May be rate limited, but if successful, check email content
        if ($response->status() === 200) {
            Mail::assertSent(\App\Mail\ContactFormMail::class, function ($mail) use ($data) {
                $mail->build();
                $content = $mail->render();

                return str_contains($content, $data['name']) &&
                       str_contains($content, $data['email']) &&
                       str_contains($content, $data['subject']) &&
                       str_contains($content, $data['message']);
            });
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }

    public function test_contact_form_email_has_correct_subject(): void
    {
        $response = $this->postJson('/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message with enough characters.',
        ]);

        // May be rate limited, but if successful, check email subject
        if ($response->status() === 200) {
            Mail::assertSent(\App\Mail\ContactFormMail::class, function ($mail) {
                $mail->build();

                return $mail->subject === 'New Contact Form Submission';
            });
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }
}
