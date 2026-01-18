<?php

namespace Tests\Unit\Services;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class EmailServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EmailService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->service = new EmailService;
    }

    public function test_send_form_submission_notification_sends_email(): void
    {
        $form = Form::factory()->create();
        $submission = FormSubmission::factory()->create(['form_id' => $form->id]);

        $this->service->sendFormSubmissionNotification(
            'admin@example.com',
            $form,
            $submission
        );

        Mail::assertSent(\App\Mail\FormSubmissionNotification::class, function ($mail) {
            return $mail->hasTo('admin@example.com');
        });
    }

    public function test_send_contact_form_sends_email(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message',
        ];

        $this->service->sendContactForm($data);

        Mail::assertSent(\App\Mail\ContactFormMail::class);
    }

    public function test_send_email_logs_error_on_failure(): void
    {
        Log::spy();
        Mail::shouldReceive('send')->andThrow(new \Exception('Email failed'));

        $this->expectException(\Exception::class);

        try {
            $this->service->send('test@example.com', 'Subject', 'view', []);
        } catch (\Exception $e) {
            Log::shouldHaveReceived('error')->once();
            throw $e;
        }
    }

    public function test_send_form_submission_notification_logs_error_on_failure(): void
    {
        Log::spy();
        Mail::shouldReceive('to')->andThrow(new \Exception('Email failed'));

        $form = Form::factory()->create();
        $submission = FormSubmission::factory()->create(['form_id' => $form->id]);

        $this->expectException(\Exception::class);

        try {
            $this->service->sendFormSubmissionNotification(
                'admin@example.com',
                $form,
                $submission
            );
        } catch (\Exception $e) {
            Log::shouldHaveReceived('error')->once();
            throw $e;
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
