@extends('layouts.admin')

@section('title', 'Edit Page SEO')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <h1 class="text-2xl font-bold">Edit SEO: {{ $page->title }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.seo.update-page', $page->id) }}" class="p-6">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            {{-- Basic Meta Tags --}}
            <div>
                <h2 class="text-lg font-semibold mb-4">Basic Meta Tags</h2>
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
                                  class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">{{ old('meta_description', $page->meta_description) }}</textarea>
                        <p class="mt-1 text-sm text-neutral-500">
                            <span id="meta_description_count">0</span>/160 characters (recommended: 150-160)
                        </p>
                    </div>
                </div>
            </div>

            {{-- Open Graph Tags --}}
            <div>
                <h2 class="text-lg font-semibold mb-4">Open Graph Tags</h2>
                <div class="space-y-4">
                    <div>
                        <label for="og_title" class="block text-sm font-medium text-neutral-700 mb-1">
                            OG Title
                        </label>
                        <input type="text" 
                               id="og_title" 
                               name="og_title" 
                               value="{{ old('og_title', $seo->og_title) }}"
                               maxlength="60"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="og_description" class="block text-sm font-medium text-neutral-700 mb-1">
                            OG Description
                        </label>
                        <textarea id="og_description" 
                                  name="og_description" 
                                  rows="3"
                                  maxlength="200"
                                  class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">{{ old('og_description', $seo->og_description) }}</textarea>
                    </div>

                    <div>
                        <label for="og_image" class="block text-sm font-medium text-neutral-700 mb-1">
                            OG Image URL
                        </label>
                        <input type="url" 
                               id="og_image" 
                               name="og_image" 
                               value="{{ old('og_image', $seo->og_image) }}"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <p class="mt-1 text-sm text-neutral-500">Recommended: 1200×630px</p>
                    </div>
                </div>

                {{-- OG Preview --}}
                <div class="mt-4 p-4 bg-neutral-50 rounded-lg">
                    <h3 class="text-sm font-medium text-neutral-700 mb-2">Preview</h3>
                    <div id="ogPreview" class="border border-neutral-300 rounded-lg overflow-hidden bg-white max-w-md">
                        <div id="ogPreviewImage" class="w-full h-48 bg-neutral-200 flex items-center justify-center">
                            <span class="text-neutral-400 text-sm">No image</span>
                        </div>
                        <div class="p-3">
                            <div id="ogPreviewTitle" class="font-semibold text-neutral-900 mb-1"></div>
                            <div id="ogPreviewDescription" class="text-sm text-neutral-600"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Twitter Card Tags --}}
            <div>
                <h2 class="text-lg font-semibold mb-4">Twitter Card Tags</h2>
                <div class="space-y-4">
                    <div>
                        <label for="twitter_card" class="block text-sm font-medium text-neutral-700 mb-1">
                            Card Type
                        </label>
                        <select id="twitter_card" 
                                name="twitter_card"
                                class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="summary" {{ old('twitter_card', $seo->twitter_card) == 'summary' ? 'selected' : '' }}>Summary</option>
                            <option value="summary_large_image" {{ old('twitter_card', $seo->twitter_card) == 'summary_large_image' ? 'selected' : '' }}>Summary with Large Image</option>
                        </select>
                    </div>

                    <div>
                        <label for="twitter_title" class="block text-sm font-medium text-neutral-700 mb-1">
                            Twitter Title
                        </label>
                        <input type="text" 
                               id="twitter_title" 
                               name="twitter_title" 
                               value="{{ old('twitter_title', $seo->twitter_title) }}"
                               maxlength="70"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="twitter_description" class="block text-sm font-medium text-neutral-700 mb-1">
                            Twitter Description
                        </label>
                        <textarea id="twitter_description" 
                                  name="twitter_description" 
                                  rows="3"
                                  maxlength="200"
                                  class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">{{ old('twitter_description', $seo->twitter_description) }}</textarea>
                    </div>

                    <div>
                        <label for="twitter_image" class="block text-sm font-medium text-neutral-700 mb-1">
                            Twitter Image URL
                        </label>
                        <input type="url" 
                               id="twitter_image" 
                               name="twitter_image" 
                               value="{{ old('twitter_image', $seo->twitter_image) }}"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <p class="mt-1 text-sm text-neutral-500">Recommended: 1200×675px for large image cards</p>
                    </div>
                </div>

                {{-- Twitter Card Preview --}}
                <div class="mt-4 p-4 bg-neutral-50 rounded-lg">
                    <h3 class="text-sm font-medium text-neutral-700 mb-2">Preview</h3>
                    <div id="twitterPreview" class="border border-neutral-300 rounded-lg overflow-hidden bg-white max-w-md">
                        <div id="twitterPreviewImage" class="w-full bg-neutral-200 flex items-center justify-center" style="height: 0; padding-bottom: 52.5%;">
                            <span class="text-neutral-400 text-sm absolute">No image</span>
                        </div>
                        <div class="p-3">
                            <div id="twitterPreviewTitle" class="font-semibold text-neutral-900 mb-1"></div>
                            <div id="twitterPreviewDescription" class="text-sm text-neutral-600"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Schema Markup --}}
            <div>
                <h2 class="text-lg font-semibold mb-4">Schema Markup</h2>
                <div>
                    <label for="schema_markup" class="block text-sm font-medium text-neutral-700 mb-1">
                        JSON-LD Schema
                    </label>
                    <textarea id="schema_markup" 
                              name="schema_markup" 
                              rows="10"
                              class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent font-mono text-sm">{{ old('schema_markup', $seo->schema_markup ? json_encode($seo->schema_markup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                    <p class="mt-1 text-sm text-neutral-500">Enter valid JSON-LD schema markup</p>
                </div>
            </div>

            {{-- Canonical URL --}}
            <div>
                <label for="canonical_url" class="block text-sm font-medium text-neutral-700 mb-1">
                    Canonical URL
                </label>
                <input type="url" 
                       id="canonical_url" 
                       name="canonical_url" 
                       value="{{ old('canonical_url', $seo->canonical_url) }}"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>

            {{-- Form Actions --}}
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

