<?php

namespace Tests\Unit\Services;

use App\Models\Form;
use App\Services\FormRenderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormRenderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FormRenderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FormRenderService;
    }

    public function test_render_generates_form_html(): void
    {
        $form = Form::factory()->create([
            'slug' => 'test-form',
            'is_active' => true,
            'fields' => json_encode([
                ['name' => 'name', 'type' => 'text', 'required' => true, 'label' => 'Name'],
            ]),
        ]);

        $html = $this->service->render($form);

        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('method="POST"', $html);
        $this->assertStringContainsString('name', $html);
    }

    public function test_render_returns_inactive_message_for_inactive_form(): void
    {
        $form = Form::factory()->create([
            'is_active' => false,
        ]);

        $html = $this->service->render($form);

        $this->assertStringContainsString('inactive', $html);
    }

    public function test_render_field_generates_correct_input_type(): void
    {
        $form = Form::factory()->create([
            'is_active' => true,
            'fields' => json_encode([
                ['name' => 'email', 'type' => 'email', 'required' => true, 'label' => 'Email'],
            ]),
        ]);

        $html = $this->service->render($form);

        $this->assertStringContainsString('type="email"', $html);
    }

    public function test_render_field_includes_required_attribute(): void
    {
        $form = Form::factory()->create([
            'is_active' => true,
            'fields' => json_encode([
                ['name' => 'name', 'type' => 'text', 'required' => true, 'label' => 'Name'],
            ]),
        ]);

        $html = $this->service->render($form);

        $this->assertStringContainsString('required', $html);
    }

    public function test_render_handles_textarea_field(): void
    {
        $form = Form::factory()->create([
            'is_active' => true,
            'fields' => json_encode([
                ['name' => 'message', 'type' => 'textarea', 'required' => true, 'label' => 'Message'],
            ]),
        ]);

        $html = $this->service->render($form);

        $this->assertStringContainsString('<textarea', $html);
    }

    public function test_render_handles_select_field(): void
    {
        $form = Form::factory()->create([
            'is_active' => true,
            'fields' => json_encode([
                [
                    'name' => 'option',
                    'type' => 'select',
                    'required' => true,
                    'label' => 'Option',
                    'options' => ['Option 1', 'Option 2'],
                ],
            ]),
        ]);

        $html = $this->service->render($form);

        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('Option 1', $html);
    }
}
