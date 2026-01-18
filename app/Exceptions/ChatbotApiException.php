<?php

namespace App\Exceptions;

use Exception;

class ChatbotApiException extends ChatbotException
{
    protected string $apiErrorCode;

    public function __construct(
        string $message = '',
        string $userMessage = 'I\'m having trouble connecting right now. Please try again in a moment.',
        array $context = [],
        string $apiErrorCode = 'CHATBOT_API_ERROR',
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $userMessage, $apiErrorCode, $context, $code, $previous);
        $this->apiErrorCode = $apiErrorCode;
    }

    public function getApiErrorCode(): string
    {
        return $this->apiErrorCode;
    }
}
