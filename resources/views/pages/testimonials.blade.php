@extends('layouts.app')

@section('title', $seo['title'] ?? 'Testimonials - The Strengths Toolbox')

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
                    Client Testimonials
                </h1>
                <p class="text-xl md:text-2xl text-primary-100 max-w-3xl mx-auto">
                    Real results from real businesses - hear what our clients have to say about their strengths-based transformation
                </p>
            </div>
        </div>
    </section>

    {{-- Testimonials Grid --}}
    <section class="section-padding section-light">
        <div class="container-custom">
            @php
                $testimonials = \App\Models\Testimonial::published()
                    ->orderBy('display_order', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(12);
            @endphp

            @if($testimonials->count() > 0)
                <div class="grid-3 grid-constrained">
                    @foreach($testimonials as $testimonial)
                        <div class="card-elevated p-6">
                            {{-- Quote Icon --}}
                            <div class="icon-badge icon-badge-accent mb-4">
                                <svg class="icon-md" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                                </svg>
                            </div>

                            {{-- Rating Stars --}}
                            @if($testimonial->rating)
                                <div class="flex items-center gap-1 mb-4">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg
                                            class="w-5 h-5 {{ $i <= $testimonial->rating ? 'text-accent-500' : 'text-neutral-300' }}"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                            @endif

                            {{-- Testimonial Text --}}
                            <blockquote class="feature-description mb-6 italic">
                                "{{ $testimonial->testimonial }}"
                            </blockquote>

                            {{-- Author Info --}}
                            <footer class="flex items-center gap-3 pt-4 border-t border-neutral-100">
                                <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-primary-600 font-bold text-lg">
                                        {{ strtoupper(substr($testimonial->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-semibold text-neutral-900">{{ $testimonial->name }}</p>
                                    @if($testimonial->company)
                                        <p class="text-sm text-neutral-500">{{ $testimonial->company }}</p>
                                    @endif
                                </div>
                            </footer>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-12 flex justify-center">
                    {{ $testimonials->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="icon-badge-xl icon-badge-primary mx-auto mb-6">
                        <svg class="icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <p class="text-neutral-600 text-lg mb-6">No testimonials available at this time.</p>
                    <a href="{{ route('contact') }}" class="btn btn-primary">
                        Get in Touch
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="section-padding section-gradient-primary">
        <div class="container-custom">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="section-title-light">Ready to Start Your Journey?</h2>
                <p class="section-subtitle-light mb-8">
                    Join the many organisations that have transformed their teams through strengths-based development.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('booking') }}" class="btn btn-secondary text-lg">
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
