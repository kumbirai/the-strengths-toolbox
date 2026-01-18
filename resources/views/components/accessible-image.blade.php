@props([
    'src',
    'alt' => '',
    'lazy' => true,
    'class' => '',
    'width' => null,
    'height' => null,
])

@php
    $loading = $lazy ? 'lazy' : 'eager';
    $classes = 'w-full h-auto ' . $class;
@endphp

<img 
    src="{{ $src }}" 
    alt="{{ $alt }}"
    @if($width) width="{{ $width }}" @endif
    @if($height) height="{{ $height }}" @endif
    loading="{{ $loading }}"
    decoding="async"
    class="{{ $classes }}"
    @if(empty($alt)) role="presentation" @endif
>
