@extends('layouts.app')

@section('title', $seo['title'] ?? 'The Strengths Toolbox - Build Strong Teams, Unlock Strong Profits')

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')
    <!-- Hero Section -->
    @include('partials.home.hero-section')

    <!-- Power of Strengths -->
    @include('partials.home.power-of-strengths')

    <!-- Three Pillars -->
    @include('partials.home.three-pillars')

    <!-- Why Strong Teams Fail -->
    @include('partials.home.why-teams-fail')

    <!-- Why The Strengths Toolbox -->
    @include('partials.home.why-us')

    <!-- Results You Can Expect -->
    @include('partials.home.results')

    <!-- What You'll Experience -->
    @include('partials.home.experience')

    <!-- How it Works -->
    @include('partials.home.how-it-works')

    <!-- eBook Sign-up -->
    @include('components.ebook-signup-section')

    <!-- Testimonials -->
    @include('partials.home.testimonials', ['testimonials' => $testimonials ?? collect()])
@endsection

@push('schema')
    @php
        $pageData = (object) [
            'title' => $seo['title'] ?? 'The Strengths Toolbox - Build Strong Teams, Unlock Strong Profits',
            'meta_description' => $seo['description'] ?? '',
            'content' => '',
            'slug' => '',
            'published_at' => now(),
            'updated_at' => now(),
        ];
    @endphp
    <x-structured-data type="webpage" :data="$pageData" />
@endpush
