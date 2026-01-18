@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Categories</h1>
            <a href="{{ route('admin.blog.categories.create') }}" 
               class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                Create New Category
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-neutral-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Posts</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-neutral-200">
                @forelse($categories as $category)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-neutral-900">{{ $category->name }}</div>
                            @if($category->description)
                                <div class="text-sm text-neutral-500 mt-1">{{ Str::limit($category->description, 60) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                            {{ $category->slug }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                            {{ $category->blog_posts_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.blog.categories.edit', $category->id) }}" 
                                   class="text-primary-600 hover:text-primary-900">Edit</a>
                                <button type="button" 
                                        onclick="confirmDelete({{ $category->id }}, '{{ $category->name }}', {{ $category->blog_posts_count }})"
                                        class="text-red-600 hover:text-red-900">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-neutral-500">
                            No categories found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="p-6 border-t border-neutral-200">
            {{ $categories->links() }}
        </div>
    @endif
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
        <p id="deleteMessage" class="text-neutral-600 mb-6"></p>
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
function confirmDelete(categoryId, categoryName, postCount) {
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('admin/blog/categories') }}/${categoryId}`;
    
    const message = document.getElementById('deleteMessage');
    if (postCount > 0) {
        message.textContent = `Cannot delete category "${categoryName}" because it has ${postCount} associated blog post(s).`;
        form.querySelector('button[type="submit"]').disabled = true;
        form.querySelector('button[type="submit"]').classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        message.textContent = `Are you sure you want to delete the category "${categoryName}"? This action cannot be undone.`;
        form.querySelector('button[type="submit"]').disabled = false;
        form.querySelector('button[type="submit"]').classList.remove('opacity-50', 'cursor-not-allowed');
    }
    
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
@endpush
@endsection
