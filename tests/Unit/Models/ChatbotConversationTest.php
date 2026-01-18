<?php

namespace Tests\Unit\Models;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_has_messages_relationship(): void
    {
        $conversation = ChatbotConversation::factory()->create();
        $message = ChatbotMessage::factory()->create(['conversation_id' => $conversation->id]);

        $this->assertTrue($conversation->messages->contains($message));
        $this->assertEquals(1, $conversation->messages->count());
    }

    public function test_conversation_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $conversation = ChatbotConversation::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $conversation->user);
        $this->assertEquals($user->id, $conversation->user->id);
    }

    public function test_scope_by_user_returns_user_conversations(): void
    {
        $user = User::factory()->create();
        $conversation = ChatbotConversation::factory()->create(['user_id' => $user->id]);
        ChatbotConversation::factory()->create(['user_id' => null]);

        $results = ChatbotConversation::byUser($user->id)->get();

        $this->assertTrue($results->contains($conversation));
        $this->assertCount(1, $results);
    }

    public function test_scope_by_session_returns_session_conversations(): void
    {
        $sessionId = 'test-session-123';
        $conversation = ChatbotConversation::factory()->create(['session_id' => $sessionId]);
        ChatbotConversation::factory()->create(['session_id' => 'other-session']);

        $results = ChatbotConversation::bySession($sessionId)->get();

        $this->assertTrue($results->contains($conversation));
        $this->assertCount(1, $results);
    }

    public function test_get_total_messages_attribute_returns_count(): void
    {
        $conversation = ChatbotConversation::factory()->create();
        ChatbotMessage::factory()->count(5)->create(['conversation_id' => $conversation->id]);

        $this->assertEquals(5, $conversation->total_messages);
    }

    public function test_get_total_tokens_attribute_returns_sum(): void
    {
        $conversation = ChatbotConversation::factory()->create();
        ChatbotMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'tokens_used' => 100,
        ]);
        ChatbotMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'tokens_used' => 200,
        ]);

        $this->assertEquals(300, $conversation->total_tokens);
    }

    public function test_is_active_returns_true_for_active(): void
    {
        $conversation = ChatbotConversation::factory()->create();
        ChatbotMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'created_at' => now()->subDay(),
        ]);

        $this->assertTrue($conversation->isActive());
    }

    public function test_is_active_returns_false_for_inactive(): void
    {
        $conversation = ChatbotConversation::factory()->create();
        ChatbotMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'created_at' => now()->subDays(31),
        ]);

        $this->assertFalse($conversation->isActive());
    }
}
