@extends('layouts.app')

@section('title', $seo['title'] ?? 'Strengths Programme - The Strengths Toolbox')

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')
    @include('partials.pages.strengths-programme.hero')
    @include('partials.pages.strengths-programme.what-strengths-matter')
    @include('partials.pages.strengths-programme.four-programs')
    @include('partials.pages.strengths-programme.cta')
    @include('partials.pages.strengths-programme.faq')
@endsection

@push('schema')
    @php
        $pageData = (object) [
            'title' => $seo['title'] ?? 'Strengths Programme - The Strengths Toolbox',
            'meta_description' => $seo['description'] ?? '',
            'content' => '',
            'slug' => 'strengths-programme',
            'published_at' => now(),
            'updated_at' => now(),
        ];
    @endphp
    <x-structured-data type="webpage" :data="$pageData" />
@endpush
