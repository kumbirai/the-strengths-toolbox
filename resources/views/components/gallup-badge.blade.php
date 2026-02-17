@props(['size' => 'default', 'showText' => false])

@php
    // Try to get badge from media library
    $badgeMedia = \App\Models\Media::where(function ($query) {
        $query->where('filename', 'like', '%gallup%')
            ->orWhere('original_filename', 'like', '%gallup%')
            ->orWhere('alt_text', 'like', '%gallup%');
    })->first();
    
    // Fallback to direct path if not in media library
    $badgePath = $badgeMedia 
        ? asset('storage/' . $badgeMedia->path)
        : asset('storage/media/gallup-certified.png');
    
    $sizeClasses = match($size) {
        'small' => 'h-16 w-auto',
        'large' => 'h-24 w-auto',
        default => 'h-20 w-auto'
    };
@endphp

<div class="flex items-center gap-3 {{ $showText ? 'flex-row' : 'flex-col' }}">
    <img 
        src="{{ $badgePath }}" 
        alt="Gallup Certified Strengths Coach" 
        class="{{ $sizeClasses }} object-contain"
        loading="lazy"
        onerror="this.onerror=null; this.style.display='none';"
    >
    @if($showText)
        <div class="text-sm text-neutral-600">
            <div class="font-semibold">Gallup Certified</div>
            <div class="text-xs">Strengths Coach</div>
        </div>
    @endif
</div>
