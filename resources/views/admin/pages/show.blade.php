@extends('layouts.admin')

@section('title', 'View Page')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">{{ $page->title }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.pages.edit', $page->id) }}" 
                   class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Edit
                </a>
                <a href="{{ route('admin.pages.preview', $page->id) }}" 
                   target="_blank"
                   class="px-4 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-700 transition-colors">
                    Preview
                </a>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">
        {{-- Basic Information --}}
        <div>
            <h2 class="text-lg font-semibold mb-4">Basic Information</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Title</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $page->title }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Slug</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $page->slug }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Status</dt>
                    <dd class="mt-1">
                        @if($page->is_published)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Published
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Draft
                            </span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Created</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $page->created_at->format('M d, Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Updated</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $page->updated_at->format('M d, Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        @if($page->excerpt)
            <div>
                <h2 class="text-lg font-semibold mb-4">Excerpt</h2>
                <p class="text-neutral-700">{{ $page->excerpt }}</p>
            </div>
        @endif

        {{-- Content --}}
        <div>
            <h2 class="text-lg font-semibold mb-4">Content</h2>
            <div class="prose max-w-none">
                {!! $page->content !!}
            </div>
        </div>

        {{-- SEO Information --}}
        @if($page->meta_title || $page->meta_description || $page->meta_keywords)
            <div>
                <h2 class="text-lg font-semibold mb-4">SEO Information</h2>
                <dl class="space-y-4">
                    @if($page->meta_title)
                        <div>
                            <dt class="text-sm font-medium text-neutral-500">Meta Title</dt>
                            <dd class="mt-1 text-sm text-neutral-900">{{ $page->meta_title }}</dd>
                        </div>
                    @endif
                    @if($page->meta_description)
                        <div>
                            <dt class="text-sm font-medium text-neutral-500">Meta Description</dt>
                            <dd class="mt-1 text-sm text-neutral-900">{{ $page->meta_description }}</dd>
                        </div>
                    @endif
                    @if($page->meta_keywords)
                        <div>
                            <dt class="text-sm font-medium text-neutral-500">Meta Keywords</dt>
                            <dd class="mt-1 text-sm text-neutral-900">{{ $page->meta_keywords }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif
    </div>
</div>
@endsection
