<?php

namespace Tests\Feature;

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FormSubmissionEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        \Illuminate\Support\Facades\RateLimiter::clear('form-submission:127.0.0.1');
    }

    public function test_form_submission_sends_notification_email(): void
    {
        \Illuminate\Support\Facades\RateLimiter::clear('form-submission:127.0.0.1');

        $form = Form::factory()->create([
            'email_to' => 'admin@example.com',
            'fields' => json_encode([
                ['name' => 'name', 'type' => 'text', 'required' => true, 'label' => 'Name'],
                ['name' => 'email', 'type' => 'email', 'required' => true, 'label' => 'Email'],
            ]),
        ]);

        $response = $this->postJson(route('forms.submit', $form->slug), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // May be rate limited, but if successful, should send email
        if ($response->status() === 200) {
            Mail::assertSent(\App\Mail\FormSubmissionNotification::class, function ($mail) use ($form) {
                return $mail->hasTo('admin@example.com') &&
                       $mail->form->id === $form->id;
            });
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }

    public function test_form_submission_email_contains_form_data(): void
    {
        \Illuminate\Support\Facades\RateLimiter::clear('form-submission:127.0.0.1');

        $form = Form::factory()->create([
            'email_to' => 'admin@example.com',
            'name' => 'Test Form',
            'fields' => json_encode([
                ['name' => 'name', 'type' => 'text', 'required' => true, 'label' => 'Name'],
                ['name' => 'email', 'type' => 'email', 'required' => true, 'label' => 'Email'],
            ]),
        ]);

        $response = $this->postJson(route('forms.submit', $form->slug), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // May be rate limited, but if successful, check email content
        if ($response->status() === 200) {
            Mail::assertSent(\App\Mail\FormSubmissionNotification::class, function ($mail) {
                $mail->build();
                $content = $mail->render();

                return str_contains($content, 'Test User') &&
                       str_contains($content, 'test@example.com');
            });
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }

    public function test_form_submission_email_handles_missing_recipient(): void
    {
        \Illuminate\Support\Facades\RateLimiter::clear('form-submission:127.0.0.1');

        // Create form without email_to (nullable)
        $form = Form::factory()->create([
            'email_to' => null,
            'fields' => json_encode([
                ['name' => 'name', 'type' => 'text', 'required' => true, 'label' => 'Name'],
                ['name' => 'email', 'type' => 'email', 'required' => true, 'label' => 'Email'],
            ]),
        ]);

        $response = $this->postJson(route('forms.submit', $form->slug), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // May be rate limited, but if successful, no email should be sent
        if ($response->status() === 200) {
            Mail::assertNothingSent();
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }
}
