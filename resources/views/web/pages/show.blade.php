@extends('layouts.app')

@section('title', $seo['title'] ?? $page->title)

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')
    <x-content-page :page="$page" />
@endsection

@push('schema')
    <x-structured-data type="webpage" :data="$page" />
    
    @php
        $breadcrumbs = \App\Helpers\BreadcrumbHelper::generate($page->title, url('/' . $page->slug));
    @endphp
    <x-structured-data type="breadcrumb" :data="$breadcrumbs" />
@endpush
