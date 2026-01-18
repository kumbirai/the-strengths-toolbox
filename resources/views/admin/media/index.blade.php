@extends('layouts.admin')

@section('title', 'Media Library')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Media Library</h1>
            <button onclick="openUploadModal()" 
                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                Upload Media
            </button>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="p-6 border-b border-neutral-200">
        <form method="GET" action="{{ route('admin.media.index') }}" class="flex gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search media..." 
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <select name="type" 
                        class="px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Images</option>
                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other Files</option>
                </select>
            </div>
            <button type="submit" 
                    class="px-4 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-700 transition-colors">
                Filter
            </button>
            @if(request('search') || request('type'))
                <a href="{{ route('admin.media.index') }}" 
                   class="px-4 py-2 bg-neutral-300 text-neutral-700 rounded-lg hover:bg-neutral-400 transition-colors">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Media Grid --}}
    <div class="p-6">
        @if($media->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($media as $item)
                    <div class="group relative bg-neutral-50 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                        @if($item->isImage())
                            <a href="{{ route('admin.media.show', $item->id) }}">
                                <img src="{{ $item->thumbnail_url ?? $item->url }}" 
                                     alt="{{ $item->alt_text ?? $item->original_filename }}"
                                     class="w-full h-32 object-cover">
                            </a>
                        @else
                            <a href="{{ route('admin.media.show', $item->id) }}">
                                <div class="w-full h-32 bg-neutral-200 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </a>
                        @endif
                        
                        <div class="p-2">
                            <p class="text-xs text-neutral-600 truncate" title="{{ $item->original_filename }}">
                                {{ $item->original_filename }}
                            </p>
                        </div>

                        {{-- Actions overlay --}}
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                            <a href="{{ route('admin.media.show', $item->id) }}" 
                               class="px-3 py-1 bg-white text-neutral-900 rounded text-sm hover:bg-neutral-100">
                                View
                            </a>
                            <button onclick="confirmDelete({{ $item->id }})"
                                    class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                                Delete
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-neutral-500">No media files found.</p>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if($media->hasPages())
        <div class="p-6 border-t border-neutral-200">
            {{ $media->links() }}
        </div>
    @endif
</div>

{{-- Upload Modal --}}
<div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold mb-4">Upload Media</h3>
        <form id="uploadForm" method="POST" action="{{ route('admin.media.upload') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="file" class="block text-sm font-medium text-neutral-700 mb-1">
                    File <span class="text-red-500">*</span>
                </label>
                <input type="file" 
                       id="file" 
                       name="file" 
                       required
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label for="alt_text" class="block text-sm font-medium text-neutral-700 mb-1">
                    Alt Text
                </label>
                <input type="text" 
                       id="alt_text" 
                       name="alt_text"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-neutral-700 mb-1">
                    Description
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3"
                          class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
            </div>
            <div class="flex gap-4">
                <button type="button" 
                        onclick="closeUploadModal()"
                        class="flex-1 px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Upload
                </button>
            </div>
        </form>
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
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadForm').reset();
}

function confirmDelete(mediaId) {
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('admin/media') }}/${mediaId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Handle form submission
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();
    
    xhr.open('POST', this.action);
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                window.location.reload();
            } else {
                alert('Upload failed: ' + response.message);
            }
        } else {
            alert('Upload failed. Please try again.');
        }
    };
    
    xhr.send(formData);
});
</script>
@endpush
@endsection
