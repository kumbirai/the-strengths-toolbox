@extends('layouts.app')

@section('title', $seo['title'] ?? 'Search Results' . ($query ? ' for "' . $query . '"' : '') . ' - The Strengths Toolbox')

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')
    <section class="bg-gradient-to-br from-primary-600 to-primary-800 text-white py-16">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    Search Results
                </h1>
                @if($query)
                    <p class="text-xl text-primary-100">
                        Results for "{{ $query }}"
                    </p>
                @endif
            </div>
        </div>
    </section>

    <section class="section-padding bg-white">
        <div class="container-custom">
            <!-- Search Form -->
            <form action="{{ route('blog.search') }}" method="GET" class="mb-8 flex gap-4">
                <input 
                    type="text" 
                    name="q" 
                    value="{{ $query }}"
                    placeholder="Search blog posts..."
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                >
                <button type="submit" class="btn btn-primary px-8">
                    Search
                </button>
            </form>

            @if($posts->count() > 0)
                <p class="text-gray-600 mb-6">
                    Found {{ $posts->total() }} result(s) for "{{ $query }}"
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                    @foreach($posts as $post)
                        @include('blog.partials.post-card', ['post' => $post])
                    @endforeach
                </div>

                {{ $posts->appends(['q' => $query])->links() }}
            @else
                <div class="text-center py-12">
                    <p class="text-gray-600 mb-4">No results found for "{{ $query }}".</p>
                    <a href="{{ route('blog.index') }}" class="btn btn-primary">View All Posts</a>
                </div>
            @endif
        </div>
    </section>
@endsection
