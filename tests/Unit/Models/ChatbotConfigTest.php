<?php

namespace Tests\Unit\Models;

use App\Models\ChatbotConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_config_can_be_created(): void
    {
        $config = ChatbotConfig::create([
            'key' => 'test_key',
            'value' => 'test_value',
        ]);

        $this->assertDatabaseHas('chatbot_configs', [
            'key' => 'test_key',
        ]);

        // Value is cast as array, so it's stored as JSON
        $this->assertEquals('test_value', $config->value);
    }
}
