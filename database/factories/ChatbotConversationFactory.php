<?php

namespace Database\Factories;

use App\Models\ChatbotConversation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatbotConversationFactory extends Factory
{
    protected $model = ChatbotConversation::class;

    public function definition(): array
    {
        return [
            'session_id' => 'session-'.$this->faker->uuid(),
            'user_id' => null,
            'context' => [],
        ];
    }
}
