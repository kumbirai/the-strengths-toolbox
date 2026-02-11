@props(['comments', 'post'])

<div id="comments" class="space-y-6">
    @if($comments->count() > 0)
        <h3 class="text-2xl font-semibold text-neutral-900 mb-6">
            {{ $comments->count() }} {{ Str::plural('Comment', $comments->count()) }}
        </h3>

        @foreach($comments as $comment)
            <x-blog.comment-item :comment="$comment" :post="$post" :depth="0" />
        @endforeach
    @else
        <div class="text-center py-8 text-neutral-500">
            <p>No comments yet. Be the first to leave a comment!</p>
        </div>
    @endif
</div>
