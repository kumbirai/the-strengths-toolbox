@extends('layouts.admin')

@section('title', 'Media Details')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Media Details</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.media.index') }}" 
                   class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                    Back to Library
                </a>
                <button onclick="confirmDelete({{ $media->id }})"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Media Preview --}}
            <div>
                <h2 class="text-lg font-semibold mb-4">Preview</h2>
                @if($media->isImage())
                    <div class="bg-neutral-50 rounded-lg p-4">
                        <img src="{{ $media->url }}" 
                             alt="{{ $media->alt_text ?? $media->original_filename }}"
                             class="w-full rounded-lg">
                    </div>
                    @if($media->thumbnail_url)
                        <div class="mt-4">
                            <h3 class="text-sm font-medium text-neutral-700 mb-2">Thumbnail</h3>
                            <img src="{{ $media->thumbnail_url }}" 
                                 alt="Thumbnail"
                                 class="w-32 h-32 object-cover rounded-lg">
                        </div>
                    @endif
                @else
                    <div class="bg-neutral-50 rounded-lg p-12 flex items-center justify-center">
                        <svg class="w-24 h-24 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ $media->url }}" 
                           target="_blank"
                           class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                            Download File
                        </a>
                    </div>
                @endif
            </div>

            {{-- Media Information --}}
            <div>
                <h2 class="text-lg font-semibold mb-4">Information</h2>
                
                <form method="POST" action="{{ route('admin.media.update', $media->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="alt_text" class="block text-sm font-medium text-neutral-700 mb-1">
                            Alt Text
                        </label>
                        <input type="text" 
                               id="alt_text" 
                               name="alt_text" 
                               value="{{ old('alt_text', $media->alt_text) }}"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-neutral-700 mb-1">
                            Description
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">{{ old('description', $media->description) }}</textarea>
                    </div>

                    <div class="pt-4 border-t border-neutral-200">
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Filename</dt>
                                <dd class="text-sm text-neutral-900">{{ $media->filename }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Original Filename</dt>
                                <dd class="text-sm text-neutral-900">{{ $media->original_filename }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">MIME Type</dt>
                                <dd class="text-sm text-neutral-900">{{ $media->mime_type }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Size</dt>
                                <dd class="text-sm text-neutral-900">{{ number_format($media->size / 1024, 2) }} KB</dd>
                            </div>
                            @if($media->isImage())
                                <div>
                                    <dt class="text-sm font-medium text-neutral-500">Dimensions</dt>
                                    <dd class="text-sm text-neutral-900">{{ $media->width }} × {{ $media->height }} px</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">Uploaded</dt>
                                <dd class="text-sm text-neutral-900">{{ $media->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                            @if($media->uploader)
                                <div>
                                    <dt class="text-sm font-medium text-neutral-500">Uploaded By</dt>
                                    <dd class="text-sm text-neutral-900">{{ $media->uploader->name }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-neutral-500">URL</dt>
                                <dd class="text-sm text-neutral-900 break-all">
                                    <a href="{{ $media->url }}" target="_blank" class="text-primary-600 hover:underline">
                                        {{ $media->url }}
                                    </a>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4">
                        <button type="submit" 
                                class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
        <p class="text-neutral-600 mb-6">Are you sure you want to delete this media file? This action cannot be undone.</p>
        <form id="deleteForm" method="POST" class="flex gap-4">
            @csrf
            @method('DELETE')
            <button type="button" 
                    onclick="closeDeleteModal()"
                    class="flex-1 px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                Cancel
            </button>
            <button type="submit" 
                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                Delete
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(mediaId) {
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('admin/media') }}/${mediaId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
@endpush
@endsection
