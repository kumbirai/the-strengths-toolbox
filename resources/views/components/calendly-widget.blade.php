@props(['url', 'height' => '700px'])

@php
    $calendlyUrl = $url ?? config('services.calendly.url');
@endphp

@if($calendlyUrl)
    <div 
        class="calendly-inline-widget" 
        data-url="{{ $calendlyUrl }}"
        style="min-width:320px;height:{{ $height }};"
    ></div>
@else
    <div class="bg-gray-100 rounded-lg p-8 text-center">
        <p class="text-gray-600">Calendly widget not configured.</p>
    </div>
@endif
