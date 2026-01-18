@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title)

@section('content')
<div class="container-custom section-padding">
    <article>
        <h1 class="text-4xl font-bold mb-6">{{ $page->title }}</h1>
        
        @if($page->excerpt)
            <p class="text-xl text-gray-600 mb-6">{{ $page->excerpt }}</p>
        @endif
        
        <div class="prose max-w-none">
            {!! $page->content !!}
        </div>
    </article>
</div>
@endsection
