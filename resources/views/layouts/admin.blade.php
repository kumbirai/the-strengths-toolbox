<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>

    {{-- Favicons --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-neutral-100">
    <div class="min-h-screen flex">
        {{-- Sidebar --}}
        <aside class="w-64 bg-neutral-900 text-white min-h-screen">
            <div class="p-4">
                <h1 class="text-xl font-bold font-display">{{ config('app.name') }}</h1>
                <p class="text-sm text-neutral-400">Admin Panel</p>
            </div>

            <nav class="mt-8">
                <a href="{{ route('admin.dashboard') }}"
                   class="block px-4 py-2 hover:bg-neutral-800 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-neutral-800 border-l-4 border-primary-500' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.pages.index') }}"
                   class="block px-4 py-2 hover:bg-neutral-800 transition-colors {{ request()->routeIs('admin.pages.*') ? 'bg-neutral-800 border-l-4 border-primary-500' : '' }}">
                    Pages
                </a>
                <a href="{{ route('admin.blog.index') }}"
                   class="block px-4 py-2 hover:bg-neutral-800 transition-colors {{ request()->routeIs('admin.blog.*') ? 'bg-neutral-800 border-l-4 border-primary-500' : '' }}">
                    Blog Posts
                </a>
                <a href="{{ route('admin.forms.index') }}"
                   class="block px-4 py-2 hover:bg-neutral-800 transition-colors {{ request()->routeIs('admin.forms.*') ? 'bg-neutral-800 border-l-4 border-primary-500' : '' }}">
                    Forms
                </a>
                <a href="{{ route('admin.media.index') }}"
                   class="block px-4 py-2 hover:bg-neutral-800 transition-colors {{ request()->routeIs('admin.media.*') ? 'bg-neutral-800 border-l-4 border-primary-500' : '' }}">
                    Media Library
                </a>
                <a href="{{ route('admin.testimonials.index') }}"
                   class="block px-4 py-2 hover:bg-neutral-800 transition-colors {{ request()->routeIs('admin.testimonials.*') ? 'bg-neutral-800 border-l-4 border-primary-500' : '' }}">
                    Testimonials
                </a>
                <a href="{{ route('admin.seo.index') }}"
                   class="block px-4 py-2 hover:bg-neutral-800 transition-colors {{ request()->routeIs('admin.seo.*') ? 'bg-neutral-800 border-l-4 border-primary-500' : '' }}">
                    SEO Management
                </a>
            </nav>

            <div class="absolute bottom-0 w-64 p-4 border-t border-neutral-800">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 hover:bg-neutral-800 transition-colors rounded-lg">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 p-8 bg-neutral-50">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-4 bg-accent-50 border-l-4 border-accent-500 text-accent-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
