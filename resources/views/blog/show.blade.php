@extends('layouts.app')

@section('title', $seo['title'] ?? $post->title)

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')
    {{-- Hero Section --}}
    <section class="relative section-gradient-primary overflow-hidden">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V4h4V2h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <div class="container-custom section-padding relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                {{-- Breadcrumb --}}
                <nav class="mb-6" aria-label="Breadcrumb">
                    <ol class="flex items-center justify-center flex-wrap gap-2 text-sm text-primary-200">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                        <li>/</li>
                        <li><a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">Blog</a></li>
                        <li>/</li>
                        <li class="text-white">{{ Str::limit($post->title, 40) }}</li>
                    </ol>
                </nav>

                {{-- Category --}}
                @if($post->categories->count() > 0)
                    <div class="mb-4">
                        <a
                            href="{{ route('blog.category', $post->categories->first()->slug) }}"
                            class="badge bg-white/20 text-white hover:bg-white/30 transition-colors"
                        >
                            {{ $post->categories->first()->name }}
                        </a>
                    </div>
                @endif

                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-6 leading-tight">
                    {{ $post->title }}
                </h1>

                {{-- Meta --}}
                <div class="flex flex-wrap items-center justify-center gap-4 text-sm text-primary-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $post->published_at->format('F d, Y') }}</span>
                    </div>
                    @if($post->author)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>By {{ $post->author->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Featured Image --}}
    @if($post->featured_image)
        <div class="container-custom -mt-8 relative z-20">
            <div class="max-w-4xl mx-auto">
                <img
                    src="{{ asset('storage/' . $post->featured_image) }}"
                    alt="{{ $post->title }}"
                    class="w-full aspect-video object-cover rounded-xl shadow-xl"
                >
            </div>
        </div>
    @endif

    {{-- Content Section --}}
    <section class="section-padding section-light">
        <div class="container-custom">
            <article class="max-w-4xl mx-auto">
                {{-- Tags --}}
                @if($post->tags->count() > 0)
                    <div class="flex flex-wrap gap-2 mb-8">
                        @foreach($post->tags as $tag)
                            <a
                                href="{{ route('blog.tag', $tag->slug) }}"
                                class="badge badge-primary"
                            >
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Content --}}
                <div class="prose-content mb-12">
                    {!! $post->content !!}
                </div>

                {{-- Share Buttons --}}
                <div class="border-t border-b border-neutral-200 py-6 mb-12">
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="font-semibold text-neutral-700">Share:</span>
                        <a
                            href="https://twitter.com/intent/tweet?url={{ urlencode(url('/blog/' . $post->slug)) }}&text={{ urlencode($post->title) }}"
                            target="_blank"
                            class="text-neutral-600 hover:text-primary-600 transition-colors"
                        >
                            Twitter
                        </a>
                        <a
                            href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url('/blog/' . $post->slug)) }}"
                            target="_blank"
                            class="text-neutral-600 hover:text-primary-600 transition-colors"
                        >
                            LinkedIn
                        </a>
                        <a
                            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/blog/' . $post->slug)) }}"
                            target="_blank"
                            class="text-neutral-600 hover:text-primary-600 transition-colors"
                        >
                            Facebook
                        </a>
                    </div>
                </div>

                {{-- Back to Blog --}}
                <div class="text-center">
                    <a href="{{ route('blog.index') }}" class="btn btn-secondary">
                        ← Back to Blog
                    </a>
                </div>
            </article>
        </div>
    </section>

    {{-- Related Posts --}}
    @if($relatedPosts && $relatedPosts->count() > 0)
        <section class="section-padding section-muted">
            <div class="container-custom">
                <div class="section-header">
                    <h2 class="section-title">Related Posts</h2>
                    <p class="section-subtitle">
                        Continue exploring insights on strengths-based development
                    </p>
                </div>

                <div class="grid-3 grid-constrained">
                    @foreach($relatedPosts->take(3) as $relatedPost)
                        @include('blog.partials.post-card', ['post' => $relatedPost])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA Section --}}
    <section class="section-padding section-gradient-primary">
        <div class="container-custom">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="section-title-light">Ready to Transform Your Team?</h2>
                <p class="section-subtitle-light mb-8">
                    Discover how strengths-based development can help your organization achieve exceptional results.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('contact') }}" class="btn btn-secondary text-lg">
                        Book a Free Consultation
                    </a>
                    <a href="{{ route('strengths-programme') }}" class="btn btn-outline border-white text-white hover:bg-white hover:text-primary-700 text-lg">
                        Explore Our Programmes
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('schema')
    <x-structured-data type="article" :data="$post" />

    @php
        $category = $post->categories->first();
        $breadcrumbs = \App\Helpers\BreadcrumbHelper::generateForPost(
            $post->title,
            url('/blog/' . $post->slug),
            $category?->name,
            $category ? route('blog.category', $category->slug) : null
        );
    @endphp
    <x-structured-data type="breadcrumb" :data="$breadcrumbs" />
@endpush
