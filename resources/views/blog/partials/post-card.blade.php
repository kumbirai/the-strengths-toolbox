<article class="card-elevated overflow-hidden">
    @if($post->featured_image)
        <a href="{{ route('blog.show', $post->slug) }}" class="block">
            <img
                src="{{ $post->featured_image_url }}"
                alt="{{ $post->title }}"
                class="w-full h-48 object-cover"
                loading="lazy"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            >
            <div class="w-full h-48 bg-gradient-to-br from-primary-100 to-accent-100 items-center justify-center" style="display: none;">
                <svg class="w-16 h-16 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
        </a>
    @else
        <div class="w-full h-48 bg-gradient-to-br from-primary-100 to-accent-100 flex items-center justify-center">
            <svg class="w-16 h-16 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
    @endif

    <div class="p-6">
        @if($post->categories->count() > 0)
            <div class="mb-3">
                <a
                    href="{{ route('blog.category', $post->categories->first()->slug) }}"
                    class="badge badge-primary"
                >
                    {{ $post->categories->first()->name }}
                </a>
            </div>
        @endif

        <h2 class="feature-title mb-3">
            <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-primary-600 transition-colors">
                {{ $post->title }}
            </a>
        </h2>

        @if($post->excerpt)
            <p class="feature-description mb-4 line-clamp-3">{{ $post->excerpt }}</p>
        @endif

        <div class="flex items-center justify-between text-sm text-neutral-500 pt-4 border-t border-neutral-100">
            <span>{{ $post->published_at->format('M d, Y') }}</span>
            <a
                href="{{ route('blog.show', $post->slug) }}"
                class="text-primary-600 font-semibold hover:text-primary-700 transition-colors"
            >
                Read More →
            </a>
        </div>
    </div>
</article>
