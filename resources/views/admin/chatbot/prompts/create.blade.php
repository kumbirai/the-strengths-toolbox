@extends('layouts.admin')

@section('title', 'Create Chatbot Prompt')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create Chatbot Prompt</h1>
        <p class="text-gray-600 mt-2">Create a new system prompt for the chatbot</p>
    </div>

    <form action="{{ route('admin.chatbot.prompts.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf

        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea name="description" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Template <span class="text-red-500">*</span>
                </label>
                <textarea name="template" rows="10" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 font-mono text-sm">{{ old('template') }}</textarea>
                <p class="text-sm text-gray-500 mt-1">Use {variable_name} for placeholders</p>
                @error('template')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    Active
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    Set as Default
                </label>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('admin.chatbot.prompts.index') }}" 
                   class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700">
                    Create Prompt
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
