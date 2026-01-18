<?php

namespace Tests\Unit\Models;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_has_submissions_relationship(): void
    {
        $form = Form::factory()->create();
        $submission = FormSubmission::factory()->create(['form_id' => $form->id]);

        $this->assertTrue($form->submissions->contains($submission));
        $this->assertEquals(1, $form->submissions->count());
    }

    public function test_scope_active_returns_only_active_forms(): void
    {
        $active = Form::factory()->create(['is_active' => true]);
        $inactive = Form::factory()->create(['is_active' => false]);

        $results = Form::active()->get();

        $this->assertTrue($results->contains($active));
        $this->assertFalse($results->contains($inactive));
    }
}
