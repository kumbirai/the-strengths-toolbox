@extends('layouts.admin')

@section('title', 'Edit Page')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Edit Page</h1>
            <a href="{{ route('admin.pages.preview', $page->id) }}" 
               target="_blank"
               class="px-4 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-700 transition-colors">
                Preview
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.pages.update', $page->id) }}" class="p-6">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            {{-- Basic Information --}}
            <div>
                <h2 class="text-lg font-semibold mb-4">Basic Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-neutral-700 mb-1">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               value="{{ old('title', $page->title) }}"
                               required
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-neutral-700 mb-1">
                            Slug
                        </label>
                        <input type="text" 
                               id="slug" 
                               name="slug" 
                               value="{{ old('slug', $page->slug) }}"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('slug') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-neutral-500">Leave empty to auto-generate from title</p>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="excerpt" class="block text-sm font-medium text-neutral-700 mb-1">
                            Excerpt
                        </label>
                        <textarea id="excerpt" 
                                  name="excerpt" 
                                  rows="3"
                                  class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('excerpt') border-red-500 @enderror">{{ old('excerpt', $page->excerpt) }}</textarea>
                        <p class="mt-1 text-sm text-neutral-500">Brief description of the page</p>
                        @error('excerpt')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div>
                <h2 class="text-lg font-semibold mb-4">Content</h2>
                <div>
                    <label for="content" class="block text-sm font-medium text-neutral-700 mb-1">
                        Content <span class="text-red-500">*</span>
                    </label>
                    <textarea id="content" 
                              name="content" 
                              rows="15"
                              required
                              class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('content') border-red-500 @enderror">{{ old('content', $page->content) }}</textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- SEO Settings --}}
            <div>
                <h2 class="text-lg font-semibold mb-4">SEO Settings</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="meta_title" class="block text-sm font-medium text-neutral-700 mb-1">
                            Meta Title
                        </label>
                        <input type="text" 
                               id="meta_title" 
                               name="meta_title" 
                               value="{{ old('meta_title', $page->meta_title) }}"
                               maxlength="60"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('meta_title') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-neutral-500">
                            <span id="meta_title_count">0</span>/60 characters (recommended: 50-60)
                        </p>
                        @error('meta_title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="meta_description" class="block text-sm font-medium text-neutral-700 mb-1">
                            Meta Description
                        </label>
                        <textarea id="meta_description" 
                                  name="meta_description" 
                                  rows="3"
                                  maxlength="160"
                                  class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('meta_description') border-red-500 @enderror">{{ old('meta_description', $page->meta_description) }}</textarea>
                        <p class="mt-1 text-sm text-neutral-500">
                            <span id="meta_description_count">0</span>/160 characters (recommended: 150-160)
                        </p>
                        @error('meta_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="meta_keywords" class="block text-sm font-medium text-neutral-700 mb-1">
                            Meta Keywords
                        </label>
                        <input type="text" 
                               id="meta_keywords" 
                               name="meta_keywords" 
                               value="{{ old('meta_keywords', $page->meta_keywords) }}"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('meta_keywords') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-neutral-500">Comma-separated keywords</p>
                        @error('meta_keywords')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Publishing Options --}}
            <div>
                <h2 class="text-lg font-semibold mb-4">Publishing Options</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_published" 
                               name="is_published" 
                               value="1"
                               {{ old('is_published', $page->is_published) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                        <label for="is_published" class="ml-2 text-sm font-medium text-neutral-700">
                            Publish immediately
                        </label>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-neutral-200">
                <a href="{{ route('admin.pages.index') }}" 
                   class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Update Page
                </button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key', 'no-api-key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
@endpush

@push('scripts')
<script>
// Initialize TinyMCE
document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#content',
            height: 500,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help | code',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            branding: false,
            promotion: false,
            images_upload_url: '{{ route("admin.media.upload") }}',
            automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_handler: function (blobInfo, progress) {
                return new Promise(function (resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', '{{ route("admin.media.upload") }}');
                    
                    xhr.upload.onprogress = function (e) {
                        progress(e.loaded / e.total * 100);
                    };
                    
                    xhr.onload = function () {
                        if (xhr.status === 403) {
                            reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                            return;
                        }
                        
                        if (xhr.status < 200 || xhr.status >= 300) {
                            reject('HTTP Error: ' + xhr.status);
                            return;
                        }
                        
                        var json = JSON.parse(xhr.responseText);
                        
                        if (!json || typeof json.location != 'string') {
                            reject('Invalid JSON: ' + xhr.responseText);
                            return;
                        }
                        
                        resolve(json.location);
                    };
                    
                    xhr.onerror = function () {
                        reject('Image upload failed');
                    };
                    
                    var formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    formData.append('_token', '{{ csrf_token() }}');
                    
                    xhr.send(formData);
                });
            }
        });
    }
});

// Auto-generate slug from title
document.getElementById('title').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
        const title = this.value;
        slugInput.value = title.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        slugInput.dataset.autoGenerated = 'true';
    }
});

// Manual slug edit disables auto-generation
document.getElementById('slug').addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
});

// Character counters
document.getElementById('meta_title').addEventListener('input', function() {
    document.getElementById('meta_title_count').textContent = this.value.length;
});

document.getElementById('meta_description').addEventListener('input', function() {
    document.getElementById('meta_description_count').textContent = this.value.length;
});

// Initialize counters
document.getElementById('meta_title_count').textContent = document.getElementById('meta_title').value.length;
document.getElementById('meta_description_count').textContent = document.getElementById('meta_description').value.length;
</script>
@endpush
@endsection
