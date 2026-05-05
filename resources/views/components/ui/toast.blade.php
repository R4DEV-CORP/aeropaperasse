@blaze

@props([
    'variant' => 'info',
    'title' => null,
    'dismissable' => true,
])

@php
    $variants = [
        'info' => [
            'bar' => 'bg-blue-500',
            'border' => 'border-blue-200',
        ],
        'success' => [
            'bar' => 'bg-emerald-500',
            'border' => 'border-emerald-200',
        ],
        'warning' => [
            'bar' => 'bg-amber-500',
            'border' => 'border-amber-200',
        ],
        'danger' => [
            'bar' => 'bg-red-500',
            'border' => 'border-red-200',
        ],
    ];

    $v = $variants[$variant] ?? $variants['info'];
@endphp

<div
    role="status"
    {{ $attributes->merge(['class' => "pointer-events-auto flex w-80 items-stretch overflow-hidden rounded-lg border bg-white shadow-lg {$v['border']}"]) }}
>
    <span class="w-1 shrink-0 {{ $v['bar'] }}" aria-hidden="true"></span>

    <div class="flex flex-1 items-start gap-3 px-3 py-3">
        <div class="min-w-0 flex-1">
            @if ($title)
                <p class="text-sm font-semibold text-foreground">{{ $title }}</p>
            @endif

            @if ($slot->isNotEmpty())
                <div @class(['text-sm text-foreground-muted', 'mt-0.5' => $title])>
                    {{ $slot }}
                </div>
            @endif
        </div>

        @if ($dismissable)
            <button
                type="button"
                aria-label="Fermer"
                class="shrink-0 rounded p-0.5 text-foreground-subtle transition hover:bg-slate-100 hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</div>
