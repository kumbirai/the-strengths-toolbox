@extends('layouts.app')

@section('title', $seo['title'] ?? 'About Us - The Strengths Toolbox')

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')
    @include('partials.pages.about.hero')
    @include('partials.pages.about.what-we-do')
    @include('partials.pages.about.our-story')
    @include('partials.pages.about.why-choose-us')
    @include('partials.pages.about.track-record')
    @include('partials.pages.about.community-cta')
@endsection

@push('schema')
    @php
        $pageData = (object) [
            'title' => $seo['title'] ?? 'About Us - The Strengths Toolbox',
            'meta_description' => $seo['description'] ?? '',
            'content' => '',
            'slug' => 'about-us',
            'published_at' => now(),
            'updated_at' => now(),
        ];
    @endphp
    <x-structured-data type="webpage" :data="$pageData" />
@endpush
