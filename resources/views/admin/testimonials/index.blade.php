@extends('layouts.admin')

@section('title', 'Testimonials')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Testimonials</h1>
            <a href="{{ route('admin.testimonials.create') }}" 
               class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                Create New Testimonial
            </a>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="p-6 border-b border-neutral-200">
        <form method="GET" action="{{ route('admin.testimonials.index') }}" class="flex gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search testimonials..." 
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <select name="is_featured" 
                        class="px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All</option>
                    <option value="1" {{ request('is_featured') == '1' ? 'selected' : '' }}>Featured</option>
                    <option value="0" {{ request('is_featured') == '0' ? 'selected' : '' }}>Not Featured</option>
                </select>
            </div>
            <button type="submit" 
                    class="px-4 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-700 transition-colors">
                Filter
            </button>
            @if(request('search') || request('is_featured'))
                <a href="{{ route('admin.testimonials.index') }}" 
                   class="px-4 py-2 bg-neutral-300 text-neutral-700 rounded-lg hover:bg-neutral-400 transition-colors">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Testimonials Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-neutral-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Company</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Testimonial</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Order</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-neutral-200">
                @forelse($testimonials as $testimonial)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-neutral-900">{{ $testimonial->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                            {{ $testimonial->company ?? '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-neutral-900">{{ Str::limit($testimonial->testimonial, 80) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($testimonial->rating)
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $testimonial->rating)
                                            <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-neutral-300 fill-current" viewBox="0 0 20 20">
                                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                            </svg>
                                        @endif
                                    @endfor
                                </div>
                            @else
                                <span class="text-sm text-neutral-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($testimonial->is_featured)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-primary-100 text-primary-800">
                                    Featured
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-neutral-100 text-neutral-800">
                                    Regular
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                            {{ $testimonial->display_order }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.testimonials.edit', $testimonial->id) }}" 
                                   class="text-primary-600 hover:text-primary-900">Edit</a>
                                <a href="{{ route('admin.testimonials.show', $testimonial->id) }}" 
                                   class="text-neutral-600 hover:text-neutral-900">View</a>
                                <button type="button" 
                                        onclick="confirmDelete({{ $testimonial->id }})"
                                        class="text-red-600 hover:text-red-900">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-neutral-500">
                            No testimonials found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($testimonials->hasPages())
        <div class="p-6 border-t border-neutral-200">
            {{ $testimonials->links() }}
        </div>
    @endif
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
        <p class="text-neutral-600 mb-6">Are you sure you want to delete this testimonial? This action cannot be undone.</p>
        <form id="deleteForm" method="POST" class="flex gap-4">
            @csrf
            @method('DELETE')
            <button type="button" 
                    onclick="closeDeleteModal()"
                    class="flex-1 px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                Cancel
            </button>
            <button type="submit" 
                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                Delete
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(testimonialId) {
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('admin/testimonials') }}/${testimonialId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
@endpush
@endsection
