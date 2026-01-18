<?php

namespace Tests\Unit\Models;

use App\Models\ChatbotPrompt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotPromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_active_returns_only_active_prompts(): void
    {
        $active = ChatbotPrompt::create([
            'name' => 'Active Prompt',
            'template' => 'Test template',
            'is_active' => true,
        ]);
        $inactive = ChatbotPrompt::create([
            'name' => 'Inactive Prompt',
            'template' => 'Test template',
            'is_active' => false,
        ]);

        $results = ChatbotPrompt::where('is_active', true)->get();

        $this->assertTrue($results->contains($active));
        $this->assertFalse($results->contains($inactive));
    }

    public function test_get_default_returns_default_prompt(): void
    {
        ChatbotPrompt::create([
            'name' => 'Default Prompt',
            'template' => 'Default template',
            'is_default' => true,
            'is_active' => true,
            'version' => 1,
        ]);

        $default = ChatbotPrompt::getDefault();

        $this->assertNotNull($default);
        $this->assertTrue($default->is_default);
        $this->assertTrue($default->is_active);
    }

    public function test_render_replaces_placeholders(): void
    {
        $prompt = ChatbotPrompt::create([
            'name' => 'Test Prompt',
            'template' => 'Hello {name}, welcome to {company}',
            'variables' => ['name' => 'John'],
        ]);

        $rendered = $prompt->render(['company' => 'Test Company']);

        $this->assertStringContainsString('John', $rendered);
        $this->assertStringContainsString('Test Company', $rendered);
        $this->assertStringNotContainsString('{', $rendered);
    }
}
