<?php

namespace App\Services;

use App\Contracts\AIClientInterface;
use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChatbotService extends BaseService
{
    protected ?AIClientInterface $aiClient;

    protected ?ChatbotContextService $contextService;

    protected ?ChatbotRateLimitService $rateLimitService;

    protected ?ChatbotErrorHandler $errorHandler;

    protected int $maxContextMessages;

    protected int $conversationCacheTtl;

    public function __construct(
        ?AIClientInterface $aiClient = null,
        ?ChatbotContextService $contextService = null,
        ?ChatbotRateLimitService $rateLimitService = null,
        ?ChatbotErrorHandler $errorHandler = null
    ) {
        // Resolve dependencies from container if not provided
        $this->aiClient = $aiClient ?? $this->resolveAIClient();
        $this->contextService = $contextService ?? $this->resolveContextService();
        $this->rateLimitService = $rateLimitService ?? $this->resolveRateLimitService();
        $this->errorHandler = $errorHandler ?? $this->resolveErrorHandler();
        $this->maxContextMessages = config('chatbot.max_context_messages', 10);
        $this->conversationCacheTtl = config('chatbot.cache_ttl', 3600);
    }

    /**
     * Resolve AI client from container based on configuration
     */
    protected function resolveAIClient(): ?AIClientInterface
    {
        $provider = config('chatbot.ai_provider', 'openai');

        try {
            switch ($provider) {
                case 'custom':
                    // Check if custom AI is configured
                    if (empty(config('services.custom_ai.api_url'))) {
                        Log::warning('Custom AI API URL not configured, chatbot will use placeholder responses');

                        return null;
                    }

                    return app(CustomAIClient::class);

                case 'openai':
                default:
                    // Check if OpenAI API key is configured
                    if (empty(config('services.openai.api_key'))) {
                        Log::warning('OpenAI API key not configured, chatbot will use placeholder responses');

                        return null;
                    }

                    return app(OpenAIClient::class);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to resolve AI client', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Resolve context service from container
     */
    protected function resolveContextService(): ?ChatbotContextService
    {
        try {
            return app(ChatbotContextService::class);
        } catch (\Exception $e) {
            Log::warning('Failed to resolve ChatbotContextService', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Resolve rate limit service from container
     */
    protected function resolveRateLimitService(): ?ChatbotRateLimitService
    {
        try {
            return app(ChatbotRateLimitService::class);
        } catch (\Exception $e) {
            Log::warning('Failed to resolve ChatbotRateLimitService', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Resolve error handler from container
     */
    protected function resolveErrorHandler(): ?ChatbotErrorHandler
    {
        try {
            return app(ChatbotErrorHandler::class);
        } catch (\Exception $e) {
            Log::warning('Failed to resolve ChatbotErrorHandler', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a new conversation
     */
    public function createConversation(string $sessionId, ?int $userId = null): ChatbotConversation
    {
        try {
            $this->validateSessionId($sessionId);

            $conversation = ChatbotConversation::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'context' => $this->getDefaultContext(),
            ]);

            Log::info('Chatbot conversation created', [
                'conversation_id' => $conversation->id,
                'session_id' => $sessionId,
                'user_id' => $userId,
            ]);

            return $conversation;
        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to create chatbot conversation');
            throw $e;
        }
    }

    /**
     * Get or create conversation
     */
    public function getOrCreateConversation(?int $conversationId, string $sessionId, ?int $userId = null): ChatbotConversation
    {
        try {
            // If conversation ID provided, try to find it
            if ($conversationId) {
                $conversation = ChatbotConversation::find($conversationId);
                if ($conversation) {
                    // Update user_id if provided and different
                    if ($userId && $conversation->user_id !== $userId) {
                        $conversation->update(['user_id' => $userId]);
                    }

                    return $conversation;
                }
            }

            // Try to find by session_id
            $conversation = ChatbotConversation::where('session_id', $sessionId)->first();

            if ($conversation) {
                // Update user_id if provided and different
                if ($userId && $conversation->user_id !== $userId) {
                    $conversation->update(['user_id' => $userId]);
                }

                return $conversation;
            }

            // Create new conversation
            return $this->createConversation($sessionId, $userId);
        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to get or create chatbot conversation');
            throw $e;
        }
    }

    /**
     * Send a message and get response
     */
    public function sendMessage(
        int $conversationId,
        string $message,
        ?string $sessionId = null,
        ?int $userId = null,
        ?string $ipAddress = null
    ): array {
        $context = [
            'conversation_id' => $conversationId,
            'session_id' => $sessionId,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
        ];

        try {
            $conversation = ChatbotConversation::findOrFail($conversationId);

            // Validate message
            $this->validateMessage($message);

            // Check rate limits if service is available
            if ($this->rateLimitService) {
                if ($sessionId) {
                    $rateLimit = $this->rateLimitService->checkRateLimit($sessionId, 'session');
                    if (! $rateLimit['allowed']) {
                        return $this->rateLimitExceededResponse($rateLimit);
                    }
                }

                if ($conversationId) {
                    $rateLimit = $this->rateLimitService->checkRateLimit($conversationId, 'conversation');
                    if (! $rateLimit['allowed']) {
                        return $this->rateLimitExceededResponse($rateLimit);
                    }
                }

                if ($userId) {
                    $rateLimit = $this->rateLimitService->checkRateLimit($userId, 'user');
                    if (! $rateLimit['allowed']) {
                        return $this->rateLimitExceededResponse($rateLimit);
                    }
                }

                if ($ipAddress) {
                    $rateLimit = $this->rateLimitService->checkRateLimit($ipAddress, 'ip');
                    if (! $rateLimit['allowed']) {
                        return $this->rateLimitExceededResponse($rateLimit);
                    }
                }
            }

            // Save user message
            $userMessage = $this->saveMessage(
                $conversationId,
                'user',
                $message
            );

            // Use AI client if available, otherwise return placeholder
            if ($this->aiClient && $this->aiClient->isConfigured() && $this->contextService) {
                // Build context using context service
                $conversationHistory = $this->contextService->buildContext($conversation, $message);

                // Get system prompt
                $systemPrompt = $this->contextService->getSystemPrompt($conversation);

                // Build messages array for AI client
                $messages = $this->aiClient->buildMessagesArray(
                    $systemPrompt,
                    $conversationHistory,
                    $message
                );

                // Validate context
                if (! $this->contextService->validateContext($messages)) {
                    throw new \RuntimeException('Invalid context generated');
                }

                // Call AI API
                try {
                    $aiResponse = $this->aiClient->chatCompletion($messages);
                } catch (\Exception $e) {
                    if ($this->errorHandler) {
                        return $this->errorHandler->handleException($e, $context);
                    }
                    throw $e;
                }

                // Save AI response
                $aiMessage = $this->saveMessage(
                    $conversationId,
                    'assistant',
                    $aiResponse['content'],
                    $aiResponse['tokens_used']
                );

                // Clear context cache
                $this->contextService->clearContextCache($conversationId);

                Log::info('Chatbot message processed', [
                    'conversation_id' => $conversationId,
                    'user_message_id' => $userMessage->id,
                    'ai_message_id' => $aiMessage->id,
                    'tokens_used' => $aiResponse['tokens_used'],
                ]);

                return [
                    'success' => true,
                    'message' => $aiResponse['content'],
                    'conversation_id' => $conversationId,
                    'user_message_id' => $userMessage->id,
                    'ai_message_id' => $aiMessage->id,
                    'tokens_used' => $aiResponse['tokens_used'],
                ];
            } else {
                // Fallback placeholder response
                $response = 'Thank you for your message. The chatbot service is being configured. Please contact us directly for assistance.';

                // Save AI response
                $aiMessage = $this->saveMessage(
                    $conversationId,
                    'assistant',
                    $response
                );

                // Update conversation context
                $this->updateConversationContext($conversation);

                Log::info('Chatbot message processed (placeholder)', [
                    'conversation_id' => $conversationId,
                    'user_message_id' => $userMessage->id,
                    'ai_message_id' => $aiMessage->id,
                ]);

                return [
                    'success' => true,
                    'message' => $response,
                    'conversation_id' => $conversationId,
                    'user_message_id' => $userMessage->id,
                    'ai_message_id' => $aiMessage->id,
                ];
            }
        } catch (\Exception $e) {
            if ($this->errorHandler) {
                return $this->errorHandler->handleException($e, $context);
            }

            Log::error('Chatbot error', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Sorry, I encountered an error. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ];
        }
    }

    /**
     * Rate limit exceeded response
     */
    protected function rateLimitExceededResponse(array $rateLimit): array
    {
        $resetAt = \Carbon\Carbon::createFromTimestamp($rateLimit['reset_at']);
        $secondsUntilReset = $resetAt->diffInSeconds(now());

        return [
            'success' => false,
            'message' => 'Rate limit exceeded. Please try again later.',
            'error' => 'rate_limit_exceeded',
            'rate_limit' => [
                'limit' => $rateLimit['limit'],
                'remaining' => 0,
                'reset_at' => $resetAt->toIso8601String(),
                'reset_in_seconds' => $secondsUntilReset,
            ],
        ];
    }

    /**
     * Get conversation with messages
     */
    public function getConversation(int $conversationId): ?ChatbotConversation
    {
        return Cache::remember(
            "chatbot.conversation.{$conversationId}",
            $this->conversationCacheTtl,
            function () use ($conversationId) {
                return ChatbotConversation::with('messages')
                    ->find($conversationId);
            }
        );
    }

    /**
     * Get conversation history (last N messages)
     */
    public function getConversationHistory(int $conversationId, ?int $limit = null): \Illuminate\Database\Eloquent\Collection
    {
        $limit = $limit ?? $this->maxContextMessages;

        return ChatbotMessage::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get conversation context for OpenAI
     */
    public function getConversationContext(int $conversationId): array
    {
        if ($this->contextService) {
            $conversation = ChatbotConversation::findOrFail($conversationId);

            return $this->contextService->buildContext($conversation);
        }

        $messages = $this->getConversationHistory($conversationId, $this->maxContextMessages);

        return $messages->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->message,
            ];
        })->toArray();
    }

    /**
     * Update conversation context
     */
    public function updateConversationContext(ChatbotConversation $conversation): void
    {
        try {
            $messageCount = $conversation->messages()->count();
            $lastMessage = $conversation->messages()->latest()->first();

            $context = array_merge($conversation->context ?? [], [
                'message_count' => $messageCount,
                'last_message_at' => $lastMessage?->created_at?->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ]);

            $conversation->update(['context' => $context]);

            // Clear cache
            Cache::forget("chatbot.conversation.{$conversation->id}");
        } catch (\Exception $e) {
            Log::warning('Failed to update conversation context', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get conversation statistics
     */
    public function getConversationStats(int $conversationId): array
    {
        $conversation = ChatbotConversation::findOrFail($conversationId);

        $messages = $conversation->messages;

        return [
            'total_messages' => $messages->count(),
            'user_messages' => $messages->where('role', 'user')->count(),
            'assistant_messages' => $messages->where('role', 'assistant')->count(),
            'total_tokens' => $messages->sum('tokens_used') ?? 0,
            'created_at' => $conversation->created_at->toIso8601String(),
            'last_message_at' => $messages->last()?->created_at?->toIso8601String(),
        ];
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
            'created_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Validate session ID
     *
     * @throws \InvalidArgumentException
     */
    protected function validateSessionId(string $sessionId): void
    {
        if (empty($sessionId)) {
            throw new \InvalidArgumentException('Session ID cannot be empty');
        }

        if (strlen($sessionId) > 255) {
            throw new \InvalidArgumentException('Session ID cannot exceed 255 characters');
        }
    }

    /**
     * Validate message
     *
     * @throws \InvalidArgumentException
     */
    protected function validateMessage(string $message): void
    {
        if (empty(trim($message))) {
            throw new \InvalidArgumentException('Message cannot be empty');
        }

        $maxLength = config('chatbot.max_message_length', 1000);
        if (strlen($message) > $maxLength) {
            throw new \InvalidArgumentException("Message cannot exceed {$maxLength} characters");
        }
    }

    /**
     * Validate role
     *
     * @throws \InvalidArgumentException
     */
    protected function validateRole(string $role): void
    {
        $validRoles = ['user', 'assistant', 'system'];

        if (! in_array($role, $validRoles)) {
            throw new \InvalidArgumentException('Invalid role. Must be one of: '.implode(', ', $validRoles));
        }
    }

    /**
     * Save message with validation
     */
    public function saveMessage(int $conversationId, string $role, string $message, ?int $tokensUsed = null): ChatbotMessage
    {
        try {
            $this->validateRole($role);
            $this->validateMessage($message);

            // Verify conversation exists
            $conversation = ChatbotConversation::findOrFail($conversationId);

            $chatbotMessage = ChatbotMessage::create([
                'conversation_id' => $conversationId,
                'role' => $role,
                'message' => $message,
                'tokens_used' => $tokensUsed,
            ]);

            // Clear conversation cache
            Cache::forget("chatbot.conversation.{$conversationId}");

            // Update conversation updated_at timestamp
            $conversation->touch();

            Log::debug('Chatbot message saved', [
                'message_id' => $chatbotMessage->id,
                'conversation_id' => $conversationId,
                'role' => $role,
                'message_length' => strlen($message),
                'tokens_used' => $tokensUsed,
            ]);

            return $chatbotMessage;
        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to save chatbot message');
            throw $e;
        }
    }

    /**
     * Archive old conversations
     *
     * @return int Number of conversations archived
     */
    public function archiveOldConversations(int $daysOld = 90): int
    {
        $conversations = ChatbotConversation::old($daysOld)->get();
        $count = 0;

        foreach ($conversations as $conversation) {
            // Update context to mark as archived
            $context = $conversation->context ?? [];
            $context['archived'] = true;
            $context['archived_at'] = now()->toIso8601String();

            $conversation->update(['context' => $context]);
            $count++;
        }

        Log::info('Chatbot conversations archived', [
            'count' => $count,
            'days_old' => $daysOld,
        ]);

        return $count;
    }

    /**
     * Clean up old messages
     *
     * @return int Number of messages deleted
     */
    public function cleanupOldMessages(int $daysOld = 365): int
    {
        $deleted = ChatbotMessage::where('created_at', '<', now()->subDays($daysOld))
            ->delete();

        Log::info('Chatbot messages cleaned up', [
            'deleted' => $deleted,
            'days_old' => $daysOld,
        ]);

        return $deleted;
    }

    /**
     * Get storage statistics
     */
    public function getStorageStats(): array
    {
        return [
            'total_conversations' => ChatbotConversation::count(),
            'active_conversations' => ChatbotConversation::active()->count(),
            'total_messages' => ChatbotMessage::count(),
            'total_tokens' => ChatbotMessage::sum('tokens_used') ?? 0,
            'old_conversations' => ChatbotConversation::old()->count(),
            'messages_last_30_days' => ChatbotMessage::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    /**
     * Get conversation with messages (paginated)
     */
    public function getConversationPaginated(int $conversationId, int $perPage = 20, int $page = 1): array
    {
        $cacheKey = "chatbot.conversation.{$conversationId}.page.{$page}.perpage.{$perPage}";

        return Cache::remember($cacheKey, 300, function () use ($conversationId, $perPage, $page) {
            $conversation = ChatbotConversation::findOrFail($conversationId);

            $messages = $conversation->messages()
                ->orderBy('created_at', 'asc')
                ->paginate($perPage, ['*'], 'page', $page);

            return [
                'conversation' => [
                    'id' => $conversation->id,
                    'session_id' => $conversation->session_id,
                    'user_id' => $conversation->user_id,
                    'created_at' => $conversation->created_at->toIso8601String(),
                    'updated_at' => $conversation->updated_at->toIso8601String(),
                    'total_messages' => $conversation->messages()->count(),
                ],
                'messages' => $messages->items(),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                    'last_page' => $messages->lastPage(),
                    'from' => $messages->firstItem(),
                    'to' => $messages->lastItem(),
                ],
            ];
        });
    }

    /**
     * Get conversation messages with filters
     */
    public function getConversationMessages(
        int $conversationId,
        array $filters = [],
        int $limit = 50
    ): \Illuminate\Database\Eloquent\Collection {
        $query = ChatbotMessage::where('conversation_id', $conversationId);

        // Filter by role
        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        // Filter by date range
        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        // Filter by tokens used
        if (isset($filters['min_tokens'])) {
            $query->where('tokens_used', '>=', $filters['min_tokens']);
        }

        // Order and limit
        $query->orderBy('created_at', $filters['order'] ?? 'asc')
            ->limit($limit);

        return $query->get();
    }

    /**
     * Get recent conversations for user
     */
    public function getRecentConversations(?int $userId = null, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $query = ChatbotConversation::with(['messages' => function ($q) {
            $q->latest()->limit(1);
        }]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search conversations
     */
    public function searchConversations(
        string $searchTerm,
        array $filters = [],
        int $limit = 20
    ): \Illuminate\Database\Eloquent\Collection {
        $query = ChatbotConversation::whereHas('messages', function ($q) use ($searchTerm) {
            $q->where('message', 'like', "%{$searchTerm}%");
        });

        // Filter by user
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by date range
        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return $query->with('messages')
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get conversation summary
     */
    public function getConversationSummary(int $conversationId): array
    {
        $cacheKey = "chatbot.conversation.summary.{$conversationId}";

        return Cache::remember($cacheKey, 600, function () use ($conversationId) {
            $conversation = ChatbotConversation::findOrFail($conversationId);
            $messages = $conversation->messages;

            return [
                'id' => $conversation->id,
                'session_id' => $conversation->session_id,
                'user_id' => $conversation->user_id,
                'total_messages' => $messages->count(),
                'user_messages' => $messages->where('role', 'user')->count(),
                'assistant_messages' => $messages->where('role', 'assistant')->count(),
                'total_tokens' => $messages->sum('tokens_used') ?? 0,
                'first_message_at' => $messages->first()?->created_at?->toIso8601String(),
                'last_message_at' => $messages->last()?->created_at?->toIso8601String(),
                'created_at' => $conversation->created_at->toIso8601String(),
                'updated_at' => $conversation->updated_at->toIso8601String(),
            ];
        });
    }

    /**
     * Clear conversation cache
     */
    public function clearConversationCache(int $conversationId): void
    {
        $patterns = [
            "chatbot.conversation.{$conversationId}",
            "chatbot.conversation.{$conversationId}.*",
            "chatbot.conversation.summary.{$conversationId}",
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }

        // Clear all paginated cache entries (if using tag-based cache)
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags(["chatbot.conversation.{$conversationId}"])->flush();
        }
    }
}
