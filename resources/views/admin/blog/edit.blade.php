@extends('layouts.admin')

@section('title', 'Edit Blog Post')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <h1 class="text-2xl font-bold">Edit Blog Post</h1>
    </div>

    <form method="POST" action="{{ route('admin.blog.update', $post->id) }}" enctype="multipart/form-data" class="p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
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
                                   value="{{ old('title', $post->title) }}"
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
                                   value="{{ old('slug', $post->slug) }}"
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
                                      class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('excerpt') border-red-500 @enderror">{{ old('excerpt', $post->excerpt) }}</textarea>
                            <p class="mt-1 text-sm text-neutral-500">Brief description of the post</p>
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
                                  class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('content') border-red-500 @enderror">{{ old('content', $post->content) }}</textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Publishing Options --}}
                <div class="bg-neutral-50 rounded-lg p-4">
                    <h2 class="text-lg font-semibold mb-4">Publishing Options</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="author_id" class="block text-sm font-medium text-neutral-700 mb-1">
                                Author <span class="text-red-500">*</span>
                            </label>
                            <select id="author_id" 
                                    name="author_id" 
                                    required
                                    class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('author_id') border-red-500 @enderror">
                                <option value="">Select Author</option>
                                @foreach(\App\Models\User::all() as $user)
                                    <option value="{{ $user->id }}" {{ old('author_id', $post->author_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('author_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="published_at" class="block text-sm font-medium text-neutral-700 mb-1">
                                Publish Date
                            </label>
                            <input type="datetime-local" 
                                   id="published_at" 
                                   name="published_at" 
                                   value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                                   class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('published_at') border-red-500 @enderror">
                            <p class="mt-1 text-sm text-neutral-500">Schedule for future publication</p>
                            @error('published_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="is_published" 
                                   name="is_published" 
                                   value="1"
                                   {{ old('is_published', $post->is_published) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                            <label for="is_published" class="ml-2 text-sm font-medium text-neutral-700">
                                Publish immediately
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Featured Image --}}
                <div class="bg-neutral-50 rounded-lg p-4">
                    <h2 class="text-lg font-semibold mb-4">Featured Image</h2>
                    
                    @if($post->featured_image)
                        <div class="mb-4">
                            <img src="{{ asset('storage/' . $post->featured_image) }}" 
                                 alt="{{ $post->title }}"
                                 class="w-full rounded-lg">
                            <p class="mt-2 text-sm text-neutral-500">Current featured image</p>
                        </div>
                    @endif
                    
                    <div>
                        <label for="featured_image" class="block text-sm font-medium text-neutral-700 mb-1">
                            {{ $post->featured_image ? 'Replace Image' : 'Upload Image' }}
                        </label>
                        <input type="file" 
                               id="featured_image" 
                               name="featured_image" 
                               accept="image/*"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('featured_image') border-red-500 @enderror">
                        @error('featured_image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <div id="imagePreview" class="mt-4 hidden">
                            <img id="previewImg" src="" alt="Preview" class="w-full rounded-lg">
                        </div>
                    </div>
                </div>

                {{-- Categories --}}
                <div class="bg-neutral-50 rounded-lg p-4">
                    <h2 class="text-lg font-semibold mb-4">Categories</h2>
                    
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @forelse($categories as $category)
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="category_{{ $category->id }}" 
                                       name="category_ids[]" 
                                       value="{{ $category->id }}"
                                       {{ in_array($category->id, old('category_ids', $post->categories->pluck('id')->toArray())) ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                                <label for="category_{{ $category->id }}" class="ml-2 text-sm text-neutral-700">
                                    {{ $category->name }}
                                </label>
                            </div>
                        @empty
                            <p class="text-sm text-neutral-500">No categories available.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Tags --}}
                <div class="bg-neutral-50 rounded-lg p-4">
                    <h2 class="text-lg font-semibold mb-4">Tags</h2>
                    
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @forelse($tags as $tag)
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="tag_{{ $tag->id }}" 
                                       name="tag_ids[]" 
                                       value="{{ $tag->id }}"
                                       {{ in_array($tag->id, old('tag_ids', $post->tags->pluck('id')->toArray())) ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                                <label for="tag_{{ $tag->id }}" class="ml-2 text-sm text-neutral-700">
                                    {{ $tag->name }}
                                </label>
                            </div>
                        @empty
                            <p class="text-sm text-neutral-500">No tags available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-neutral-200">
            <a href="{{ route('admin.blog.index') }}" 
               class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                Update Post
            </button>
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
            promotion: false
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

// Featured image preview
document.getElementById('featured_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection
