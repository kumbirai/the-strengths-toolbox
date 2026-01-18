@extends('layouts.app')

@section('title', $seo['title'] ?? 'Contact Us - The Strengths Toolbox')

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
                    Get In Touch
                </h1>
                <p class="text-xl md:text-2xl text-primary-100 max-w-3xl mx-auto">
                    Ready to transform your team? Let's start the conversation.
                </p>
            </div>
        </div>
    </section>

    {{-- Contact Information --}}
    <section class="section-padding section-light">
        <div class="container-custom">
            <div class="grid-3 grid-constrained mb-12">
                {{-- Email --}}
                <div class="feature-card-centered">
                    <div class="icon-badge-xl icon-badge-primary mx-auto mb-6">
                        <svg class="icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Email</h3>
                    <a href="mailto:welcome@eberhardniklaus.co.za" class="text-primary-600 hover:text-primary-700 transition-colors">
                        welcome@eberhardniklaus.co.za
                    </a>
                </div>

                {{-- Phone --}}
                <div class="feature-card-centered">
                    <div class="icon-badge-xl icon-badge-primary mx-auto mb-6">
                        <svg class="icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Phone</h3>
                    <a href="tel:+27832948033" class="text-primary-600 hover:text-primary-700 transition-colors">
                        +27 83 294 8033
                    </a>
                </div>

                {{-- Location --}}
                <div class="feature-card-centered">
                    <div class="icon-badge-xl icon-badge-primary mx-auto mb-6">
                        <svg class="icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Location</h3>
                    <p class="feature-description">South Africa</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact Form Section --}}
    <section
        class="section-padding section-muted"
        x-data="{ showEbook: false }"
        @contact-form-success.window="showEbook = true"
    >
        <div class="container-custom">
            {{-- Contact Form --}}
            <div
                x-show="!showEbook"
                x-transition:leave="transition ease-out duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <div class="max-w-3xl mx-auto">
                    <div class="section-header">
                        <h2 class="section-title">Send Us a Message</h2>
                        <p class="section-subtitle">
                            Have a question? We'd love to hear from you.
                        </p>
                    </div>

                    <div class="card p-8">
                        @include('partials.contact.form')
                    </div>
                </div>
            </div>

            {{-- eBook Section --}}
            <div
                x-show="showEbook"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                @include('components.ebook-signup-section')
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="section-padding section-gradient-primary">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="section-title-light">Ready to Get Started?</h2>
                <p class="section-subtitle-light mb-8">
                    Book your complimentary 30-minute consultation today
                </p>
                <a href="{{ route('booking') }}" class="btn btn-secondary text-lg px-8 py-4">
                    Book Your Free Consultation
                </a>
            </div>
        </div>
    </section>
@endsection

@push('schema')
    @php
        $pageData = (object) [
            'title' => $seo['title'] ?? 'Contact Us - The Strengths Toolbox',
            'meta_description' => $seo['description'] ?? '',
            'content' => '',
            'slug' => 'contact',
            'published_at' => now(),
            'updated_at' => now(),
        ];
    @endphp
    <x-structured-data type="webpage" :data="$pageData" />
@endpush
