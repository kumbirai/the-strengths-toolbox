<?php

namespace Tests\Unit\Models;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_belongs_to_conversation(): void
    {
        $conversation = ChatbotConversation::factory()->create();
        $message = ChatbotMessage::factory()->create(['conversation_id' => $conversation->id]);

        $this->assertInstanceOf(ChatbotConversation::class, $message->conversation);
        $this->assertEquals($conversation->id, $message->conversation->id);
    }

    public function test_scope_by_role_filters_by_role(): void
    {
        $conversation = ChatbotConversation::factory()->create();
        $userMessage = ChatbotMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
        ]);
        $assistantMessage = ChatbotMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
        ]);

        $results = ChatbotMessage::byRole('user')->get();

        $this->assertTrue($results->contains($userMessage));
        $this->assertFalse($results->contains($assistantMessage));
    }

    public function test_get_length_attribute_returns_message_length(): void
    {
        $message = ChatbotMessage::factory()->create(['message' => 'Test message']);

        $this->assertEquals(12, $message->length);
    }

    public function test_is_user_message_returns_true_for_user(): void
    {
        $message = ChatbotMessage::factory()->create(['role' => 'user']);

        $this->assertTrue($message->isUserMessage());
        $this->assertFalse($message->isAssistantMessage());
    }

    public function test_is_assistant_message_returns_true_for_assistant(): void
    {
        $message = ChatbotMessage::factory()->create(['role' => 'assistant']);

        $this->assertTrue($message->isAssistantMessage());
        $this->assertFalse($message->isUserMessage());
    }
}
