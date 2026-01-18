<?php

namespace Tests\Unit\Services;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Repositories\FormRepository;
use App\Services\EmailService;
use App\Services\FormService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class FormServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FormService $formService;

    protected FormRepository $formRepository;

    protected EmailService $emailService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formRepository = new FormRepository(new \App\Models\Form);
        $this->emailService = Mockery::mock(EmailService::class);
        $this->formService = new FormService($this->formRepository, $this->emailService);
    }

    /**
     * Test form service gets form by slug
     */
    public function test_get_by_slug_returns_form(): void
    {
        $form = Form::factory()->create([
            'slug' => 'test-form',
            'is_active' => true,
        ]);

        $result = $this->formService->getBySlug('test-form');

        $this->assertNotNull($result);
        $this->assertEquals($form->id, $result->id);
    }

    /**
     * Test form service returns null for non-existent slug
     */
    public function test_get_by_slug_returns_null_for_non_existent(): void
    {
        $result = $this->formService->getBySlug('non-existent');

        $this->assertNull($result);
    }

    /**
     * Test form submission creates submission record
     */
    public function test_submit_creates_submission(): void
    {
        $form = Form::factory()->create([
            'is_active' => true,
            'fields' => json_encode([
                ['name' => 'name', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'type' => 'email', 'required' => true],
            ]),
        ]);

        $this->emailService->shouldReceive('sendFormSubmissionNotification')
            ->once()
            ->andReturn(true);

        $submission = $this->formService->submit($form->id, [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(FormSubmission::class, $submission);
        $this->assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
        ]);
    }

    /**
     * Test form submission validates required fields
     */
    public function test_submit_validates_required_fields(): void
    {
        $form = Form::factory()->create([
            'is_active' => true,
            'fields' => json_encode([
                ['name' => 'name', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'type' => 'email', 'required' => true],
            ]),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Field 'name' is required");

        $this->formService->submit($form->id, [
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test form submission validates email format
     */
    public function test_submit_validates_email_format(): void
    {
        $form = Form::factory()->create([
            'is_active' => true,
            'fields' => json_encode([
                ['name' => 'email', 'type' => 'email', 'required' => true],
            ]),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Field 'email' must be a valid email address");

        $this->formService->submit($form->id, [
            'email' => 'invalid-email',
        ]);
    }

    public function test_get_all_active_returns_only_active_forms(): void
    {
        Form::factory()->count(3)->create(['is_active' => true]);
        Form::factory()->count(2)->create(['is_active' => false]);

        $result = $this->formService->getAllActive();

        $this->assertCount(3, $result);
        $result->each(function ($form) {
            $this->assertTrue($form->is_active);
        });
    }

    public function test_update_updates_form(): void
    {
        $form = Form::factory()->create([
            'name' => 'Original Form',
        ]);

        $updated = $this->formService->update($form->id, [
            'name' => 'Updated Form',
        ]);

        $this->assertEquals('Updated Form', $updated->name);
        $this->assertDatabaseHas('forms', [
            'id' => $form->id,
            'name' => 'Updated Form',
        ]);
    }

    public function test_update_throws_exception_when_form_not_found(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Form with ID 99999 not found');

        $this->formService->update(99999, ['name' => 'Test']);
    }

    public function test_delete_removes_form(): void
    {
        $form = Form::factory()->create();

        $result = $this->formService->delete($form->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('forms', ['id' => $form->id]);
    }

    public function test_delete_throws_exception_when_form_not_found(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Form with ID 99999 not found');

        $this->formService->delete(99999);
    }

    public function test_generate_unique_slug_creates_unique_slug(): void
    {
        Form::factory()->create(['slug' => 'test-form']);

        $form = $this->formService->create([
            'name' => 'Test Form',
            'email_to' => 'test@example.com',
            'fields' => [
                ['name' => 'name', 'type' => 'text', 'required' => true, 'label' => 'Name'],
            ],
        ]);

        $this->assertEquals('test-form-1', $form->slug);
    }

    public function test_get_submissions_returns_paginated_submissions(): void
    {
        $form = Form::factory()->create();
        FormSubmission::factory()->count(25)->create(['form_id' => $form->id]);

        $result = $this->formService->getSubmissions($form->id, 10);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
