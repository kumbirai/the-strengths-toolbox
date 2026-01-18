@extends('layouts.admin')

@section('title', 'Create Form')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <h1 class="text-2xl font-bold">Create New Form</h1>
    </div>

    <form method="POST" action="{{ route('admin.forms.store') }}" id="formBuilder" class="p-6">
        @csrf

        <div class="space-y-6">
            {{-- Form Settings --}}
            <div>
                <h2 class="text-lg font-semibold mb-4">Form Settings</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-700 mb-1">
                            Form Name <span class="text-red-500">*</span>
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
                        <label for="slug" class="block text-sm font-medium text-neutral-700 mb-1">
                            Slug
                        </label>
                        <input type="text" 
                               id="slug" 
                               name="slug" 
                               value="{{ old('slug') }}"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('slug') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-neutral-500">Leave empty to auto-generate from name</p>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email_to" class="block text-sm font-medium text-neutral-700 mb-1">
                            Email Notifications To
                        </label>
                        <input type="email" 
                               id="email_to" 
                               name="email_to" 
                               value="{{ old('email_to') }}"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('email_to') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-neutral-500">Email address to receive form submissions</p>
                        @error('email_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="success_message" class="block text-sm font-medium text-neutral-700 mb-1">
                            Success Message
                        </label>
                        <input type="text" 
                               id="success_message" 
                               name="success_message" 
                               value="{{ old('success_message', 'Thank you for your submission!') }}"
                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('success_message') border-red-500 @enderror">
                        @error('success_message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                        <label for="is_active" class="ml-2 text-sm font-medium text-neutral-700">
                            Active
                        </label>
                    </div>
                </div>
            </div>

            {{-- Form Fields Builder --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Form Fields</h2>
                    <button type="button" 
                            onclick="addField()"
                            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Add Field
                    </button>
                </div>

                <div id="fieldsContainer" class="space-y-4">
                    <!-- Fields will be added here dynamically -->
                </div>

                <input type="hidden" name="fields" id="fieldsInput" value="[]">
            </div>

            {{-- Form Actions --}}
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-neutral-200">
                <a href="{{ route('admin.forms.index') }}" 
                   class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Create Form
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Field Types Template --}}
<template id="fieldTemplate">
    <div class="field-item border border-neutral-300 rounded-lg p-4 bg-neutral-50">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium text-neutral-900">Field <span class="field-index"></span></h3>
            <button type="button" 
                    onclick="removeField(this)"
                    class="text-red-600 hover:text-red-900 text-sm">
                Remove
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Field Type <span class="text-red-500">*</span></label>
                <select name="field_type[]" 
                        class="field-type w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        onchange="updateFieldType(this)">
                    <option value="text">Text</option>
                    <option value="email">Email</option>
                    <option value="textarea">Textarea</option>
                    <option value="select">Select</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="radio">Radio</option>
                    <option value="file">File</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Label <span class="text-red-500">*</span></label>
                <input type="text" 
                       name="field_label[]" 
                       required
                       class="field-label w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" 
                       name="field_name[]" 
                       required
                       class="field-name w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Placeholder</label>
                <input type="text" 
                       name="field_placeholder[]"
                       class="field-placeholder w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
        </div>
        
        <div class="mt-4">
            <label class="flex items-center">
                <input type="checkbox" 
                       name="field_required[]" 
                       value="1"
                       class="field-required w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                <span class="ml-2 text-sm text-neutral-700">Required</span>
            </label>
        </div>
        
        <div class="field-options-container mt-4 hidden">
            <label class="block text-sm font-medium text-neutral-700 mb-1">Options (one per line)</label>
            <textarea name="field_options[]" 
                      rows="3"
                      class="field-options w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
            <p class="mt-1 text-sm text-neutral-500">For select, checkbox, and radio fields</p>
        </div>
    </div>
</template>

@push('scripts')
<script>
let fieldCount = 0;
const fields = [];

function addField() {
    fieldCount++;
    const template = document.getElementById('fieldTemplate');
    const clone = template.content.cloneNode(true);
    
    clone.querySelector('.field-index').textContent = fieldCount;
    clone.querySelector('.field-item').setAttribute('data-index', fieldCount - 1);
    
    document.getElementById('fieldsContainer').appendChild(clone);
    updateFieldsArray();
}

function removeField(button) {
    button.closest('.field-item').remove();
    updateFieldsArray();
}

function updateFieldType(select) {
    const container = select.closest('.field-item');
    const optionsContainer = container.querySelector('.field-options-container');
    const fieldType = select.value;
    
    if (['select', 'checkbox', 'radio'].includes(fieldType)) {
        optionsContainer.classList.remove('hidden');
    } else {
        optionsContainer.classList.add('hidden');
    }
    
    updateFieldsArray();
}

function updateFieldsArray() {
    const fieldItems = document.querySelectorAll('.field-item');
    fields.length = 0;
    
    fieldItems.forEach((item, index) => {
        const field = {
            type: item.querySelector('.field-type').value,
            label: item.querySelector('.field-label').value,
            name: item.querySelector('.field-name').value,
            placeholder: item.querySelector('.field-placeholder').value || null,
            required: item.querySelector('.field-required').checked,
        };
        
        const optionsText = item.querySelector('.field-options').value;
        if (optionsText && ['select', 'checkbox', 'radio'].includes(field.type)) {
            field.options = optionsText.split('\n').filter(opt => opt.trim()).map(opt => opt.trim());
        }
        
        fields.push(field);
    });
    
    document.getElementById('fieldsInput').value = JSON.stringify(fields);
}

// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
        const name = this.value;
        slugInput.value = name.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        slugInput.dataset.autoGenerated = 'true';
    }
});

// Manual slug edit disables auto-generation
document.getElementById('slug').addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
});

// Update fields array on any field change
document.addEventListener('input', function(e) {
    if (e.target.closest('.field-item')) {
        updateFieldsArray();
    }
});

document.addEventListener('change', function(e) {
    if (e.target.closest('.field-item')) {
        updateFieldsArray();
    }
});

// Form submission
document.getElementById('formBuilder').addEventListener('submit', function(e) {
    updateFieldsArray();
    
    if (fields.length === 0) {
        e.preventDefault();
        alert('Please add at least one field to the form.');
        return false;
    }
});

// Add first field on load
document.addEventListener('DOMContentLoaded', function() {
    addField();
});
</script>
@endpush
@endsection
