<?php

namespace App\Services;

use App\Contracts\AIClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class CustomAIClient extends BaseService implements AIClientInterface
{
    protected Client $client;

    protected string $apiUrl;

    protected ?string $apiKey;

    protected string $model;

    protected int $maxTokens;

    protected float $temperature;

    protected int $timeout;

    protected array $headers;

    protected string $requestFormat; // 'openai', 'custom', 'anthropic', etc.

    public function __construct()
    {
        $this->apiUrl = config('services.custom_ai.api_url', '');
        $this->apiKey = config('services.custom_ai.api_key');
        $this->model = config('services.custom_ai.model', '');
        $this->maxTokens = config('services.custom_ai.max_tokens', 500);
        $this->temperature = config('services.custom_ai.temperature', 0.7);
        $this->timeout = config('services.custom_ai.timeout', 30);
        $this->requestFormat = config('services.custom_ai.request_format', 'openai');
        $this->headers = config('services.custom_ai.headers', []);

        if (empty($this->apiUrl)) {
            throw new \RuntimeException('Custom AI API URL is not configured');
        }

        // Build headers
        $defaultHeaders = [
            'Content-Type' => 'application/json',
        ];

        if ($this->apiKey) {
            $authHeader = config('services.custom_ai.auth_header', 'Authorization');
            $authPrefix = config('services.custom_ai.auth_prefix', 'Bearer ');
            $defaultHeaders[$authHeader] = $authPrefix.$this->apiKey;
        }

        $this->headers = array_merge($defaultHeaders, $this->headers);

        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'headers' => $this->headers,
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
            $payload = $this->buildPayload($messages, $options);

            Log::debug('Custom AI API request', [
                'url' => $this->apiUrl,
                'model' => $payload['model'] ?? 'N/A',
                'message_count' => count($messages),
                'format' => $this->requestFormat,
            ]);

            $endpoint = config('services.custom_ai.endpoint', '/chat/completions');
            $response = $this->client->post($endpoint, [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $this->parseResponse($data, $options);

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
            Log::error('Custom AI API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Build request payload based on format
     */
    protected function buildPayload(array $messages, array $options): array
    {
        $format = $options['format'] ?? $this->requestFormat;

        switch ($format) {
            case 'openai':
                return [
                    'model' => $options['model'] ?? $this->model,
                    'messages' => $messages,
                    'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                    'temperature' => $options['temperature'] ?? $this->temperature,
                ];

            case 'anthropic':
                // Anthropic Claude format
                return [
                    'model' => $options['model'] ?? $this->model,
                    'messages' => $messages,
                    'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                    'temperature' => $options['temperature'] ?? $this->temperature,
                ];

            case 'custom':
                // Custom format - use custom payload builder if provided
                $customBuilder = config('services.custom_ai.payload_builder');
                if ($customBuilder && is_callable($customBuilder)) {
                    return call_user_func($customBuilder, $messages, $options, $this);
                }

                // Fallback to OpenAI format
                return [
                    'model' => $options['model'] ?? $this->model,
                    'messages' => $messages,
                    'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                    'temperature' => $options['temperature'] ?? $this->temperature,
                ];

            default:
                throw new \InvalidArgumentException("Unknown request format: {$format}");
        }
    }

    /**
     * Parse response based on format
     */
    protected function parseResponse(array $data, array $options): array
    {
        $format = $options['format'] ?? $this->requestFormat;

        switch ($format) {
            case 'openai':
                if (! isset($data['choices'][0]['message']['content'])) {
                    throw new \RuntimeException('Invalid response from Custom AI API');
                }

                return [
                    'content' => $data['choices'][0]['message']['content'],
                    'tokens_used' => $data['usage']['total_tokens'] ?? null,
                    'prompt_tokens' => $data['usage']['prompt_tokens'] ?? null,
                    'completion_tokens' => $data['usage']['completion_tokens'] ?? null,
                    'model' => $data['model'] ?? $this->model,
                    'finish_reason' => $data['choices'][0]['finish_reason'] ?? null,
                ];

            case 'anthropic':
                if (! isset($data['content'][0]['text'])) {
                    throw new \RuntimeException('Invalid response from Custom AI API');
                }

                return [
                    'content' => $data['content'][0]['text'],
                    'tokens_used' => $data['usage']['input_tokens'] + $data['usage']['output_tokens'] ?? null,
                    'prompt_tokens' => $data['usage']['input_tokens'] ?? null,
                    'completion_tokens' => $data['usage']['output_tokens'] ?? null,
                    'model' => $data['model'] ?? $this->model,
                ];

            case 'custom':
                // Custom format - use custom parser if provided
                $customParser = config('services.custom_ai.response_parser');
                if ($customParser && is_callable($customParser)) {
                    return call_user_func($customParser, $data, $options, $this);
                }
                // Fallback to OpenAI format
                if (! isset($data['choices'][0]['message']['content'])) {
                    throw new \RuntimeException('Invalid response from Custom AI API');
                }

                return [
                    'content' => $data['choices'][0]['message']['content'],
                    'tokens_used' => $data['usage']['total_tokens'] ?? null,
                    'model' => $data['model'] ?? $this->model,
                ];

            default:
                throw new \InvalidArgumentException("Unknown response format: {$format}");
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
     * Check if client is configured
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiUrl) && ! empty($this->model);
    }

    /**
     * Handle client errors (4xx)
     */
    protected function handleClientError(ClientException $e): void
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $body = $e->getResponse()->getBody()->getContents();
        $data = json_decode($body, true);

        $errorMessage = $data['error']['message'] ?? $data['message'] ?? 'Custom AI API client error';

        Log::error('Custom AI API client error', [
            'status_code' => $statusCode,
            'error' => $errorMessage,
            'response' => $data,
        ]);

        if ($statusCode === 401) {
            throw new \App\Exceptions\ChatbotApiException(
                'Invalid Custom AI API key',
                'I\'m having trouble connecting right now. Please try again later.',
                [],
                'CHATBOT_API_AUTH_ERROR'
            );
        } elseif ($statusCode === 429) {
            throw new \App\Exceptions\ChatbotApiException(
                'Custom AI API rate limit exceeded',
                'I\'m receiving too many requests. Please wait a moment and try again.',
                [],
                'CHATBOT_API_RATE_LIMIT'
            );
        } elseif ($statusCode === 400 || $statusCode === 404) {
            throw new \App\Exceptions\ChatbotApiException(
                'Invalid request to Custom AI API: '.$errorMessage,
                'I encountered an error processing your request. Please try again.',
                [],
                'CHATBOT_API_CLIENT_ERROR'
            );
        }
    }

    /**
     * Handle server errors (5xx)
     */
    protected function handleServerError(ServerException $e): void
    {
        $statusCode = $e->getResponse()->getStatusCode();

        Log::error('Custom AI API server error', [
            'status_code' => $statusCode,
            'error' => $e->getMessage(),
        ]);

        throw new \App\Exceptions\ChatbotApiException(
            'Custom AI API is temporarily unavailable',
            'The AI service is temporarily unavailable. Please try again in a moment.',
            [],
            'CHATBOT_API_SERVER_ERROR'
        );
    }

    /**
     * Handle request errors
     */
    protected function handleRequestError(RequestException $e): void
    {
        Log::error('Custom AI API request error', [
            'error' => $e->getMessage(),
        ]);

        if ($e->hasResponse()) {
            throw new \App\Exceptions\ChatbotApiException(
                'Failed to communicate with Custom AI API',
                'I\'m having trouble connecting. Please check your internet connection and try again.',
                [],
                'CHATBOT_API_CONNECTION_ERROR'
            );
        } else {
            throw new \App\Exceptions\ChatbotApiException(
                'Network error: Unable to reach Custom AI API',
                'I\'m having trouble connecting right now. Please try again later.',
                [],
                'CHATBOT_API_NETWORK_ERROR'
            );
        }
    }
}
