<?php

namespace App\Services;

use App\Mail\ContactFormAcknowledgmentMail;
use App\Mail\ContactFormMail;
use App\Mail\EbookWelcomeMail;
use App\Mail\FormSubmissionNotification;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Send form submission notification email
     */
    public function sendFormSubmissionNotification(
        string $to,
        Form $form,
        FormSubmission $submission
    ): void {
        try {
            Mail::to($to)->send(
                new FormSubmissionNotification($form, $submission)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send form submission email', [
                'to' => $to,
                'form_id' => $form->id,
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send contact form email
     */
    public function sendContactForm(array $data): void
    {
        try {
            $to = config('mail.contact_to', config('mail.from.address'));

            Mail::to($to)->send(new ContactFormMail($data));
        } catch (\Exception $e) {
            Log::error('Failed to send contact form email', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send contact form acknowledgment email to submitter
     */
    public function sendContactFormAcknowledgment(array $data): void
    {
        try {
            if (! isset($data['email']) || empty($data['email'])) {
                Log::warning('Cannot send contact form acknowledgment: email not provided', [
                    'data' => $data,
                ]);

                return;
            }

            Mail::to($data['email'])->send(new ContactFormAcknowledgmentMail($data));
        } catch (\Exception $e) {
            // Log error but don't throw - acknowledgment email failure shouldn't block form submission
            Log::error('Failed to send contact form acknowledgment email', [
                'email' => $data['email'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send eBook welcome email to subscriber
     */
    public function sendEbookWelcome(Subscriber $subscriber, string $downloadUrl): void
    {
        try {
            Mail::to($subscriber->email)->send(
                new EbookWelcomeMail($subscriber, $downloadUrl)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send eBook welcome email', [
                'subscriber_id' => $subscriber->id,
                'email' => $subscriber->email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send email to multiple recipients
     *
     * @param  array|string  $to
     */
    public function send($to, string $subject, string $view, array $data = []): void
    {
        try {
            Mail::send($view, $data, function ($message) use ($to, $subject) {
                if (is_array($to)) {
                    $message->to($to);
                } else {
                    $message->to($to);
                }
                $message->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
