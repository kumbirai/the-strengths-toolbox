@extends('layouts.admin')

@section('title', 'Chatbot Configuration')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Chatbot Configuration</h1>
        <p class="text-gray-600 mt-2">Configure chatbot settings and behavior</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.chatbot.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- General Settings --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">General Settings</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <input type="checkbox" name="enabled" value="1" 
                               {{ ($config['general']['enabled'] ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        Enable Chatbot
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Max Context Messages
                    </label>
                    <input type="number" name="max_context_messages" 
                           value="{{ $config['general']['max_context_messages'] ?? 10 }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                           min="1" max="50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Max Message Length
                    </label>
                    <input type="number" name="max_message_length" 
                           value="{{ $config['general']['max_message_length'] ?? 1000 }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                           min="1" max="5000">
                </div>
            </div>
        </div>

        {{-- OpenAI Settings --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">OpenAI Settings</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Model
                    </label>
                    <select name="openai_model" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="gpt-4" {{ ($config['openai']['model'] ?? 'gpt-4') === 'gpt-4' ? 'selected' : '' }}>GPT-4</option>
                        <option value="gpt-3.5-turbo" {{ ($config['openai']['model'] ?? '') === 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Max Tokens
                    </label>
                    <input type="number" name="openai_max_tokens" 
                           value="{{ $config['openai']['max_tokens'] ?? 500 }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                           min="1" max="4000">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Temperature (0-2)
                    </label>
                    <input type="number" name="openai_temperature" 
                           value="{{ $config['openai']['temperature'] ?? 0.7 }}"
                           step="0.1"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                           min="0" max="2">
                </div>
            </div>
        </div>

        {{-- Rate Limiting --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Rate Limiting</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Requests Per Minute
                    </label>
                    <input type="number" name="rate_limit_per_minute" 
                           value="{{ $config['rate_limiting']['per_minute'] ?? 10 }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                           min="1" max="100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Requests Per Hour
                    </label>
                    <input type="number" name="rate_limit_per_hour" 
                           value="{{ $config['rate_limiting']['per_hour'] ?? 60 }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                           min="1" max="1000">
                </div>
            </div>
        </div>

        {{-- System Prompt --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">System Prompt</h2>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Default System Prompt
                </label>
                <textarea name="system_prompt" rows="6"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                          maxlength="2000">{{ $config['system_prompt']['default_prompt'] ?? '' }}</textarea>
                <p class="text-sm text-gray-500 mt-1">Maximum 2000 characters</p>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="flex justify-end">
            <button type="submit" 
                    class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                Save Configuration
            </button>
        </div>
    </form>

    {{-- Test Section --}}
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h2 class="text-xl font-semibold mb-4">Test Configuration</h2>
        <div x-data="{ testing: false, message: '', response: null }">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Test Message
                    </label>
                    <input type="text" x-model="message" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                           placeholder="Enter a test message">
                </div>
                <button @click="testing = true; fetch('{{ route('admin.chatbot.test') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ test_message: message }) }).then(r => r.json()).then(d => { response = d; testing = false; })"
                        :disabled="testing || !message"
                        class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 disabled:opacity-50">
                    <span x-show="!testing">Test Chatbot</span>
                    <span x-show="testing">Testing...</span>
                </button>
                <div x-show="response" class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <pre x-text="JSON.stringify(response, null, 2)" class="text-sm"></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
