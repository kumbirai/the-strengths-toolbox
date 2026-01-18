<?php

namespace App\Services;

use App\Contracts\AIClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class OpenAIClient extends BaseService implements AIClientInterface
{
    protected Client $client;

    protected string $apiKey;

    protected string $model;

    protected int $maxTokens;

    protected float $temperature;

    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model', 'gpt-4');
        $this->maxTokens = config('services.openai.max_tokens', 500);
        $this->temperature = config('services.openai.temperature', 0.7);
        $this->timeout = config('services.openai.timeout', 30);

        if (empty($this->apiKey)) {
            throw new \RuntimeException('OpenAI API key is not configured');
        }

        $this->client = new Client([
            'base_uri' => config('services.openai.base_uri', 'https://api.openai.com/v1/'),
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'timeout' => $this->timeout,
        ]);
    }

    /**
     * Send chat completion request
     *
     * @throws \Exception
     */
    public function chatCompletion(array $messages, array $options = []): array
    {
        try {
            $payload = [
                'model' => $options['model'] ?? $this->model,
                'messages' => $messages,
                'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                'temperature' => $options['temperature'] ?? $this->temperature,
            ];

            Log::debug('OpenAI API request', [
                'model' => $payload['model'],
                'message_count' => count($messages),
                'max_tokens' => $payload['max_tokens'],
            ]);

            $response = $this->client->post('chat/completions', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (! isset($data['choices'][0]['message']['content'])) {
                throw new \RuntimeException('Invalid response from OpenAI API');
            }

            $result = [
                'content' => $data['choices'][0]['message']['content'],
                'tokens_used' => $data['usage']['total_tokens'] ?? null,
                'prompt_tokens' => $data['usage']['prompt_tokens'] ?? null,
                'completion_tokens' => $data['usage']['completion_tokens'] ?? null,
                'model' => $data['model'] ?? $this->model,
                'finish_reason' => $data['choices'][0]['finish_reason'] ?? null,
            ];

            Log::info('OpenAI API response', [
                'tokens_used' => $result['tokens_used'],
                'model' => $result['model'],
            ]);

            return $result;
        } catch (ClientException $e) {
            $this->handleClientError($e);
            throw $e;
        } catch (ServerException $e) {
            $this->handleServerError($e);
            throw $e;
        } catch (RequestException $e) {
            $this->handleRequestError($e);
            throw $e;
        } catch (\Exception $e) {
            Log::error('OpenAI API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Build messages array for API
     */
    public function buildMessagesArray(string $systemPrompt, array $conversationHistory, string $userMessage): array
    {
        $messages = [];

        // Add system message
        if (! empty($systemPrompt)) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }

        // Add conversation history
        foreach ($conversationHistory as $message) {
            $messages[] = [
                'role' => $message['role'] ?? 'user',
                'content' => $message['content'] ?? '',
            ];
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        return $messages;
    }

    /**
     * Handle client errors (4xx)
     */
    protected function handleClientError(ClientException $e): void
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $body = $e->getResponse()->getBody()->getContents();
        $data = json_decode($body, true);

        $errorMessage = $data['error']['message'] ?? 'OpenAI API client error';

        Log::error('OpenAI API client error', [
            'status_code' => $statusCode,
            'error' => $errorMessage,
            'response' => $data,
        ]);

        // Handle specific error codes - throw exceptions that error handler can catch
        if ($statusCode === 401) {
            throw new \App\Exceptions\ChatbotApiException(
                'Invalid OpenAI API key',
                'I\'m having trouble connecting right now. Please try again later.',
                [],
                'CHATBOT_API_AUTH_ERROR'
            );
        } elseif ($statusCode === 429) {
            // Check if it's a quota error
            if (stripos($errorMessage, 'quota') !== false || stripos($errorMessage, 'insufficient_quota') !== false) {
                throw new \App\Exceptions\ChatbotApiException(
                    'OpenAI API quota exceeded: '.$errorMessage,
                    'The AI service quota has been exceeded. Please contact support or check your OpenAI account billing.',
                    [],
                    'CHATBOT_API_QUOTA_EXCEEDED'
                );
            }
            throw new \App\Exceptions\ChatbotApiException(
                'OpenAI API rate limit exceeded',
                'I\'m receiving too many requests. Please wait a moment and try again.',
                [],
                'CHATBOT_API_RATE_LIMIT'
            );
        } elseif ($statusCode === 400) {
            throw new \App\Exceptions\ChatbotApiException(
                'Invalid request to OpenAI API: '.$errorMessage,
                'I encountered an error processing your request. Please try again.',
                [],
                'CHATBOT_API_CLIENT_ERROR'
            );
        } elseif ($statusCode === 404) {
            throw new \App\Exceptions\ChatbotApiException(
                'OpenAI API model not found: '.$errorMessage,
                'The AI model is not available. Please contact support or check your configuration.',
                [],
                'CHATBOT_API_MODEL_NOT_FOUND'
            );
        }
    }

    /**
     * Handle server errors (5xx)
     */
    protected function handleServerError(ServerException $e): void
    {
        $statusCode = $e->getResponse()->getStatusCode();

        Log::error('OpenAI API server error', [
            'status_code' => $statusCode,
            'error' => $e->getMessage(),
        ]);

        throw new \RuntimeException('OpenAI API is temporarily unavailable. Please try again later.');
    }

    /**
     * Handle request errors
     */
    protected function handleRequestError(RequestException $e): void
    {
        Log::error('OpenAI API request error', [
            'error' => $e->getMessage(),
        ]);

        if ($e->hasResponse()) {
            throw new \RuntimeException('Failed to communicate with OpenAI API');
        } else {
            throw new \RuntimeException('Network error: Unable to reach OpenAI API');
        }
    }

    /**
     * Check if API key is configured
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }
}
