@props(['placeholder' => 'Search...', 'class' => ''])

<form action="{{ route('search') }}" method="GET" class="{{ $class }}" x-data="{ query: '{{ request('q', '') }}' }">
    <div class="relative">
        <input 
            type="text" 
            name="q" 
            x-model="query"
            value="{{ request('q', '') }}"
            placeholder="{{ $placeholder }}"
            class="w-full px-4 py-2 pl-10 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            aria-label="Search"
        >
        <button type="submit" class="absolute inset-y-0 left-0 flex items-center pl-3" aria-label="Submit search">
            <svg class="w-5 h-5 text-gray-400 hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </button>
        @if(request('q'))
            <a href="{{ route('search') }}" class="absolute inset-y-0 right-0 flex items-center pr-3" aria-label="Clear search">
                <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
        @endif
    </div>
</form>
