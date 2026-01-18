<header class="bg-white shadow-sm">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">Admin Panel</h1>
            
            @auth('admin')
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">
                    {{ Auth::guard('admin')->user()->name }}
                </span>
                
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button 
                        type="submit" 
                        class="text-sm text-gray-600 hover:text-gray-900"
                    >
                        Logout
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </div>
</header>
