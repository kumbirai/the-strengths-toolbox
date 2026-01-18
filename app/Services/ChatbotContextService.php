<?php

namespace App\Services;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use Illuminate\Support\Facades\Cache;

class ChatbotContextService extends BaseService
{
    protected int $maxMessages;

    protected int $maxTokens;

    protected bool $truncateOldMessages;

    public function __construct()
    {
        $this->maxMessages = config('chatbot.context.max_messages', 10);
        $this->maxTokens = config('chatbot.context.max_tokens', 2000);
        $this->truncateOldMessages = config('chatbot.context.truncate_old_messages', true);
    }

    /**
     * Build conversation context for OpenAI
     */
    public function buildContext(ChatbotConversation $conversation, string $newUserMessage = ''): array
    {
        $cacheKey = "chatbot.context.{$conversation->id}";

        return Cache::remember($cacheKey, 300, function () use ($conversation, $newUserMessage) {
            // Get conversation history
            $messages = $this->getConversationHistory($conversation);

            // Build messages array
            $contextMessages = $this->buildMessagesArray($messages, $newUserMessage);

            // Optimize for token limits
            $optimizedMessages = $this->optimizeForTokens($contextMessages);

            return $optimizedMessages;
        });
    }

    /**
     * Get conversation history
     */
    protected function getConversationHistory(ChatbotConversation $conversation): \Illuminate\Database\Eloquent\Collection
    {
        return ChatbotMessage::where('conversation_id', $conversation->id)
            ->where('role', '!=', 'system')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Build messages array from history
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $messages
     */
    protected function buildMessagesArray($messages, string $newUserMessage = ''): array
    {
        $messagesArray = [];

        // Add historical messages
        foreach ($messages as $message) {
            $messagesArray[] = [
                'role' => $message->role,
                'content' => $message->message,
                'created_at' => $message->created_at,
            ];
        }

        // Add new user message if provided
        if (! empty($newUserMessage)) {
            $messagesArray[] = [
                'role' => 'user',
                'content' => $newUserMessage,
                'created_at' => now(),
            ];
        }

        return $messagesArray;
    }

    /**
     * Optimize messages for token limits
     */
    protected function optimizeForTokens(array $messages): array
    {
        if (! $this->truncateOldMessages) {
            return $this->formatMessages($messages);
        }

        // Estimate tokens (rough estimate: 1 token ≈ 4 characters)
        $estimatedTokens = $this->estimateTokens($messages);

        if ($estimatedTokens <= $this->maxTokens) {
            return $this->formatMessages($messages);
        }

        // Need to truncate - keep most recent messages
        $truncated = $this->truncateMessages($messages);

        return $this->formatMessages($truncated);
    }

    /**
     * Estimate tokens for messages
     */
    protected function estimateTokens(array $messages): int
    {
        $totalChars = 0;

        foreach ($messages as $message) {
            $totalChars += strlen($message['content'] ?? '');
        }

        // Rough estimate: 1 token ≈ 4 characters
        return (int) ceil($totalChars / 4);
    }

    /**
     * Truncate messages to fit token limit
     */
    protected function truncateMessages(array $messages): array
    {
        // Keep most recent messages
        $reversed = array_reverse($messages);
        $kept = [];
        $currentTokens = 0;

        foreach ($reversed as $message) {
            $messageTokens = (int) ceil(strlen($message['content'] ?? '') / 4);

            if ($currentTokens + $messageTokens <= $this->maxTokens) {
                array_unshift($kept, $message);
                $currentTokens += $messageTokens;
            } else {
                break;
            }
        }

        // If we have space, try to keep at least one user-assistant pair
        if (empty($kept) && ! empty($messages)) {
            $kept = [end($messages)];
        }

        return $kept;
    }

    /**
     * Format messages for OpenAI API
     */
    protected function formatMessages(array $messages): array
    {
        return array_map(function ($message) {
            return [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }, $messages);
    }

    /**
     * Get system prompt for conversation
     */
    public function getSystemPrompt(ChatbotConversation $conversation): string
    {
        if (! config('chatbot.system_prompt.enabled', true)) {
            return '';
        }

        $customPrompt = config('chatbot.system_prompt.custom');
        if (! empty($customPrompt)) {
            return $this->replacePlaceholders($customPrompt, $conversation);
        }

        $template = config('chatbot.system_prompt.template', 'default');

        return $this->getSystemPromptTemplate($template, $conversation);
    }

    /**
     * Get system prompt template
     */
    protected function getSystemPromptTemplate(string $template, ChatbotConversation $conversation): string
    {
        // Try to get prompt from database
        $prompt = \App\Models\ChatbotPrompt::getDefault();

        if ($prompt) {
            $context = $conversation->context ?? [];
            $variables = [
                'company_name' => $context['company'] ?? 'The Strengths Toolbox',
                'phone' => $context['contact']['phone'] ?? '+27 83 294 8033',
                'email' => $context['contact']['email'] ?? 'welcome@eberhardniklaus.co.za',
                'services' => implode(', ', $context['services'] ?? [
                    'Strengths-based development',
                    'Team building',
                    'Sales training',
                    'Facilitation workshops',
                ]),
            ];

            return $prompt->render($variables);
        }

        // Fallback to default prompt
        $context = $conversation->context ?? [];

        $phone = $context['contact']['phone'] ?? '+27 83 294 8033';
        $email = $context['contact']['email'] ?? 'welcome@eberhardniklaus.co.za';

        $prompt = "You are a helpful assistant for The Strengths Toolbox, a company that provides strengths-based development, team building, and sales training services.

Your role is to:
- Answer questions about services, programs, and offerings
- Help visitors understand the benefits of strengths-based development
- Guide visitors to relevant pages or resources
- Provide information about booking consultations
- Be friendly, professional, and concise

Key information about The Strengths Toolbox:
- Services: Strengths-based development, team building, sales training, facilitation workshops
- Founder: Eberhard Niklaus
- Contact: {$phone}, {$email}
- Main offering: Build Strong Teams. Unlock Strong Profits.

If you don't know the answer to something, politely direct the user to contact the company directly or visit the contact page.";

        return $this->replacePlaceholders($prompt, $conversation);
    }

    /**
     * Replace placeholders in prompt
     */
    protected function replacePlaceholders(string $prompt, ChatbotConversation $conversation): string
    {
        $context = $conversation->context ?? [];
        $defaults = $this->getDefaultContext();

        $replacements = [
            '{company_name}' => $context['company'] ?? $defaults['company'] ?? 'The Strengths Toolbox',
            '{phone}' => $context['contact']['phone'] ?? $defaults['contact']['phone'] ?? '+27 83 294 8033',
            '{email}' => $context['contact']['email'] ?? $defaults['contact']['email'] ?? 'welcome@eberhardniklaus.co.za',
            '{services}' => implode(', ', $context['services'] ?? $defaults['services'] ?? []),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $prompt);
    }

    /**
     * Get default context
     */
    protected function getDefaultContext(): array
    {
        return [
            'company' => 'The Strengths Toolbox',
            'services' => [
                'Strengths-based development',
                'Team building',
                'Sales training',
                'Facilitation workshops',
            ],
            'contact' => [
                'phone' => '+27 83 294 8033',
                'email' => 'welcome@eberhardniklaus.co.za',
            ],
        ];
    }

    /**
     * Clear context cache for conversation
     */
    public function clearContextCache(int $conversationId): void
    {
        Cache::forget("chatbot.context.{$conversationId}");
    }

    /**
     * Validate context messages
     */
    public function validateContext(array $messages): bool
    {
        if (empty($messages)) {
            return false;
        }

        foreach ($messages as $message) {
            if (! isset($message['role']) || ! isset($message['content'])) {
                return false;
            }

            $validRoles = ['user', 'assistant', 'system'];
            if (! in_array($message['role'], $validRoles)) {
                return false;
            }

            if (empty(trim($message['content']))) {
                return false;
            }
        }

        return true;
    }
}
