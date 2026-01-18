@extends('layouts.app')

@section('title', $seo['title'] ?? 'Blog - The Strengths Toolbox')

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
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    Our Blog
                </h1>
                <p class="text-xl md:text-2xl text-primary-100 max-w-3xl mx-auto">
                    Insights on strengths-based development, team building, and business growth
                </p>
            </div>
        </div>
    </section>

    {{-- Blog Posts --}}
    <section class="section-padding section-light">
        <div class="container-custom">
            {{-- Search and Filters --}}
            <div class="max-w-2xl mx-auto mb-12">
                <form action="{{ route('blog.search') }}" method="GET" class="flex gap-4">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search blog posts..."
                        class="form-input flex-1"
                    >
                    <button type="submit" class="btn btn-primary">
                        Search
                    </button>
                </form>
            </div>

            @if($posts->count() > 0)
                {{-- Posts Grid --}}
                <div class="grid-3 grid-constrained">
                    @foreach($posts as $post)
                        @include('blog.partials.post-card', ['post' => $post])
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-12 flex justify-center">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="icon-badge-xl icon-badge-primary mx-auto mb-6">
                        <svg class="icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <p class="text-neutral-600 text-lg mb-6">No blog posts found.</p>
                    <a href="{{ route('blog.index') }}" class="btn btn-primary">
                        View All Posts
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="section-padding section-muted">
        <div class="container-custom">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="section-title">Want to Learn More?</h2>
                <p class="section-subtitle mb-8">
                    Discover how strengths-based development can transform your team and drive business growth.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('contact') }}" class="btn btn-primary text-lg">
                        Get in Touch
                    </a>
                    <a href="{{ route('strengths-programme') }}" class="btn btn-secondary text-lg">
                        Explore Our Programmes
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
