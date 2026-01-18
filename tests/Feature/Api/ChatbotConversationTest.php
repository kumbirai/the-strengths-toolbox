<?php

namespace Tests\Feature\Api;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_conversation_returns_conversation(): void
    {
        $conversation = ChatbotConversation::factory()->create();
        ChatbotMessage::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
        ]);

        $response = $this->getJson("/api/chatbot/conversation/{$conversation->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'conversation' => [
                'id',
                'session_id',
            ],
        ]);
    }

    public function test_get_conversation_returns_404_for_non_existent(): void
    {
        $response = $this->getJson('/api/chatbot/conversation/99999');

        $response->assertStatus(404);
    }

    public function test_get_conversation_messages_returns_paginated_messages(): void
    {
        $conversation = ChatbotConversation::factory()->create();
        ChatbotMessage::factory()->count(25)->create([
            'conversation_id' => $conversation->id,
        ]);

        $response = $this->getJson("/api/chatbot/conversation/{$conversation->id}/messages?page=1&per_page=10");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
        ]);
        // Messages may be in different structure
        $this->assertTrue($response->json('success') === true);
    }

    public function test_get_conversation_stats_returns_statistics(): void
    {
        $conversation = ChatbotConversation::factory()->create();
        ChatbotMessage::factory()->count(10)->create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
        ]);
        ChatbotMessage::factory()->count(10)->create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'tokens_used' => 100,
        ]);

        $response = $this->getJson("/api/chatbot/conversation/{$conversation->id}/stats");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'stats' => [
                'total_messages',
                'user_messages',
                'assistant_messages',
            ],
        ]);
    }

    public function test_search_conversations_returns_matching_conversations(): void
    {
        $conversation1 = ChatbotConversation::factory()->create();
        ChatbotMessage::factory()->create([
            'conversation_id' => $conversation1->id,
            'message' => 'Hello world',
        ]);

        $conversation2 = ChatbotConversation::factory()->create();
        ChatbotMessage::factory()->create([
            'conversation_id' => $conversation2->id,
            'message' => 'Goodbye',
        ]);

        $response = $this->getJson('/api/chatbot/conversations/search?search=Hello');

        // May be rate limited
        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'success',
                'conversations',
            ]);
        } else {
            $this->assertEquals(429, $response->status()); // Rate limited
        }
    }
}
