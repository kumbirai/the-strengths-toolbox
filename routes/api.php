<?php

use App\Http\Controllers\Api\ChatbotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Suppress bot requests for non-existent analytics endpoints
Route::get('/admin/analytics/dashboard', function () {
    return response()->noContent();
});

// Chatbot API routes
Route::prefix('chatbot')->name('api.chatbot.')->group(function () {
    // Send message endpoint
    Route::post('/message', [ChatbotController::class, 'sendMessage'])
        ->middleware(['throttle:10,1', 'chatbot.ratelimit'])
        ->name('message');

    // Get conversation history
    Route::get('/conversation/{conversationId}', [ChatbotController::class, 'getConversation'])
        ->middleware(['throttle:20,1'])
        ->name('conversation');

    // Get conversation with pagination
    Route::get('/conversation/{conversationId}/messages', [ChatbotController::class, 'getConversationPaginated'])
        ->middleware(['throttle:20,1'])
        ->name('conversation.messages');

    // Get conversation statistics
    Route::get('/conversation/{conversationId}/stats', [ChatbotController::class, 'getConversationStats'])
        ->middleware(['throttle:20,1'])
        ->name('conversation.stats');

    // Get conversation summary
    Route::get('/conversation/{conversationId}/summary', [ChatbotController::class, 'getConversationSummary'])
        ->middleware(['throttle:20,1'])
        ->name('conversation.summary');

    // Search conversations
    Route::get('/conversations/search', [ChatbotController::class, 'searchConversations'])
        ->middleware(['throttle:10,1'])
        ->name('conversations.search');
});
