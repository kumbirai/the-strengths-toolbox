<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminChatbotController extends Controller
{
    /**
     * Show chatbot configuration
     */
    public function index()
    {
        $config = [
            'general' => ChatbotConfig::getByGroup('general'),
            'openai' => ChatbotConfig::getByGroup('openai'),
            'rate_limiting' => ChatbotConfig::getByGroup('rate_limiting'),
            'system_prompt' => ChatbotConfig::getByGroup('system_prompt'),
        ];

        return view('admin.chatbot.index', compact('config'));
    }

    /**
     * Update chatbot configuration
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enabled' => 'nullable|boolean',
            'max_context_messages' => 'nullable|integer|min:1|max:50',
            'max_message_length' => 'nullable|integer|min:1|max:5000',
            'openai_model' => 'nullable|string|in:gpt-4,gpt-3.5-turbo',
            'openai_max_tokens' => 'nullable|integer|min:1|max:4000',
            'openai_temperature' => 'nullable|numeric|min:0|max:2',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:100',
            'rate_limit_per_hour' => 'nullable|integer|min:1|max:1000',
            'system_prompt' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update general settings
        ChatbotConfig::set('enabled', $request->input('enabled', true), 'boolean', 'general');
        ChatbotConfig::set('max_context_messages', $request->input('max_context_messages', 10), 'integer', 'general');
        ChatbotConfig::set('max_message_length', $request->input('max_message_length', 1000), 'integer', 'general');

        // Update OpenAI settings
        ChatbotConfig::set('model', $request->input('openai_model', 'gpt-4'), 'string', 'openai');
        ChatbotConfig::set('max_tokens', $request->input('openai_max_tokens', 500), 'integer', 'openai');
        ChatbotConfig::set('temperature', $request->input('openai_temperature', 0.7), 'float', 'openai');

        // Update rate limiting
        ChatbotConfig::set('per_minute', $request->input('rate_limit_per_minute', 10), 'integer', 'rate_limiting');
        ChatbotConfig::set('per_hour', $request->input('rate_limit_per_hour', 60), 'integer', 'rate_limiting');

        // Update system prompt
        ChatbotConfig::set('default_prompt', $request->input('system_prompt', ''), 'string', 'system_prompt');

        return redirect()->route('admin.chatbot.index')
            ->with('success', 'Chatbot configuration updated successfully.');
    }

    /**
     * Test chatbot configuration
     */
    public function test(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_message' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid test message.',
            ], 422);
        }

        try {
            $chatbotService = app(\App\Services\ChatbotService::class);
            $conversation = $chatbotService->createConversation('test_'.time());

            $response = $chatbotService->sendMessage(
                $conversation->id,
                $request->input('test_message')
            );

            return response()->json([
                'success' => true,
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
