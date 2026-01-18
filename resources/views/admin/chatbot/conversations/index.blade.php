@extends('layouts.admin')

@section('title', 'Chatbot Conversations')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Chatbot Conversations</h1>
        <p class="text-gray-600 mt-2">View and manage chatbot conversations</p>
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total Conversations</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_conversations'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Active Conversations</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['active_conversations'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total Messages</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_messages'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total Tokens</div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_tokens']) }}</div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" 
                   value="{{ request('search') }}"
                   placeholder="Search messages..."
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2">
            <input type="date" name="start_date" 
                   value="{{ request('start_date') }}"
                   class="border border-gray-300 rounded-lg px-4 py-2">
            <input type="date" name="end_date" 
                   value="{{ request('end_date') }}"
                   class="border border-gray-300 rounded-lg px-4 py-2">
            <button type="submit" 
                    class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700">
                Search
            </button>
            <a href="{{ route('admin.chatbot.conversations.index') }}" 
               class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">
                Clear
            </a>
        </form>
    </div>

    {{-- Conversations Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Session</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Messages</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($conversations as $conversation)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        #{{ $conversation->id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ Str::limit($conversation->session_id, 20) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $conversation->user ? $conversation->user->name : 'Guest' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $conversation->messages->count() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $conversation->created_at->format('Y-m-d H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.chatbot.conversations.show', $conversation) }}" 
                           class="text-primary-600 hover:text-primary-900">View</a>
                        <a href="{{ route('admin.chatbot.conversations.export', $conversation) }}" 
                           class="text-blue-600 hover:text-blue-900 ml-4">Export</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $conversations->links() }}
    </div>
</div>
@endsection