// OG Preview updates
function updateOGPreview() {
    const title = document.getElementById('og_title').value || document.getElementById('meta_title').value || 'Page Title';
    const description = document.getElementById('og_description').value || document.getElementById('meta_description').value || 'Page description';
    const image = document.getElementById('og_image').value;
    
    document.getElementById('ogPreviewTitle').textContent = title;
    document.getElementById('ogPreviewDescription').textContent = description;
    
    if (image) {
        document.getElementById('ogPreviewImage').innerHTML = `<img src="${image}" alt="${title}" class="w-full h-full object-cover">`;
    } else {
        document.getElementById('ogPreviewImage').innerHTML = '<span class="text-neutral-400 text-sm">No image</span>';
    }
}

['og_title', 'og_description', 'og_image', 'meta_title', 'meta_description'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateOGPreview);
});

// Twitter Preview updates
function updateTwitterPreview() {
    const cardType = document.getElementById('twitter_card').value;
    const title = document.getElementById('twitter_title').value || document.getElementById('meta_title').value || 'Page Title';
    const description = document.getElementById('twitter_description').value || document.getElementById('meta_description').value || 'Page description';
    const image = document.getElementById('twitter_image').value || document.getElementById('og_image').value;
    
    document.getElementById('twitterPreviewTitle').textContent = title;
    document.getElementById('twitterPreviewDescription').textContent = description;
    
    const imageContainer = document.getElementById('twitterPreviewImage');
    if (cardType === 'summary_large_image' && image) {
        imageContainer.style.paddingBottom = '52.5%';
        imageContainer.innerHTML = `<img src="${image}" alt="${title}" class="absolute inset-0 w-full h-full object-cover">`;
    } else {
        imageContainer.style.paddingBottom = '0';
        imageContainer.innerHTML = '<span class="text-neutral-400 text-sm">No image</span>';
    }
}

['twitter_card', 'twitter_title', 'twitter_description', 'twitter_image', 'og_image', 'meta_title', 'meta_description'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateTwitterPreview);
    document.getElementById(id).addEventListener('change', updateTwitterPreview);
});

// Initialize previews
updateOGPreview();
updateTwitterPreview();
</script>
@endpush
@endsection
