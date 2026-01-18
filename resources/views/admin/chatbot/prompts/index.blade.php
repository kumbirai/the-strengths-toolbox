@extends('layouts.admin')

@section('title', 'Chatbot Prompts')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Chatbot Prompts</h1>
            <p class="text-gray-600 mt-2">Manage system prompts for the chatbot</p>
        </div>
        <a href="{{ route('admin.chatbot.prompts.create') }}" 
           class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">
            Create Prompt
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Version</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($prompts as $prompt)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $prompt->name }}</div>
                        <div class="text-sm text-gray-500">{{ Str::limit($prompt->description, 50) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full {{ $prompt->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $prompt->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if($prompt->is_default)
                            <span class="ml-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Default</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        v{{ $prompt->version }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.chatbot.prompts.edit', $prompt) }}" 
                           class="text-primary-600 hover:text-primary-900">Edit</a>
                        <form action="{{ route('admin.chatbot.prompts.destroy', $prompt) }}" 
                              method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-red-600 hover:text-red-900 ml-4"
                                    onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
