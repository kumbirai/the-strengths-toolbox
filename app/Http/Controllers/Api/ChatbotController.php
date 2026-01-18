<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotRateLimitService;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ChatbotController extends Controller
{
    protected ChatbotService $chatbotService;

    protected ChatbotRateLimitService $rateLimitService;

    public function __construct(
        ChatbotService $chatbotService,
        ChatbotRateLimitService $rateLimitService
    ) {
        $this->chatbotService = $chatbotService;
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * Send a message to the chatbot
     */
    public function sendMessage(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000|min:1',
            'conversation_id' => 'nullable|integer|exists:chatbot_conversations,id',
            'session_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversationId = $request->input('conversation_id');
            $sessionId = $request->input('session_id') ?? session()->getId();
            $userId = auth()->check() ? auth()->id() : null;
            $ipAddress = $request->ip();
            $message = $request->input('message');

            // Get or create conversation
            if (! $conversationId) {
                $conversation = $this->chatbotService->createConversation($sessionId, $userId);
                $conversationId = $conversation->id;
            } else {
                $conversation = $this->chatbotService->getOrCreateConversation(
                    $conversationId,
                    $sessionId,
                    $userId
                );
                $conversationId = $conversation->id;
            }

            // Get rate limit status
            $rateLimitStatus = $this->rateLimitService->getRateLimitStatus($sessionId, 'session');

            // Send message and get response
            $response = $this->chatbotService->sendMessage(
                $conversationId,
                $message,
                $sessionId,
                $userId,
                $ipAddress
            );

            // Prepare response with rate limit info
            $jsonResponse = response()->json([
                'success' => $response['success'],
                'message' => $response['message'] ?? null,
                'conversation_id' => $conversationId,
                'user_message_id' => $response['user_message_id'] ?? null,
                'ai_message_id' => $response['ai_message_id'] ?? null,
                'tokens_used' => $response['tokens_used'] ?? null,
                'error' => $response['error'] ?? null,
                'error_code' => $response['error_code'] ?? null,
                'rate_limit' => $response['rate_limit'] ?? [
                    'remaining' => $rateLimitStatus['remaining'],
                    'reset_at' => \Carbon\Carbon::createFromTimestamp($rateLimitStatus['reset_at'])->toIso8601String(),
                ],
            ]);

            // Add rate limit headers
            if (isset($rateLimitStatus['limit'])) {
                $jsonResponse->withHeaders([
                    'X-RateLimit-Limit' => $rateLimitStatus['limit'],
                    'X-RateLimit-Remaining' => $rateLimitStatus['remaining'],
                    'X-RateLimit-Reset' => $rateLimitStatus['reset_at'],
                ]);
            }

            // Log successful request
            Log::info('Chatbot API request', [
                'conversation_id' => $conversationId,
                'session_id' => $sessionId,
                'user_id' => $userId,
                'message_length' => strlen($message),
                'success' => $response['success'],
            ]);

            return $jsonResponse;
        } catch (\Exception $e) {
            Log::error('Chatbot API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred processing your request. Please try again.',
                'error_code' => 'CHATBOT_API_ERROR',
            ], 500);
        }
    }

    /**
     * Get conversation history
     */
    public function getConversation(Request $request, int $conversationId): JsonResponse
    {
        try {
            $conversation = $this->chatbotService->getConversation($conversationId);

            if (! $conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found.',
                    'error_code' => 'CONVERSATION_NOT_FOUND',
                ], 404);
            }

            // Check if user has access (optional - implement based on requirements)
            $userId = auth()->id();
            if ($userId && $conversation->user_id && $conversation->user_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to conversation.',
                    'error_code' => 'UNAUTHORIZED',
                ], 403);
            }

            // Format messages
            $messages = $conversation->messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'message' => $message->message,
                    'tokens_used' => $message->tokens_used,
                    'created_at' => $message->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'conversation' => [
                    'id' => $conversation->id,
                    'session_id' => $conversation->session_id,
                    'user_id' => $conversation->user_id,
                    'created_at' => $conversation->created_at->toIso8601String(),
                    'updated_at' => $conversation->updated_at->toIso8601String(),
                ],
                'messages' => $messages,
                'message_count' => $messages->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot conversation retrieval error', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred retrieving the conversation.',
                'error_code' => 'CONVERSATION_RETRIEVAL_ERROR',
            ], 500);
        }
    }

    /**
     * Get conversation statistics
     */
    public function getConversationStats(Request $request, int $conversationId): JsonResponse
    {
        try {
            $stats = $this->chatbotService->getConversationStats($conversationId);

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot stats retrieval error', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred retrieving conversation statistics.',
                'error_code' => 'STATS_RETRIEVAL_ERROR',
            ], 500);
        }
    }

    /**
     * Get conversation with pagination
     */
    public function getConversationPaginated(Request $request, int $conversationId): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $page = (int) $request->input('page', 1);

        try {
            $data = $this->chatbotService->getConversationPaginated($conversationId, $perPage, $page);

            return response()->json([
                'success' => true,
                ...$data,
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot conversation retrieval error', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred retrieving the conversation.',
                'error_code' => 'CONVERSATION_RETRIEVAL_ERROR',
            ], 500);
        }
    }

    /**
     * Search conversations
     */
    public function searchConversations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|min:1|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid search parameters.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $filters = $request->only(['user_id', 'start_date', 'end_date']);
            $limit = (int) $request->input('limit', 20);
            $searchTerm = $request->input('search');

            $conversations = $this->chatbotService->searchConversations($searchTerm, $filters, $limit);

            return response()->json([
                'success' => true,
                'conversations' => $conversations,
                'count' => $conversations->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot search error', [
                'error' => $e->getMessage(),
                'search' => $request->input('search'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred searching conversations.',
                'error_code' => 'SEARCH_ERROR',
            ], 500);
        }
    }

    /**
     * Get conversation summary
     */
    public function getConversationSummary(Request $request, int $conversationId): JsonResponse
    {
        try {
            $summary = $this->chatbotService->getConversationSummary($conversationId);

            return response()->json([
                'success' => true,
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot summary retrieval error', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred retrieving conversation summary.',
                'error_code' => 'SUMMARY_RETRIEVAL_ERROR',
            ], 500);
        }
    }
}
