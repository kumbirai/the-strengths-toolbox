@extends('layouts.app')

@section('title', $seo['title'] ?? 'Book a Consultation - The Strengths Toolbox')

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
                    Book Your Free Consultation
                </h1>
                <p class="text-xl md:text-2xl text-primary-100 max-w-3xl mx-auto">
                    Schedule a 30-minute complimentary consultation to discuss how
                    strengths-based development can transform your business.
                </p>
            </div>
        </div>
    </section>

    {{-- Benefits Section --}}
    <section class="section-padding section-light">
        <div class="container-custom">
            <div class="section-header">
                <h2 class="section-title">What to Expect</h2>
            </div>

            <div class="grid-2 grid-constrained">
                @php
                    $benefits = [
                        [
                            'title' => 'No Obligation',
                            'description' => 'This is a free consultation with no commitment required.',
                            'icon' => 'check'
                        ],
                        [
                            'title' => 'Personalized Discussion',
                            'description' => 'We\'ll discuss your specific challenges and goals.',
                            'icon' => 'chat'
                        ],
                        [
                            'title' => 'Expert Insights',
                            'description' => 'Get insights from 30+ years of experience.',
                            'icon' => 'lightbulb'
                        ],
                        [
                            'title' => 'Clear Next Steps',
                            'description' => 'Leave with a clear understanding of how we can help.',
                            'icon' => 'arrow'
                        ]
                    ];
                @endphp

                @foreach($benefits as $benefit)
                    <div class="feature-card">
                        <div class="flex gap-4">
                            <div class="icon-badge icon-badge-primary flex-shrink-0">
                                @if($benefit['icon'] === 'check')
                                    <svg class="icon-md" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($benefit['icon'] === 'chat')
                                    <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                @elseif($benefit['icon'] === 'lightbulb')
                                    <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                @elseif($benefit['icon'] === 'arrow')
                                    <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <h3 class="feature-title">{{ $benefit['title'] }}</h3>
                                <p class="feature-description">{{ $benefit['description'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Calendly Widget --}}
    <section class="section-padding section-muted">
        <div class="container-custom">
            <div class="max-w-5xl mx-auto">
                <div class="card p-4 lg:p-8">
                    <x-calendly-widget :url="config('services.calendly.url')" height="800px" />
                </div>
            </div>
        </div>
    </section>

    {{-- Alternative Contact --}}
    <section class="section-padding section-light">
        <div class="container-custom">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="section-title">Prefer to Contact Us Directly?</h2>
                <p class="section-subtitle mb-8">
                    If you'd rather reach out via email or phone, we're here to help.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="mailto:{{ config('mail.from.address') }}" class="btn btn-secondary">
                        Send Email
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-primary">
                        Contact Form
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
