<?php

namespace Tests\Unit\Models;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_submission_belongs_to_form(): void
    {
        $form = Form::factory()->create();
        $submission = FormSubmission::factory()->create(['form_id' => $form->id]);

        $this->assertInstanceOf(Form::class, $submission->form);
        $this->assertEquals($form->id, $submission->form->id);
    }

    public function test_mark_as_read_updates_is_read(): void
    {
        $submission = FormSubmission::factory()->create(['is_read' => false]);

        $submission->markAsRead();

        $this->assertTrue($submission->fresh()->is_read);
    }
}
