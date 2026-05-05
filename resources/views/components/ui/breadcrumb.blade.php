@blaze

@props([
    'items' => [],
])

<nav {{ $attributes->merge(['class' => 'flex items-center gap-2 text-sm']) }} aria-label="Fil d'Ariane">
    @foreach ($items as $i => $item)
        @if ($i > 0)
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 shrink-0 text-foreground-subtle">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        @endif

        @if ($loop->last)
            <span class="font-medium text-foreground">{{ $item['label'] }}</span>
        @elseif (! empty($item['href']))
            <a href="{{ $item['href'] }}" wire:navigate class="text-foreground-muted transition hover:text-foreground">{{ $item['label'] }}</a>
        @else
            <span class="text-foreground-muted">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
