<?php

namespace Tests\Unit\Services;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Services\ChatbotContextService;
use App\Services\ChatbotErrorHandler;
use App\Services\ChatbotRateLimitService;
use App\Services\ChatbotService;
use App\Services\OpenAIClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ChatbotServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ChatbotService $service;

    protected OpenAIClient $openAIClient;

    protected ChatbotContextService $contextService;

    protected ChatbotRateLimitService $rateLimitService;

    protected ChatbotErrorHandler $errorHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->openAIClient = Mockery::mock(OpenAIClient::class);
        $this->contextService = Mockery::mock(ChatbotContextService::class);
        $this->rateLimitService = Mockery::mock(ChatbotRateLimitService::class);
        $this->errorHandler = Mockery::mock(ChatbotErrorHandler::class);

        $this->service = new ChatbotService(
            $this->openAIClient,
            $this->contextService,
            $this->rateLimitService,
            $this->errorHandler
        );
    }

    public function test_create_conversation_creates_new_conversation(): void
    {
        $conversation = $this->service->createConversation('test-session-123');

        $this->assertInstanceOf(ChatbotConversation::class, $conversation);
        $this->assertEquals('test-session-123', $conversation->session_id);
        $this->assertDatabaseHas('chatbot_conversations', [
            'session_id' => 'test-session-123',
        ]);
    }

    public function test_get_or_create_conversation_returns_existing(): void
    {
        $conversation = ChatbotConversation::factory()->create([
            'session_id' => 'test-session-123',
        ]);

        $result = $this->service->getOrCreateConversation(
            $conversation->id,
            'test-session-123'
        );

        $this->assertEquals($conversation->id, $result->id);
    }

    public function test_get_or_create_conversation_creates_new_when_not_exists(): void
    {
        $result = $this->service->getOrCreateConversation(
            null,
            'new-session-123'
        );

        $this->assertInstanceOf(ChatbotConversation::class, $result);
        $this->assertEquals('new-session-123', $result->session_id);
    }

    public function test_send_message_saves_user_message(): void
    {
        $conversation = ChatbotConversation::factory()->create();

        $this->openAIClient->shouldReceive('isConfigured')->andReturn(false);
        $this->rateLimitService->shouldReceive('checkRateLimit')
            ->andReturn(['allowed' => true, 'remaining' => 10, 'reset_at' => now()->timestamp]);

        $result = $this->service->sendMessage(
            $conversation->id,
            'Test message',
            'test-session'
        );

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('chatbot_messages', [
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'message' => 'Test message',
        ]);
    }

    public function test_send_message_calls_openai_client_when_configured(): void
    {
        $conversation = ChatbotConversation::factory()->create();

        $this->openAIClient->shouldReceive('isConfigured')->andReturn(true);
        $this->rateLimitService->shouldReceive('checkRateLimit')
            ->andReturn(['allowed' => true, 'remaining' => 10, 'reset_at' => now()->timestamp]);

        $this->contextService->shouldReceive('buildContext')
            ->andReturn([]);
        $this->contextService->shouldReceive('getSystemPrompt')
            ->andReturn('System prompt');
        $this->contextService->shouldReceive('validateContext')
            ->andReturn(true);
        $this->contextService->shouldReceive('clearContextCache');

        $this->openAIClient->shouldReceive('buildMessagesArray')
            ->andReturn([]);
        $this->openAIClient->shouldReceive('chatCompletion')
            ->andReturn([
                'content' => 'AI response',
                'tokens_used' => 100,
            ]);

        $result = $this->service->sendMessage(
            $conversation->id,
            'Test message',
            'test-session'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('AI response', $result['message']);
    }

    public function test_send_message_respects_rate_limiting(): void
    {
        $conversation = ChatbotConversation::factory()->create();

        $this->rateLimitService->shouldReceive('checkRateLimit')
            ->andReturn([
                'allowed' => false,
                'remaining' => 0,
                'reset_at' => now()->addMinute()->timestamp,
                'limit' => 10,
                'type' => 'session',
            ]);

        $result = $this->service->sendMessage(
            $conversation->id,
            'Test message',
            'test-session'
        );

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('rate_limit', $result);
    }

    public function test_get_conversation_returns_with_messages(): void
    {
        $conversation = ChatbotConversation::factory()->create();
        ChatbotMessage::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
        ]);

        $result = $this->service->getConversation($conversation->id);

        $this->assertNotNull($result);
        $this->assertEquals($conversation->id, $result->id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
