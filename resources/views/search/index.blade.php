@extends('layouts.app')

@section('title', $seo['title'] ?? 'Search - ' . config('app.name'))

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')
    <section class="section-padding bg-white">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto">
                {{-- Search Form --}}
                <div class="mb-8">
                    <x-search-form placeholder="Search pages and blog posts..." class="max-w-2xl mx-auto" />
                </div>

                {{-- Results Header --}}
                @if(!empty($query))
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            Search Results
                        </h1>
                        <p class="text-gray-600">
                            @if($results['total'] > 0)
                                Found {{ $results['total'] }} result(s) for "<strong>{{ $query }}</strong>"
                            @else
                                No results found for "<strong>{{ $query }}</strong>"
                            @endif
                        </p>
                    </div>
                @endif

                {{-- Results --}}
                @if($results['total'] > 0)
                    {{-- Pages Results --}}
                    @if($results['pages']->isNotEmpty())
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                                Pages ({{ $results['pages']->count() }})
                            </h2>
                            <div class="space-y-4">
                                @foreach($results['pages'] as $page)
                                    <div class="bg-gray-50 rounded-lg p-6 hover:shadow-md transition-shadow">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                            <a href="{{ $page['url'] }}" class="hover:text-primary-600">
                                                {!! $page['title'] !!}
                                            </a>
                                        </h3>
                                        <p class="text-gray-600 mb-2">
                                            {!! $page['excerpt'] !!}
                                        </p>
                                        <a href="{{ $page['url'] }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                                            View Page →
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Blog Posts Results --}}
                    @if($results['posts']->isNotEmpty())
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                                Blog Posts ({{ $results['posts']->count() }})
                            </h2>
                            <div class="space-y-4">
                                @foreach($results['posts'] as $post)
                                    <div class="bg-gray-50 rounded-lg p-6 hover:shadow-md transition-shadow">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                            <a href="{{ $post['url'] }}" class="hover:text-primary-600">
                                                {!! $post['title'] !!}
                                            </a>
                                        </h3>
                                        <p class="text-gray-600 mb-2">
                                            {!! $post['excerpt'] !!}
                                        </p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500">
                                                {{ $post['published_at']->format('F j, Y') }}
                                            </span>
                                            <a href="{{ $post['url'] }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                                                Read More →
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @elseif(!empty($query))
                    {{-- No Results --}}
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No results found</h3>
                        <p class="text-gray-600 mb-6">
                            Try different keywords or check your spelling.
                        </p>
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            Return to Homepage
                        </a>
                    </div>
                @endif

                {{-- Popular Searches --}}
                @if(empty($query) || $results['total'] === 0)
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Searches</h3>
                        <div class="flex flex-wrap gap-2">
                            @php
                            $searchService = app(\App\Services\SearchService::class);
                            @endphp
                            @foreach($searchService->getPopularSearches() as $popular)
                                <a href="{{ route('search', ['q' => $popular]) }}" 
                                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-primary-100 hover:text-primary-700 transition-colors">
                                    {{ $popular }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
