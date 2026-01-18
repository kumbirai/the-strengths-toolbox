<?php

namespace Tests\Unit\Repositories;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Repositories\FormRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected FormRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FormRepository(new Form);
    }

    public function test_find_by_slug_returns_form(): void
    {
        $form = Form::factory()->create([
            'slug' => 'test-form',
            'is_active' => true,
        ]);

        $result = $this->repository->findBySlug('test-form');

        $this->assertNotNull($result);
        $this->assertEquals($form->id, $result->id);
    }

    public function test_find_by_slug_returns_form_even_if_inactive(): void
    {
        $form = Form::factory()->create([
            'slug' => 'inactive-form',
            'is_active' => false,
        ]);

        $result = $this->repository->findBySlug('inactive-form');

        // findBySlug doesn't filter by active status, use findActiveBySlug for that
        $this->assertNotNull($result);
        $this->assertEquals($form->id, $result->id);
    }

    public function test_get_all_active_returns_only_active_forms(): void
    {
        Form::factory()->count(3)->create(['is_active' => true]);
        Form::factory()->count(2)->create(['is_active' => false]);

        $result = $this->repository->getAllActive();

        $this->assertCount(3, $result);
        $result->each(function ($form) {
            $this->assertTrue($form->is_active);
        });
    }

    public function test_get_all_active_excludes_inactive_forms(): void
    {
        Form::factory()->create(['is_active' => true]);
        Form::factory()->create(['is_active' => false]);

        $result = $this->repository->getAllActive();

        $this->assertCount(1, $result);
    }

    public function test_create_creates_new_form(): void
    {
        $data = [
            'name' => 'Test Form',
            'slug' => 'test-form',
            'is_active' => true,
            'email_to' => 'test@example.com',
            'fields' => [
                ['name' => 'name', 'type' => 'text', 'required' => true, 'label' => 'Name'],
            ],
        ];

        $form = $this->repository->create($data);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertDatabaseHas('forms', [
            'name' => 'Test Form',
        ]);
    }

    public function test_update_updates_form(): void
    {
        $form = Form::factory()->create(['name' => 'Original Form']);

        $this->repository->updateById($form->id, ['name' => 'Updated Form']);

        $this->assertDatabaseHas('forms', [
            'id' => $form->id,
            'name' => 'Updated Form',
        ]);
    }

    public function test_delete_removes_form(): void
    {
        $form = Form::factory()->create();

        $result = $this->repository->deleteById($form->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('forms', ['id' => $form->id]);
    }

    public function test_form_has_submissions_relationship(): void
    {
        $form = Form::factory()->create();
        $submission = FormSubmission::factory()->create(['form_id' => $form->id]);

        $this->assertTrue($form->submissions->contains($submission));
        $this->assertEquals(1, $form->submissions->count());
    }

    public function test_form_submissions_can_be_paginated(): void
    {
        $form = Form::factory()->create();
        FormSubmission::factory()->count(25)->create(['form_id' => $form->id]);

        $result = $form->submissions()->paginate(10);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_scope_active_filters_active_forms(): void
    {
        $active = Form::factory()->create(['is_active' => true]);
        $inactive = Form::factory()->create(['is_active' => false]);

        $results = Form::active()->get();

        $this->assertTrue($results->contains($active));
        $this->assertFalse($results->contains($inactive));
    }

    public function test_slug_exists_returns_true_when_exists(): void
    {
        Form::factory()->create(['slug' => 'existing-form']);

        $this->assertTrue($this->repository->slugExists('existing-form'));
        $this->assertFalse($this->repository->slugExists('non-existing-form'));
    }
}
