@props([
    'padding' => 'default',
    'elevated' => false,
    'border' => false,
])

@php
    $baseClasses = $elevated ? 'card-elevated' : 'card';

    $paddingClasses = [
        'none' => '',
        'sm' => 'p-6',
        'default' => 'p-8',
        'lg' => 'p-10',
    ];

    $borderClass = $border ? 'border-l-4 border-primary-500' : '';

    $classes = implode(' ', array_filter([
        $baseClasses,
        $paddingClasses[$padding] ?? $paddingClasses['default'],
        $borderClass,
        $attributes->get('class', ''),
    ]));
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
