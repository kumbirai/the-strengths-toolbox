@php
use App\Helpers\NavigationHelper;
@endphp

<header class="bg-white/95 backdrop-blur-sm shadow-sm sticky top-0 z-50" x-data="{ mobileMenuOpen: false, mobileSearchOpen: false }">
    <div class="container-custom">
        <div class="flex items-center justify-between h-20">
            {{-- Logo --}}
            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center group">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="{{ config('app.name') }}"
                        class="h-12 md:h-14 w-auto group-hover:scale-105 transition-transform"
                    >
                </a>
            </div>

            {{-- Search Form (Desktop) --}}
            <div class="hidden xl:block flex-shrink-0 w-64 mx-4">
                <x-search-form placeholder="Search..." />
            </div>

            {{-- Desktop Navigation --}}
            <nav class="hidden md:flex items-center space-x-4 xl:space-x-6">
                <a href="{{ route('home') }}"
                   class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    Home
                </a>
                <a href="{{ route('strengths-programme') }}"
                   class="nav-link {{ request()->routeIs('strengths-programme') ? 'active' : '' }}">
                    Strengths Programme
                </a>

                {{-- Strengths-Based Development Dropdown --}}
                <x-navigation-dropdown
                    label="Strengths-Based Development"
                    :items="NavigationHelper::getStrengthsBasedDevelopmentItems()"
                />

                {{-- Sales Training Dropdown --}}
                <x-navigation-dropdown
                    label="Sales Training"
                    :items="NavigationHelper::getSalesTrainingItems()"
                />

                {{-- Facilitation Dropdown --}}
                <x-navigation-dropdown
                    label="Facilitation"
                    :items="NavigationHelper::getFacilitationItems()"
                />

                <a href="{{ route('blog.index') }}"
                   class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}">
                    Blog
                </a>
                <a href="{{ route('about-us') }}"
                   class="nav-link {{ request()->routeIs('about-us') ? 'active' : '' }}">
                    About
                </a>
                <a href="{{ route('contact') }}"
                   class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">
                    Contact
                </a>
            </nav>

            {{-- CTA Button (Desktop) --}}
            <div class="hidden md:block flex-shrink-0">
                <a href="{{ route('booking') }}" class="btn btn-primary">
                    Book Consultation
                </a>
            </div>

            {{-- Mobile Menu Button with Search --}}
            <div class="md:hidden flex items-center gap-2">
                {{-- Mobile Search Icon --}}
                <button
                    type="button"
                    @click="mobileSearchOpen = !mobileSearchOpen"
                    class="p-2 rounded-lg text-neutral-700 hover:text-primary-600 hover:bg-primary-50"
                    aria-label="Search"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                {{-- Mobile Menu Button --}}
                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    type="button"
                    aria-label="Toggle navigation menu"
                    :aria-expanded="mobileMenuOpen"
                    class="p-2 rounded-lg text-neutral-700 hover:text-primary-600 hover:bg-primary-50 focus:outline-none focus:ring-4 focus:ring-primary-500/30 transition-all"
                >
                    <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Search Bar --}}
        <div
            x-show="mobileSearchOpen"
            x-transition
            x-cloak
            class="md:hidden border-t border-gray-200 py-3"
        >
            <x-search-form placeholder="Search..." />
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        x-cloak
        @click.away="mobileMenuOpen = false"
        class="md:hidden bg-white shadow-xl border-t border-neutral-200 max-h-[calc(100vh-5rem)] overflow-y-auto"
        role="menu"
        aria-label="Mobile navigation menu"
    >
        <div class="container-custom py-4 space-y-1">
            <a href="{{ route('home') }}"
               class="block px-4 py-3 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-600 transition-colors">
                Home
            </a>
            <a href="{{ route('strengths-programme') }}"
               class="block px-4 py-3 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-600 transition-colors">
                Strengths Programme
            </a>

            {{-- Strengths-Based Development (Mobile Dropdown) --}}
            <div x-data="{ open: false }" class="space-y-1">
                <button
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                >
                    <span>Strengths-Based Development</span>
                    <svg
                        class="w-5 h-5 transition-transform"
                        :class="open ? 'rotate-180' : ''"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="pl-4 space-y-1">
                    @foreach(NavigationHelper::getStrengthsBasedDevelopmentItems() as $item)
                        <a href="{{ $item['url'] }}"
                           class="block px-3 py-2 rounded-md text-sm text-gray-600 hover:bg-primary-50 hover:text-primary-600">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Sales Training (Mobile Dropdown) --}}
            <div x-data="{ open: false }" class="space-y-1">
                <button
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                >
                    <span>Sales Training</span>
                    <svg
                        class="w-5 h-5 transition-transform"
                        :class="open ? 'rotate-180' : ''"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="pl-4 space-y-1">
                    @foreach(NavigationHelper::getSalesTrainingItems() as $item)
                        <a href="{{ $item['url'] }}"
                           class="block px-3 py-2 rounded-md text-sm text-gray-600 hover:bg-primary-50 hover:text-primary-600">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Facilitation (Mobile Dropdown) --}}
            <div x-data="{ open: false }" class="space-y-1">
                <button
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                >
                    <span>Facilitation</span>
                    <svg
                        class="w-5 h-5 transition-transform"
                        :class="open ? 'rotate-180' : ''"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="pl-4 space-y-1">
                    @foreach(NavigationHelper::getFacilitationItems() as $item)
                        <a href="{{ $item['url'] }}"
                           class="block px-3 py-2 rounded-md text-sm text-gray-600 hover:bg-primary-50 hover:text-primary-600">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('blog.index') }}"
               class="block px-4 py-3 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-600 transition-colors">
                Blog
            </a>
            <a href="{{ route('about-us') }}"
               class="block px-4 py-3 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-600 transition-colors">
                About
            </a>
            <a href="{{ route('contact') }}"
               class="block px-4 py-3 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-600 transition-colors">
                Contact
            </a>
            <div class="pt-2">
                <a href="{{ route('booking') }}"
                   class="block px-4 py-3 rounded-lg btn btn-primary text-center">
                    Book Consultation
                </a>
            </div>
        </div>
    </div>
</header>
