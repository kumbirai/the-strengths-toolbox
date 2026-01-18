<?php

namespace App\Mail;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FormSubmissionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Form $form;

    public FormSubmission $submission;

    public function __construct(Form $form, FormSubmission $submission)
    {
        $this->form = $form;
        $this->submission = $submission;
    }

    public function build()
    {
        $submissionData = json_decode($this->submission->data, true);

        return $this->subject("New {$this->form->name} Submission")
            ->view('emails.form-submission')
            ->with([
                'form' => $this->form,
                'submission' => $this->submission,
                'data' => $submissionData,
            ]);
    }
}
