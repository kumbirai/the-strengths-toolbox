@props(['label', 'items', 'route' => null])

<div class="relative group" x-data="{ open: false }">
    @if($route)
        <a 
            href="{{ $route }}"
            class="nav-link flex items-center gap-1"
            @mouseenter="open = true"
            @mouseleave="open = false"
        >
            {{ $label }}
            <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </a>
    @else
        <button 
            type="button"
            class="nav-link flex items-center gap-1"
            @click="open = !open"
            @mouseenter="open = true"
            @mouseleave="open = false"
            aria-expanded="false"
            :aria-expanded="open"
        >
            {{ $label }}
            <svg 
                class="w-4 h-4 transition-transform" 
                :class="open ? 'rotate-180' : ''"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    @endif

    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @mouseenter="open = true"
        @mouseleave="open = false"
        @click.away="open = false"
        class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-200"
        role="menu"
        aria-orientation="vertical"
    >
        @foreach($items as $item)
            <a 
                href="{{ $item['url'] }}" 
                class="block px-4 py-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition-colors {{ request()->url() === url($item['url']) ? 'bg-primary-50 text-primary-600 font-medium' : '' }}"
                role="menuitem"
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</div>
