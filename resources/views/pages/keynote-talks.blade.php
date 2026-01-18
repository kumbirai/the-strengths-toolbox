@extends('layouts.app')

@section('title', $seo['title'] ?? 'Keynote Talks - The Strengths Toolbox')

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')
    <section class="section-padding bg-gradient-to-br from-primary-600 to-primary-800 text-white">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
                    Keynote Talks
                </h1>
                <p class="text-xl text-primary-100 mb-8">
                    Inspire your audience with engaging presentations on strengths-based development
                </p>
            </div>
        </div>
    </section>

    <section class="section-padding bg-white">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto">
                <div class="prose prose-lg max-w-none mb-12">
                    <p class="text-xl text-gray-700">
                        Eberhard Niklaus brings decades of experience and real-world insights to 
                        your events. His keynote talks combine practical wisdom with engaging 
                        storytelling to inspire audiences and drive action.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                    @php
                        $topics = [
                            [
                                'title' => 'The Power of Strengths',
                                'description' => 'Discover how identifying and leveraging natural talents transforms individual and team performance.'
                            ],
                            [
                                'title' => 'Building High-Performance Teams',
                                'description' => 'Learn the frameworks for creating cohesive teams that achieve exceptional results.'
                            ],
                            [
                                'title' => 'Strengths-Based Leadership',
                                'description' => 'Explore how authentic leadership emerges when leaders understand and use their strengths.'
                            ],
                            [
                                'title' => 'Driving Business Growth',
                                'description' => 'Understand how strengths-based development creates sustainable competitive advantage.'
                            ]
                        ];
                    @endphp

                    @foreach($topics as $topic)
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $topic['title'] }}</h3>
                            <p class="text-gray-600">{{ $topic['description'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="bg-primary-50 border-l-4 border-primary-600 p-6 rounded-r-lg mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Customized Presentations</h3>
                    <p class="text-gray-700">
                        All keynote talks can be customized to fit your audience, industry, and event 
                        objectives. Contact us to discuss your specific needs and how we can create a 
                        presentation that resonates with your audience.
                    </p>
                </div>

                <div class="text-center">
                    <a href="{{ route('contact') }}?source=keynote-talks" class="btn btn-primary text-lg px-8 py-4">
                        Book a Keynote Talk
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
