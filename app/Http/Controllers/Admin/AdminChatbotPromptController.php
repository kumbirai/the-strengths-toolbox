<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotPrompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminChatbotPromptController extends Controller
{
    /**
     * List all prompts
     */
    public function index()
    {
        $prompts = ChatbotPrompt::orderBy('created_at', 'desc')->get();

        return view('admin.chatbot.prompts.index', compact('prompts'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.chatbot.prompts.create');
    }

    /**
     * Store new prompt
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'template' => 'required|string|max:5000',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // If setting as default, unset other defaults
        if ($request->input('is_default')) {
            ChatbotPrompt::where('is_default', true)->update(['is_default' => false]);
        }

        $prompt = ChatbotPrompt::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'template' => $request->input('template'),
            'variables' => $request->input('variables', []),
            'is_active' => $request->input('is_active', true),
            'is_default' => $request->input('is_default', false),
            'version' => 1,
        ]);

        return redirect()->route('admin.chatbot.prompts.index')
            ->with('success', 'Prompt created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(ChatbotPrompt $prompt)
    {
        return view('admin.chatbot.prompts.edit', compact('prompt'));
    }

    /**
     * Update prompt
     */
    public function update(Request $request, ChatbotPrompt $prompt)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'template' => 'required|string|max:5000',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // If setting as default, unset other defaults
        if ($request->input('is_default') && ! $prompt->is_default) {
            ChatbotPrompt::where('is_default', true)->update(['is_default' => false]);
        }

        $prompt->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'template' => $request->input('template'),
            'variables' => $request->input('variables', []),
            'is_active' => $request->input('is_active', $prompt->is_active),
            'is_default' => $request->input('is_default', $prompt->is_default),
            'version' => $prompt->version + 1,
        ]);

        return redirect()->route('admin.chatbot.prompts.index')
            ->with('success', 'Prompt updated successfully.');
    }

    /**
     * Delete prompt
     */
    public function destroy(ChatbotPrompt $prompt)
    {
        $prompt->delete();

        return redirect()->route('admin.chatbot.prompts.index')
            ->with('success', 'Prompt deleted successfully.');
    }

    /**
     * Test prompt
     */
    public function test(Request $request, ChatbotPrompt $prompt)
    {
        $variables = $request->input('variables', []);
        $rendered = $prompt->render($variables);

        return response()->json([
            'success' => true,
            'rendered' => $rendered,
        ]);
    }
}
