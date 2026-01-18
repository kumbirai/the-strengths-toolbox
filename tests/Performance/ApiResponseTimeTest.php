<?php

namespace Tests\Performance;

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApiResponseTimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Http::fake();
    }

    public function test_chatbot_api_response_time_acceptable(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => ['role' => 'assistant', 'content' => 'Test'],
                ]],
                'usage' => ['total_tokens' => 100],
            ], 200),
        ]);

        $startTime = microtime(true);
        $response = $this->postJson('/api/chatbot/message', [
            'message' => 'Test',
            'session_id' => 'test-session',
        ]);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(2000, $responseTime, "API responded in {$responseTime}ms, expected < 2000ms");
    }

    public function test_form_submission_api_response_time_acceptable(): void
    {
        $form = Form::factory()->create(['slug' => 'test-form']);

        $startTime = microtime(true);
        $response = $this->postJson(route('forms.submit', $form->slug), [
            'name' => 'Test',
            'email' => 'test@example.com',
        ]);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(1000, $responseTime, "API responded in {$responseTime}ms, expected < 1000ms");
    }
}
