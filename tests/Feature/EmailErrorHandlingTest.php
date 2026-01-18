<?php

namespace Tests\Feature;

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_form_submission_succeeds_when_email_fails(): void
    {
        Mail::shouldReceive('send')->andThrow(new \Exception('Email sending failed'));

        $form = Form::factory()->create([
            'email_to' => 'admin@example.com',
        ]);

        // Form submission should still succeed
        $response = $this->postJson(route('forms.submit', $form->slug), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);

        // Submission should be stored
        $this->assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
        ]);
    }

    public function test_email_error_is_logged(): void
    {
        Log::spy();
        Mail::shouldReceive('send')->andThrow(new \Exception('Email sending failed'));

        $form = Form::factory()->create([
            'email_to' => 'admin@example.com',
        ]);

        $this->postJson(route('forms.submit', $form->slug), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Log::shouldHaveReceived('error')->with(
            'Failed to send form submission email',
            \Mockery::on(function ($context) {
                return isset($context['form_id']) && isset($context['error']);
            })
        );
    }
}
