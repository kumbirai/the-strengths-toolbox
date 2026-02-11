@props(['comment', 'post', 'depth' => 0])

@php
    $maxDepth = 5; // Maximum nesting depth
    $isReply = $depth > 0;
    $marginLeft = $depth * 2; // 2rem per level
@endphp

<div 
    id="comment-{{ $comment->id }}" 
    class="comment-item {{ $isReply ? 'border-l-4 border-primary-200 pl-4' : '' }}"
    style="{{ $isReply ? "margin-left: {$marginLeft}rem;" : '' }}"
>
    <div class="bg-white rounded-lg shadow-sm border border-neutral-200 p-6 mb-4">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-semibold">
                    {{ strtoupper(substr($comment->author_name, 0, 1)) }}
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-2">
                    <h4 class="font-semibold text-neutral-900">
                        {{ $comment->author_name }}
                    </h4>
                    @if($comment->author_website)
                        <a 
                            href="{{ $comment->author_website }}" 
                            target="_blank" 
                            rel="nofollow noopener"
                            class="text-primary-600 hover:text-primary-700 text-sm"
                        >
                            {{ parse_url($comment->author_website, PHP_URL_HOST) }}
                        </a>
                    @endif
                </div>

                <div class="text-sm text-neutral-500 mb-3">
                    <time datetime="{{ $comment->created_at->toIso8601String() }}">
                        {{ $comment->created_at->format('F d, Y \a\t g:i A') }}
                    </time>
                </div>

                <div class="prose prose-sm max-w-none text-neutral-700 mb-4">
                    {!! nl2br(e($comment->content)) !!}
                </div>

                @if($depth < $maxDepth)
                    <div class="mt-4">
                        <button
                            type="button"
                            onclick="toggleReplyForm({{ $comment->id }})"
                            class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                        >
                            Reply
                        </button>
                    </div>

                    <div id="reply-form-{{ $comment->id }}" class="hidden mt-4">
                        <x-blog.comment-form :post="$post" :parentId="$comment->id" />
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Nested Replies --}}
    @if($comment->approvedReplies->count() > 0)
        <div class="space-y-4 mt-4">
            @foreach($comment->approvedReplies as $reply)
                <x-blog.comment-item :comment="$reply" :post="$post" :depth="$depth + 1" />
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
function toggleReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    if (form) {
        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            const textarea = form.querySelector('textarea[name="content"]');
            if (textarea) {
                textarea.focus();
            }
        }
    }
}
</script>
@endpush
