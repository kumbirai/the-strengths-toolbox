<?php

namespace App\Contracts;

interface AIClientInterface
{
    /**
     * Send chat completion request
     *
     * @param  array  $messages  Array of messages with 'role' and 'content'
     * @param  array  $options  Additional options (model, max_tokens, temperature, etc.)
     * @return array Response with 'content', 'tokens_used', and optionally 'model', 'prompt_tokens', 'completion_tokens'
     *
     * @throws \Exception
     */
    public function chatCompletion(array $messages, array $options = []): array;

    /**
     * Build messages array for API
     *
     * @param  string  $systemPrompt  System prompt
     * @param  array  $conversationHistory  Previous messages
     * @param  string  $userMessage  Current user message
     * @return array Formatted messages array
     */
    public function buildMessagesArray(string $systemPrompt, array $conversationHistory, string $userMessage): array;

    /**
     * Check if client is properly configured
     */
    public function isConfigured(): bool;
}
