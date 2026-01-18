<aside class="fixed left-0 top-0 h-full w-64 bg-gray-800 text-white">
    <div class="p-6">
        <h2 class="text-xl font-bold">{{ config('app.name') }}</h2>
        <p class="text-sm text-gray-400 mt-1">Admin Panel</p>
    </div>
    
    <nav class="mt-6">
        <a 
            href="{{ route('admin.dashboard') }}" 
            class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}"
        >
            Dashboard
        </a>
        
        <!-- Additional navigation items will be added in Phase 2 -->
    </nav>
</aside>
