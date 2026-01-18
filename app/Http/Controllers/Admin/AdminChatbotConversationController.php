<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotConversation;
use App\Services\ChatbotService;
use Illuminate\Http\Request;

class AdminChatbotConversationController extends Controller
{
    protected ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * List conversations
     */
    public function index(Request $request)
    {
        $query = ChatbotConversation::with(['user', 'messages']);

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('messages', function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%");
            });
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by date
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->input('end_date'));
        }

        $conversations = $query->orderBy('updated_at', 'desc')
            ->paginate(20);

        $stats = $this->chatbotService->getStorageStats();

        return view('admin.chatbot.conversations.index', compact('conversations', 'stats'));
    }

    /**
     * Show conversation details
     */
    public function show(ChatbotConversation $conversation)
    {
        $conversation->load('messages', 'user');
        $stats = $this->chatbotService->getConversationStats($conversation->id);

        return view('admin.chatbot.conversations.show', compact('conversation', 'stats'));
    }

    /**
     * Delete conversation
     */
    public function destroy(ChatbotConversation $conversation)
    {
        $conversation->delete();

        return redirect()->route('admin.chatbot.conversations.index')
            ->with('success', 'Conversation deleted successfully.');
    }

    /**
     * Export conversation
     */
    public function export(ChatbotConversation $conversation)
    {
        $conversation->load('messages');

        $data = [
            'conversation_id' => $conversation->id,
            'session_id' => $conversation->session_id,
            'user_id' => $conversation->user_id,
            'created_at' => $conversation->created_at->toIso8601String(),
            'messages' => $conversation->messages->map(function ($message) {
                return [
                    'role' => $message->role,
                    'message' => $message->message,
                    'tokens_used' => $message->tokens_used,
                    'created_at' => $message->created_at->toIso8601String(),
                ];
            }),
        ];

        return response()->json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="conversation-'.$conversation->id.'.json"',
        ]);
    }
}
