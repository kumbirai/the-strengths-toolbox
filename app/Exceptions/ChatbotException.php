<?php

namespace App\Exceptions;

use Exception;

class ChatbotException extends Exception
{
    protected string $userMessage;

    protected string $errorCode;

    protected array $context;

    public function __construct(
        string $message = '',
        string $userMessage = '',
        string $errorCode = 'CHATBOT_ERROR',
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->userMessage = $userMessage ?: 'An error occurred. Please try again later.';
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'error_code' => $this->errorCode,
            'message' => $this->userMessage,
            'context' => $this->context,
        ];
    }
}
