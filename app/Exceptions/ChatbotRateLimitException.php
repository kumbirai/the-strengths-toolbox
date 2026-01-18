<?php

namespace App\Exceptions;

use Exception;

class ChatbotRateLimitException extends ChatbotException
{
    protected int $resetAt;

    public function __construct(
        string $message = '',
        int $resetAt = 0,
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        $userMessage = 'You\'ve sent too many messages. Please wait a moment before trying again.';

        parent::__construct($message, $userMessage, 'CHATBOT_RATE_LIMIT', $context, $code, $previous);

        $this->resetAt = $resetAt;
    }

    public function getResetAt(): int
    {
        return $this->resetAt;
    }
}
