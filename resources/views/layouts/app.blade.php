<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="session-id" content="{{ session()->getId() }}">

    {{-- SEO Meta Tags --}}
    @include('partials.meta')

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Additional Styles --}}
    @stack('styles')
</head>
<body class="bg-neutral-50 text-neutral-600 antialiased" role="document">
    {{-- Header --}}
    @include('components.header')

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('components.footer')

    {{-- Additional Scripts --}}
    @stack('scripts')

    {{-- Structured Data --}}
    <x-structured-data type="organization" />
    <x-structured-data type="website" />

    {{-- Page-specific Schema --}}
    @stack('schema')

    {{-- Calendly Script --}}
    @if(config('services.calendly.enabled', false))
        <script src="https://assets.calendly.com/assets/external/widget.js" type="text/javascript" async></script>
    @endif

    {{-- Chatbot Widget --}}
    @php
        $chatbotEnabled = \App\Models\ChatbotConfig::get('enabled', true);
    @endphp
    @if($chatbotEnabled)
        @include('components.chatbot-widget')
    @endif
</body>
</html>
