@extends('layouts.admin')

@section('title', 'Pages')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Pages</h1>
            <a href="{{ route('admin.pages.create') }}" 
               class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                Create New Page
            </a>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="p-6 border-b border-neutral-200">
        <form method="GET" action="{{ route('admin.pages.index') }}" class="flex gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search pages..." 
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <select name="is_published" 
                        class="px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="1" {{ request('is_published') == '1' ? 'selected' : '' }}>Published</option>
                    <option value="0" {{ request('is_published') == '0' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <button type="submit" 
                    class="px-4 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-700 transition-colors">
                Filter
            </button>
            @if(request('search') || request('is_published'))
                <a href="{{ route('admin.pages.index') }}" 
                   class="px-4 py-2 bg-neutral-300 text-neutral-700 rounded-lg hover:bg-neutral-400 transition-colors">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Pages Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-neutral-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Updated</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-neutral-200">
                @forelse($pages as $page)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-neutral-900">{{ $page->title }}</div>
                            @if($page->excerpt)
                                <div class="text-sm text-neutral-500 mt-1">{{ Str::limit($page->excerpt, 60) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-neutral-500">{{ $page->slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($page->is_published)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Published
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                            {{ $page->updated_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.pages.edit', $page->id) }}" 
                                   class="text-primary-600 hover:text-primary-900">Edit</a>
                                <a href="{{ route('admin.pages.show', $page->id) }}" 
                                   class="text-neutral-600 hover:text-neutral-900">View</a>
                                <button type="button" 
                                        onclick="confirmDelete({{ $page->id }})"
                                        class="text-red-600 hover:text-red-900">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-neutral-500">
                            No pages found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($pages->hasPages())
        <div class="p-6 border-t border-neutral-200">
            {{ $pages->links() }}
        </div>
    @endif
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
        <p class="text-neutral-600 mb-6">Are you sure you want to delete this page? This action cannot be undone.</p>
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
function confirmDelete(pageId) {
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('admin/pages') }}/${pageId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
@endpush
@endsection
