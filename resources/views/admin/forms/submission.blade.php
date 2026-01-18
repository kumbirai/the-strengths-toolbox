@extends('layouts.admin')

@section('title', 'Form Submission')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-neutral-200">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Submission Details</h1>
            <a href="{{ route('admin.forms.submissions', $form->id) }}" 
               class="px-4 py-2 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                Back to Submissions
            </a>
        </div>
    </div>

    <div class="p-6 space-y-6">
        <div>
            <h2 class="text-lg font-semibold mb-4">Submission Information</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Form</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $form->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Submitted</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $submission->created_at->format('M d, Y H:i:s') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-500">IP Address</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $submission->ip_address }}</dd>
                </div>
                @if($submission->user_id)
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">User</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $submission->user->name ?? 'N/A' }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div>
            <h2 class="text-lg font-semibold mb-4">Submission Data</h2>
            <div class="bg-neutral-50 rounded-lg p-4">
                @php
                    $data = is_array($submission->data) ? $submission->data : json_decode($submission->data, true);
                @endphp
                @if($data)
                    <dl class="space-y-3">
                        @foreach($data as $key => $value)
                            <div class="border-b border-neutral-200 pb-3 last:border-0 last:pb-0">
                                <dt class="text-sm font-medium text-neutral-700 mb-1">{{ $key }}</dt>
                                <dd class="text-sm text-neutral-900">
                                    @if(is_array($value))
                                        <pre class="bg-white p-2 rounded text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        {{ $value }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="text-sm text-neutral-500">No data available.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
