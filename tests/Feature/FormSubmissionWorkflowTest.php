<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FormSubmissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        \Illuminate\Support\Facades\RateLimiter::clear('form-submission:127.0.0.1');
    }

    public function test_complete_form_submission_workflow(): void
    {
        $form = Form::factory()->create([
            'slug' => 'workflow-test',
            'email_to' => 'admin@example.com',
            'fields' => json_encode([
                ['name' => 'name', 'type' => 'text', 'required' => true, 'label' => 'Name'],
                ['name' => 'email', 'type' => 'email', 'required' => true, 'label' => 'Email'],
            ]),
        ]);

        // Step 1: Submit form
        $response = $this->postJson(route('forms.submit', $form->slug), [
            'name' => 'Workflow Test',
            'email' => 'workflow@example.com',
        ]);

        // Step 2: Verify response
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Step 3: Verify database storage
        $this->assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
        ]);

        // Step 4: Verify email sent
        Mail::assertSent(\App\Mail\FormSubmissionNotification::class, function ($mail) {
            return $mail->hasTo('admin@example.com');
        });

        // Step 5: Verify submission data
        $submission = FormSubmission::where('form_id', $form->id)->first();
        $this->assertNotNull($submission);
        $data = is_array($submission->data) ? $submission->data : json_decode($submission->data, true);
        $this->assertEquals('Workflow Test', $data['name']);
        $this->assertEquals('workflow@example.com', $data['email']);
    }
}
