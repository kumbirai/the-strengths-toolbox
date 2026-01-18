@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'required' => false,
    'error' => null,
    'hint' => null,
    'placeholder' => '',
])

@php
    $inputId = $attributes->get('id', $name ?? uniqid('input_'));
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);

    $inputClasses = 'form-input';
    if ($hasError) {
        $inputClasses .= ' border-red-500 focus:border-red-500 focus:ring-red-500/20';
    }
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'mb-4']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $inputId }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->except(['class', 'id'])->merge(['class' => $inputClasses]) }}
    >

    @if($hint && !$hasError)
        <p class="form-hint">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="form-error">
            <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $errorMessage }}
        </p>
    @endif
</div>
