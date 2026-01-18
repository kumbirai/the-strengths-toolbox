@extends('layouts.admin')

@section('title', 'Create Testimonial')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <h1 class="text-2xl font-bold">Create New Testimonial</h1>
    </div>

    <form method="POST" action="{{ route('admin.testimonials.store') }}" class="p-6">
        @csrf

        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-neutral-700 mb-1">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       required
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="company" class="block text-sm font-medium text-neutral-700 mb-1">
                    Company
                </label>
                <input type="text" 
                       id="company" 
                       name="company" 
                       value="{{ old('company') }}"
                       class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('company') border-red-500 @enderror">
                @error('company')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="testimonial" class="block text-sm font-medium text-neutral-700 mb-1">
                    Testimonial <span class="text-red-500">*</span>
                </label>
                <textarea id="testimonial" 
                          name="testimonial" 
                          rows="6"
                          required
                          class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('testimonial') border-red-500 @enderror">{{ old('testimonial') }}</textarea>
                @error('testimonial')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="rating" class="block text-sm font-medium text-neutral-700 mb-1">
                        Rating
                    </label>
                    <select id="rating" 
                            name="rating"
                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('rating') border-red-500 @enderror">
                        <option value="">No Rating</option>
                        <option value="1" {{ old('rating') == '1' ? 'selected' : '' }}>1 Star</option>
                        <option value="2" {{ old('rating') == '2' ? 'selected' : '' }}>2 Stars</option>
                        <option value="3" {{ old('rating') == '3' ? 'selected' : '' }}>3 Stars</option>
                        <option value="4" {{ old('rating') == '4' ? 'selected' : '' }}>4 Stars</option>
                        <option value="5" {{ old('rating') == '5' ? 'selected' : '' }}>5 Stars</option>
                    </select>
                    @error('rating')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="user_id" class="block text-sm font-medium text-neutral-700 mb-1">
                        User (Optional)
                    </label>
                    <select id="user_id" 
                            name="user_id"
                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('user_id') border-red-500 @enderror">
                        <option value="">No User</option>
                        @foreach(\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="display_order" class="block text-sm font-medium text-neutral-700 mb-1">
                        Display Order
                    </label>
                    <input type="number" 
                           id="display_order" 
                           name="display_order" 
                           value="{{ old('display_order') }}"
                           min="0"
                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('display_order') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-neutral-500">Lower numbers appear first</p>
                    @error('display_order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center pt-8">
                    <input type="checkbox" 
                           id="is_featured" 
                           name="is_featured" 
                           value="1"
                           {{ old('is_featured') ? 'checked' : '' }}
                           class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <label for="is_featured" class="ml-2 text-sm font-medium text-neutral-700">
                        Featured Testimonial
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-neutral-200">
                <a href="{{ route('admin.testimonials.index') }}" 
                   class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Create Testimonial
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
