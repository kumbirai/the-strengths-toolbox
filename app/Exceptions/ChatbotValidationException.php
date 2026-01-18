<?php

namespace App\Exceptions;

use Exception;

class ChatbotValidationException extends ChatbotException
{
    public function __construct(
        string $message = '',
        string $userMessage = 'Invalid message. Please check your input and try again.',
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $userMessage, 'CHATBOT_VALIDATION_ERROR', $context, $code, $previous);
    }
}
