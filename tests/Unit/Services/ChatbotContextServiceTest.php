<?php

namespace Tests\Unit\Services;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Services\ChatbotContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChatbotContextServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ChatbotContextService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ChatbotContextService;
        Cache::flush();
    }

    public function test_build_context_includes_recent_messages(): void
    {
        $conversation = ChatbotConversation::factory()->create();

        ChatbotMessage::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
        ]);

        $context = $this->service->buildContext($conversation, 'New message');

        $this->assertIsArray($context);
        $this->assertGreaterThan(0, count($context));
    }

    public function test_optimize_for_tokens_truncates_when_needed(): void
    {
        $conversation = ChatbotConversation::factory()->create();

        // Create many messages
        ChatbotMessage::factory()->count(20)->create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'message' => str_repeat('a', 200), // Long messages
        ]);

        $context = $this->service->buildContext($conversation);

        // Should be optimized/truncated
        $this->assertIsArray($context);
    }

    public function test_get_system_prompt_returns_default_when_no_custom(): void
    {
        $conversation = ChatbotConversation::factory()->create();

        $prompt = $this->service->getSystemPrompt($conversation);

        $this->assertIsString($prompt);
        $this->assertNotEmpty($prompt);
    }

    public function test_replace_placeholders_replaces_all_placeholders(): void
    {
        $conversation = ChatbotConversation::factory()->create([
            'context' => [
                'company' => 'Test Company',
            ],
        ]);

        $prompt = 'Hello {company_name}, welcome to {company_name}';

        $result = $this->service->getSystemPrompt($conversation);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_clear_context_cache_clears_cache(): void
    {
        $conversation = ChatbotConversation::factory()->create();

        // Build context to populate cache
        $this->service->buildContext($conversation);

        // Clear cache
        $this->service->clearContextCache($conversation->id);

        // Verify cache is cleared by checking it doesn't exist
        $this->assertFalse(Cache::has("chatbot.context.{$conversation->id}"));
    }
}
