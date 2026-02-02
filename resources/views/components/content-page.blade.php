@props(['page', 'showBreadcrumbs' => true, 'pageImage' => null])

{{-- Hero Section --}}
<section class="relative section-gradient-primary overflow-hidden">
    {{-- Background Pattern --}}
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V4h4V2h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>

    <div class="container-custom section-padding relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            {{-- Breadcrumb --}}
            @if($showBreadcrumbs)
                <nav class="mb-6" aria-label="Breadcrumb">
                    <ol class="flex items-center justify-center space-x-2 text-sm text-primary-200">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                        <li>/</li>
                        <li class="text-white">{{ $page->title }}</li>
                    </ol>
                </nav>
            @endif

            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                {{ $page->title }}
            </h1>

            @if($page->excerpt)
                <p class="text-xl md:text-2xl text-primary-100 max-w-3xl mx-auto">
                    {{ $page->excerpt }}
                </p>
            @endif

            @if($pageImage)
                <div class="mt-8 max-w-2xl mx-auto">
                    <img
                        src="{{ $pageImage->url }}"
                        alt="{{ $pageImage->alt_text ?? $page->title }}"
                        class="w-full rounded-xl shadow-lg object-cover aspect-video"
                        loading="eager"
                    >
                </div>
            @endif
        </div>
    </div>
</section>

{{-- Main Content Section --}}
<section class="section-padding section-light">
    <div class="container-custom">
        <article class="max-w-4xl mx-auto">
            <div class="prose-content" x-data="{}" x-init="$el.querySelectorAll('img').forEach(img => { img.onerror = function() { this.style.display = 'none'; } })">
                @php
                    $content = $page->content;
                    $bookingUrl = e(route('booking'));
                    $content = str_replace(['href="/booking"', 'href=\'/booking\''], 'href="'.$bookingUrl.'"', $content);
                @endphp
                {!! $content !!}
            </div>

            @if(isset($page->cta_text) && isset($page->cta_link) && $page->cta_text && $page->cta_link)
                <div class="mt-12 text-center">
                    <a href="{{ $page->cta_link }}" class="btn btn-primary text-lg">
                        {{ $page->cta_text }}
                    </a>
                </div>
            @endif
        </article>
    </div>
</section>

{{-- CTA Section --}}
<section class="section-padding section-muted">
    <div class="container-custom">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="section-title">Ready to Transform Your Team?</h2>
            <p class="section-subtitle mb-8">
                Discover how strengths-based development can help your organization achieve exceptional results.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('contact') }}" class="btn btn-primary text-lg">
                    Book a Free Consultation
                </a>
                <a href="{{ route('strengths-programme') }}" class="btn btn-secondary text-lg">
                    Explore Our Programmes
                </a>
            </div>
        </div>
    </div>
</section>
