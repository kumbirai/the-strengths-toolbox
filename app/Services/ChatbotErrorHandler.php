<?php

namespace App\Services;

use App\Exceptions\ChatbotException;
use App\Exceptions\ChatbotRateLimitException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class ChatbotErrorHandler extends BaseService
{
    /**
     * Handle exception and return user-friendly response
     */
    public function handleException(\Exception $exception, array $context = []): array
    {
        // Log the error
        $this->logError($exception, $context);

        // Handle specific exception types
        if ($exception instanceof ChatbotException) {
            return $this->handleChatbotException($exception);
        }

        if ($exception instanceof ClientException) {
            return $this->handleClientException($exception, $context);
        }

        if ($exception instanceof ServerException) {
            return $this->handleServerException($exception, $context);
        }

        if ($exception instanceof RequestException) {
            return $this->handleRequestException($exception, $context);
        }

        // Handle generic exceptions
        return $this->handleGenericException($exception, $context);
    }

    /**
     * Handle ChatbotException
     */
    protected function handleChatbotException(ChatbotException $exception): array
    {
        $response = [
            'success' => false,
            'message' => $exception->getUserMessage(),
            'error_code' => $exception->getErrorCode(),
        ];

        if ($exception instanceof ChatbotRateLimitException) {
            $response['rate_limit'] = [
                'reset_at' => \Carbon\Carbon::createFromTimestamp($exception->getResetAt())->toIso8601String(),
            ];
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'message' => $exception->getMessage(),
                'context' => $exception->getContext(),
            ];
        }

        return $response;
    }

    /**
     * Handle HTTP client exceptions (4xx)
     */
    protected function handleClientException(ClientException $exception, array $context): array
    {
        $statusCode = $exception->getResponse()->getStatusCode();
        $body = $exception->getResponse()->getBody()->getContents();
        $data = json_decode($body, true);

        $errorMessage = $data['error']['message'] ?? 'API request failed';
        $errorType = $data['error']['type'] ?? 'unknown';

        // Log detailed error for debugging
        Log::error('OpenAI API client error', [
            'status_code' => $statusCode,
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'response_body' => $body,
            'context' => $context,
        ]);

        if ($statusCode === 401) {
            return [
                'success' => false,
                'message' => 'I\'m having trouble connecting right now. Please try again later.',
                'error_code' => 'CHATBOT_API_AUTH_ERROR',
            ];
        }

        if ($statusCode === 429) {
            return [
                'success' => false,
                'message' => 'I\'m receiving too many requests. Please wait a moment and try again.',
                'error_code' => 'CHATBOT_API_RATE_LIMIT',
            ];
        }

        // For 400/404 errors, provide more specific message if available
        if ($statusCode === 400 || $statusCode === 404) {
            $userMessage = 'I encountered an error processing your request. Please try again.';

            // Check if it's a model access error
            if (stripos($errorMessage, 'model') !== false && stripos($errorMessage, 'does not exist') !== false) {
                $userMessage = 'The AI model is not available. Please contact support or check your configuration.';
                if (config('app.debug')) {
                    $userMessage .= ' ('.$errorMessage.')';
                }
            } elseif (config('app.debug')) {
                $userMessage .= ' ('.$errorMessage.')';
            }

            return [
                'success' => false,
                'message' => $userMessage,
                'error_code' => 'CHATBOT_API_CLIENT_ERROR',
            ];
        }

        return [
            'success' => false,
            'message' => 'I encountered an error processing your request. Please try again.',
            'error_code' => 'CHATBOT_API_CLIENT_ERROR',
        ];
    }

    /**
     * Handle HTTP server exceptions (5xx)
     */
    protected function handleServerException(ServerException $exception, array $context): array
    {
        return [
            'success' => false,
            'message' => 'The AI service is temporarily unavailable. Please try again in a moment.',
            'error_code' => 'CHATBOT_API_SERVER_ERROR',
        ];
    }

    /**
     * Handle request exceptions
     */
    protected function handleRequestException(RequestException $exception, array $context): array
    {
        if ($exception->hasResponse()) {
            return [
                'success' => false,
                'message' => 'I\'m having trouble connecting. Please check your internet connection and try again.',
                'error_code' => 'CHATBOT_API_CONNECTION_ERROR',
            ];
        }

        return [
            'success' => false,
            'message' => 'I\'m having trouble connecting right now. Please try again later.',
            'error_code' => 'CHATBOT_API_NETWORK_ERROR',
        ];
    }

    /**
     * Handle generic exceptions
     */
    protected function handleGenericException(\Exception $exception, array $context): array
    {
        return [
            'success' => false,
            'message' => 'An unexpected error occurred. Please try again later.',
            'error_code' => 'CHATBOT_UNKNOWN_ERROR',
        ];
    }

    /**
     * Log error with context
     */
    protected function logError(\Exception $exception, array $context): void
    {
        $logData = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'context' => $context,
        ];

        // Add additional context for specific exceptions
        if ($exception instanceof ClientException) {
            $logData['status_code'] = $exception->getResponse()->getStatusCode();
            $logData['response_body'] = $exception->getResponse()->getBody()->getContents();
        }

        Log::error('Chatbot error', $logData);
    }

    /**
     * Get user-friendly error message
     */
    public function getUserMessage(string $errorCode): string
    {
        $messages = [
            'CHATBOT_API_ERROR' => 'I\'m having trouble connecting right now. Please try again in a moment.',
            'CHATBOT_API_AUTH_ERROR' => 'I\'m having trouble connecting right now. Please try again later.',
            'CHATBOT_API_RATE_LIMIT' => 'I\'m receiving too many requests. Please wait a moment and try again.',
            'CHATBOT_API_SERVER_ERROR' => 'The AI service is temporarily unavailable. Please try again in a moment.',
            'CHATBOT_API_CONNECTION_ERROR' => 'I\'m having trouble connecting. Please check your internet connection and try again.',
            'CHATBOT_API_NETWORK_ERROR' => 'I\'m having trouble connecting right now. Please try again later.',
            'CHATBOT_RATE_LIMIT' => 'You\'ve sent too many messages. Please wait a moment before trying again.',
            'CHATBOT_VALIDATION_ERROR' => 'Invalid message. Please check your input and try again.',
            'CHATBOT_UNKNOWN_ERROR' => 'An unexpected error occurred. Please try again later.',
        ];

        return $messages[$errorCode] ?? 'An error occurred. Please try again later.';
    }
}
