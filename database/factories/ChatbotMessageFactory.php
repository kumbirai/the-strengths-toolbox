<?php

namespace Database\Factories;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatbotMessageFactory extends Factory
{
    protected $model = ChatbotMessage::class;

    public function definition(): array
    {
        return [
            'conversation_id' => ChatbotConversation::factory(),
            'role' => $this->faker->randomElement(['user', 'assistant']),
            'message' => $this->faker->sentence(),
            'tokens_used' => $this->faker->numberBetween(10, 100),
        ];
    }
}
