@blaze

@props([
    'options' => [],
    'name' => null,
    'value' => null,
])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-lg bg-slate-100 p-1']) }} role="radiogroup">
    @foreach ($options as $option)
        @php
            $optionValue = $option['value'] ?? null;
            $isActive = (string) $optionValue === (string) $value;
            $btnClass = $isActive
                ? 'bg-white text-foreground shadow-sm ring-1 ring-border/60'
                : 'text-foreground-muted hover:text-foreground';
        @endphp
        <button
            type="button"
            role="radio"
            aria-checked="{{ $isActive ? 'true' : 'false' }}"
            wire:click="$set('{{ $name }}', @js($optionValue))"
            class="rounded-md px-3 py-1.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent {{ $btnClass }}"
        >
            {{ $option['label'] }}
        </button>
    @endforeach
</div>
