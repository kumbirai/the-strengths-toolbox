@extends('layouts.admin')

@section('title', 'Conversation Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.chatbot.conversations.index') }}" 
           class="text-primary-600 hover:text-primary-900 mb-4 inline-block">
            ← Back to Conversations
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Conversation #{{ $conversation->id }}</h1>
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total Messages</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_messages'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">User Messages</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['user_messages'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Assistant Messages</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['assistant_messages'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total Tokens</div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_tokens']) }}</div>
        </div>
    </div>

    {{-- Conversation Info --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Conversation Information</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-600">Session ID:</span>
                <p class="text-sm font-medium">{{ $conversation->session_id }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-600">User:</span>
                <p class="text-sm font-medium">{{ $conversation->user ? $conversation->user->name : 'Guest' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-600">Created:</span>
                <p class="text-sm font-medium">{{ $conversation->created_at->format('Y-m-d H:i:s') }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-600">Last Message:</span>
                <p class="text-sm font-medium">{{ $stats['last_message_at'] ? \Carbon\Carbon::parse($stats['last_message_at'])->format('Y-m-d H:i:s') : 'N/A' }}</p>
            </div>
        </div>
    </div>

    {{-- Messages --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Messages</h2>
        <div class="space-y-4">
            @foreach($conversation->messages as $message)
            <div class="border-l-4 {{ $message->role === 'user' ? 'border-primary-600' : 'border-gray-400' }} pl-4 py-2">
                <div class="flex justify-between items-start mb-1">
                    <span class="text-sm font-medium {{ $message->role === 'user' ? 'text-primary-600' : 'text-gray-600' }}">
                        {{ ucfirst($message->role) }}
                    </span>
                    <span class="text-xs text-gray-500">
                        {{ $message->created_at->format('H:i:s') }}
                        @if($message->tokens_used)
                            <span class="ml-2">({{ $message->tokens_used }} tokens)</span>
                        @endif
                    </span>
                </div>
                <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $message->message }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-6 flex gap-4">
        <a href="{{ route('admin.chatbot.conversations.export', $conversation) }}" 
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Export Conversation
        </a>
        <form action="{{ route('admin.chatbot.conversations.destroy', $conversation) }}" 
              method="POST" 
              onsubmit="return confirm('Are you sure you want to delete this conversation?')">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                Delete Conversation
            </button>
        </form>
    </div>
</div>
@endsection
