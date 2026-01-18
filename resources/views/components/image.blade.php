@props([
    'src',
    'alt' => '',
    'width' => null,
    'height' => null,
    'lazy' => true,
    'class' => '',
    'placeholder' => true,
])

@php
    $isAboveFold = $lazy === false;
    $loading = $isAboveFold ? 'eager' : 'lazy';
    $decoding = 'async';
    
    // For fallback, use data-src
    $actualSrc = $isAboveFold || !$lazy ? $src : null;
    $dataSrc = !$isAboveFold && $lazy ? $src : null;
@endphp

<img 
    @if($actualSrc) src="{{ $actualSrc }}" @endif
    @if($dataSrc) data-src="{{ $dataSrc }}" @endif
    alt="{{ $alt }}"
    @if($width) width="{{ $width }}" @endif
    @if($height) height="{{ $height }}" @endif
    @if($lazy && !$isAboveFold) loading="lazy" @endif
    decoding="{{ $decoding }}"
    class="{{ $class }}"
    @if($placeholder && $lazy)
        style="background-color: #f3f4f6; min-height: {{ $height ?? 200 }}px;"
    @endif
>
