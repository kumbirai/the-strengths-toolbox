@extends('layouts.app')

@section('title', $seo['title'] ?? $category->name . ' - Blog - The Strengths Toolbox')

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')
    <section class="bg-gradient-to-br from-primary-600 to-primary-800 text-white py-16">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    {{ $category->name }}
                </h1>
                @if($category->description)
                    <p class="text-xl text-primary-100">{{ $category->description }}</p>
                @endif
            </div>
        </div>
    </section>

    <section class="section-padding bg-white">
        <div class="container-custom">
            <!-- Breadcrumbs -->
            <nav class="mb-8" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li><a href="{{ route('home') }}" class="text-primary-600 hover:text-primary-700">Home</a></li>
                    <li class="text-gray-500">/</li>
                    <li><a href="{{ route('blog.index') }}" class="text-primary-600 hover:text-primary-700">Blog</a></li>
                    <li class="text-gray-500">/</li>
                    <li class="text-gray-700">{{ $category->name }}</li>
                </ol>
            </nav>

            @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                    @foreach($posts as $post)
                        @include('blog.partials.post-card', ['post' => $post])
                    @endforeach
                </div>

                {{ $posts->links() }}
            @else
                <div class="text-center py-12">
                    <p class="text-gray-600 mb-4">No posts found in this category.</p>
                    <a href="{{ route('blog.index') }}" class="btn btn-primary">View All Posts</a>
                </div>
            @endif
        </div>
    </section>
@endsection
