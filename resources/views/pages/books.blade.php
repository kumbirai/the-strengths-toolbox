@extends('layouts.app')

@section('title', $seo['title'] ?? 'Books - The Strengths Toolbox')

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')
    <section class="section-padding bg-white">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        Books & Resources
                    </h1>
                    <p class="text-xl text-gray-600">
                        Expand your knowledge with our recommended reading
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @php
                        $books = [
                            [
                                'title' => 'Free Sales Book',
                                'description' => 'Download our free eBook on sales strategies and strengths-based selling.',
                                'type' => 'eBook',
                                'cta' => 'Download Free',
                                'link' => route('home') . '#ebook-signup'
                            ],
                            // Add more books as needed
                        ];
                    @endphp

                    @foreach($books as $book)
                        <div class="bg-gray-50 rounded-lg p-6 hover:shadow-lg transition-shadow">
                            <div class="aspect-[3/4] bg-white rounded-lg mb-4 overflow-hidden">
                                @if(isset($ebookImage) && $ebookImage && $book['title'] === 'Free Sales Book')
                                    <img 
                                        src="{{ $ebookImage->url }}" 
                                        alt="{{ $ebookImage->alt_text ?? 'Free Sales Book eBook Cover' }}"
                                        class="w-full h-full object-cover"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="text-sm text-primary-600 font-semibold mb-2">{{ $book['type'] }}</div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $book['title'] }}</h3>
                            <p class="text-gray-600 mb-4">{{ $book['description'] }}</p>
                            <a href="{{ $book['link'] }}" class="btn btn-primary w-full">
                                {{ $book['cta'] }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
