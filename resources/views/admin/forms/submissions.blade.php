@extends('layouts.admin')

@section('title', 'Form Submissions')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Submissions: {{ $form->name }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.forms.export', $form->id) }}" 
                   class="px-4 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-700 transition-colors">
                    Export CSV
                </a>
                <a href="{{ route('admin.forms.index') }}" 
                   class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                    Back to Forms
                </a>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-neutral-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Submitted</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">IP Address</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-neutral-200">
                @forelse($submissions as $submission)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                            {{ $submission->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-neutral-900">
                                @php
                                    $data = is_array($submission->data) ? $submission->data : json_decode($submission->data, true);
                                @endphp
                                @if($data)
                                    @foreach(array_slice($data, 0, 3) as $key => $value)
                                        <div><strong>{{ $key }}:</strong> {{ Str::limit($value, 50) }}</div>
                                    @endforeach
                                    @if(count($data) > 3)
                                        <div class="text-neutral-500">... and {{ count($data) - 3 }} more</div>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                            {{ $submission->ip_address }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.forms.submission.show', [$form->id, $submission->id]) }}" 
                               class="text-primary-600 hover:text-primary-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-neutral-500">
                            No submissions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($submissions->hasPages())
        <div class="p-6 border-t border-neutral-200">
            {{ $submissions->links() }}
        </div>
    @endif
</div>
@endsection
