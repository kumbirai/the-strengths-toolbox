@extends('layouts.admin')

@section('title', 'Edit Post SEO')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <h1 class="text-2xl font-bold">Edit SEO: {{ $post->title }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.seo.update-post', $post->id) }}" class="p-6">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold mb-4">Meta Tags</h2>
                <div class="space-y-4">
                    <div>
                        <label for="meta_title" class="block text-sm font-medium text-neutral-700 mb-1">
                            Meta Title
                        </label>
                        <input type="text" 
                               id="meta_title" 
                               name="meta_title" 
                               value="{{ old('meta_title', $post->meta_title) }}"
                               maxlength="60"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <p class="mt-1 text-sm text-neutral-500">
                            <span id="meta_title_count">0</span>/60 characters (recommended: 50-60)
                        </p>
                    </div>

                    <div>
                        <label for="meta_description" class="block text-sm font-medium text-neutral-700 mb-1">
                            Meta Description
                        </label>
                        <textarea id="meta_description" 
                                  name="meta_description" 
                                  rows="3"
                                  maxlength="160"
                                  class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">{{ old('meta_description', $post->meta_description) }}</textarea>
                        <p class="mt-1 text-sm text-neutral-500">
                            <span id="meta_description_count">0</span>/160 characters (recommended: 150-160)
                        </p>
                    </div>

                    <div>
                        <label for="meta_keywords" class="block text-sm font-medium text-neutral-700 mb-1">
                            Meta Keywords
                        </label>
                        <input type="text" 
                               id="meta_keywords" 
                               name="meta_keywords" 
                               value="{{ old('meta_keywords', $post->meta_keywords) }}"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <p class="mt-1 text-sm text-neutral-500">Comma-separated keywords</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-neutral-200">
                <a href="{{ route('admin.seo.index') }}" 
                   class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Update SEO
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
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
