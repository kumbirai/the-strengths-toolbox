@props(['items', 'class' => ''])

<nav class="{{ $class }}">
    <ul class="flex space-x-8">
        @foreach($items as $item)
            <li>
                <a href="{{ $item['url'] }}" 
                   class="text-gray-700 hover:text-primary-600 transition-colors {{ request()->url() === $item['url'] ? 'text-primary-600 font-semibold' : '' }}">
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
