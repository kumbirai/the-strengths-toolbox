@php
    $seo = $seo ?? app(\App\Services\SEOService::class)->getDefaultMeta();
@endphp

<title>{{ $seo['title'] ?? config('app.name') }}</title>

{{-- Favicons --}}
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

{{-- Basic Meta Tags --}}
<meta name="description" content="{{ $seo['description'] ?? '' }}">
@if(!empty($seo['keywords']))
<meta name="keywords" content="{{ $seo['keywords'] }}">
@endif
<link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">

{{-- Robots Meta (for search results, etc.) --}}
@if(isset($seo['robots']))
<meta name="robots" content="{{ $seo['robots'] }}">
@endif

{{-- Open Graph Tags --}}
<meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? '' }}">
<meta property="og:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? '' }}">
<meta property="og:image" content="{{ $seo['og_image'] ?? '' }}">
<meta property="og:url" content="{{ $seo['og_url'] ?? $seo['canonical'] ?? url()->current() }}">
<meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
<meta property="og:site_name" content="{{ config('app.name') }}">

{{-- Article-specific Open Graph tags --}}
@if(isset($seo['article_published_time']))
<meta property="article:published_time" content="{{ $seo['article_published_time'] }}">
@endif
@if(isset($seo['article_modified_time']))
<meta property="article:modified_time" content="{{ $seo['article_modified_time'] }}">
@endif
@if(isset($seo['article_author']))
<meta property="article:author" content="{{ $seo['article_author'] }}">
@endif
@if(isset($seo['article_section']))
<meta property="article:section" content="{{ $seo['article_section'] }}">
@endif
@if(isset($seo['article_tags']))
    @foreach($seo['article_tags'] as $tag)
    <meta property="article:tag" content="{{ $tag }}">
    @endforeach
@endif

{{-- Twitter Card Tags --}}
<meta name="twitter:card" content="{{ $seo['twitter_card'] ?? 'summary_large_image' }}">
<meta name="twitter:title" content="{{ $seo['twitter_title'] ?? $seo['title'] ?? '' }}">
<meta name="twitter:description" content="{{ $seo['twitter_description'] ?? $seo['description'] ?? '' }}">
<meta name="twitter:image" content="{{ $seo['twitter_image'] ?? $seo['og_image'] ?? '' }}">

{{-- Schema Markup --}}
@if(isset($seo['schema']))
<script type="application/ld+json">
{!! json_encode($seo['schema'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif
