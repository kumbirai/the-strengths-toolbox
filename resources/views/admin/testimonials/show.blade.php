@extends('layouts.admin')

@section('title', 'View Testimonial')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">{{ $testimonial->name }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.testimonials.edit', $testimonial->id) }}" 
                   class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Edit
                </a>
                <a href="{{ route('admin.testimonials.index') }}" 
                   class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">
        <div>
            <h2 class="text-lg font-semibold mb-4">Testimonial Information</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Name</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $testimonial->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Company</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $testimonial->company ?? '—' }}</dd>
                </div>
                @if($testimonial->rating)
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Rating</dt>
                        <dd class="mt-1">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $testimonial->rating)
                                        <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-neutral-300 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    @endif
                                @endfor
                                <span class="ml-2 text-sm text-neutral-600">{{ $testimonial->rating }}/5</span>
                            </div>
                        </dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Status</dt>
                    <dd class="mt-1">
                        @if($testimonial->is_featured)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-primary-100 text-primary-800">
                                Featured
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-neutral-100 text-neutral-800">
                                Regular
                            </span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Display Order</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $testimonial->display_order }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Created</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $testimonial->created_at->format('M d, Y H:i') }}</dd>
                </div>
                @if($testimonial->user)
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">User</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $testimonial->user->name }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div>
            <h2 class="text-lg font-semibold mb-4">Testimonial Content</h2>
            <div class="bg-neutral-50 rounded-lg p-6">
                <p class="text-neutral-900 leading-relaxed">{{ $testimonial->testimonial }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
