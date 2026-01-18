<?php

namespace Tests\Feature\Admin;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin, 'admin');
    }

    public function test_admin_can_view_forms_list(): void
    {
        Form::factory()->count(5)->create();

        $response = $this->get('/admin/forms');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_form(): void
    {
        $response = $this->post('/admin/forms', [
            'name' => 'Test Form',
            'slug' => 'test-form',
            'fields' => [
                ['name' => 'name', 'type' => 'text', 'required' => true, 'label' => 'Name'],
                ['name' => 'email', 'type' => 'email', 'required' => true, 'label' => 'Email'],
            ],
            'email_to' => 'admin@example.com',
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/forms');
        $this->assertDatabaseHas('forms', [
            'slug' => 'test-form',
        ]);
    }

    public function test_admin_can_view_form_submissions(): void
    {
        $form = Form::factory()->create();
        FormSubmission::factory()->count(5)->create(['form_id' => $form->id]);

        $response = $this->get("/admin/forms/{$form->id}/submissions");

        $response->assertStatus(200);
    }
}
