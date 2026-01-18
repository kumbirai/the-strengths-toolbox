<?php

namespace Tests\Unit\Services;

use App\Exceptions\ChatbotException;
use App\Exceptions\ChatbotRateLimitException;
use App\Services\ChatbotErrorHandler;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Tests\TestCase;

class ChatbotErrorHandlerTest extends TestCase
{
    protected ChatbotErrorHandler $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ChatbotErrorHandler;
    }

    public function test_handle_exception_handles_chatbot_exception(): void
    {
        $exception = new ChatbotException(
            'Test error',
            'User-friendly message',
            'TEST_ERROR'
        );

        $result = $this->service->handleException($exception);

        $this->assertFalse($result['success']);
        $this->assertEquals('User-friendly message', $result['message']);
        $this->assertEquals('TEST_ERROR', $result['error_code']);
    }

    public function test_handle_exception_handles_rate_limit_exception(): void
    {
        $exception = Mockery::mock(ChatbotRateLimitException::class);
        $exception->shouldReceive('getUserMessage')->andReturn('Rate limit exceeded');
        $exception->shouldReceive('getErrorCode')->andReturn('CHATBOT_RATE_LIMIT');
        $exception->shouldReceive('getResetAt')->andReturn(now()->addMinute()->timestamp);
        $exception->shouldReceive('getContext')->andReturn([]);

        $result = $this->service->handleException($exception);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('rate_limit', $result);
    }

    public function test_handle_exception_handles_client_exception(): void
    {
        $response = new Response(401, [], json_encode(['error' => ['message' => 'Unauthorized']]));
        $exception = Mockery::mock(ClientException::class);
        $exception->shouldReceive('getResponse')->andReturn($response);

        $result = $this->service->handleException($exception);

        $this->assertFalse($result['success']);
        $this->assertEquals('CHATBOT_API_AUTH_ERROR', $result['error_code']);
    }

    public function test_handle_exception_handles_server_exception(): void
    {
        $response = new Response(500);
        $exception = Mockery::mock(ServerException::class);
        $exception->shouldReceive('getResponse')->andReturn($response);

        $result = $this->service->handleException($exception);

        $this->assertFalse($result['success']);
        $this->assertEquals('CHATBOT_API_SERVER_ERROR', $result['error_code']);
    }

    public function test_get_user_friendly_message_returns_appropriate_message(): void
    {
        $message = $this->service->getUserMessage('CHATBOT_API_ERROR');

        $this->assertIsString($message);
        $this->assertNotEmpty($message);
    }

    public function test_get_user_friendly_message_returns_default_for_unknown_code(): void
    {
        $message = $this->service->getUserMessage('UNKNOWN_ERROR_CODE');

        $this->assertIsString($message);
        $this->assertNotEmpty($message);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
