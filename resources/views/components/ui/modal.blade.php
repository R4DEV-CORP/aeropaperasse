@blaze

@props([
    'name',
    'maxWidth' => 'lg',
    'closable' => true,
])

@php
    $widths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
    ];
    $width = $widths[$maxWidth] ?? $widths['lg'];
@endphp

<div
    x-data="{ open: false }"
    x-show="open"
    x-cloak
    @open-modal.window="if ($event.detail?.name === '{{ $name }}') open = true"
    @close-modal.window="if ($event.detail?.name === '{{ $name }}') open = false"
    @keydown.escape.window="{{ $closable ? 'open = false' : '' }}"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    style="display: none;"
>
    <div
        x-show="open"
        x-transition.opacity
        @if ($closable) @click="open = false" @endif
        class="fixed inset-0 bg-slate-900/50"
    ></div>

    <div
        x-show="open"
        x-transition
        class="relative z-10 w-full {{ $width }} overflow-hidden rounded-lg bg-white shadow-xl"
        {{ $attributes }}
    >
        {{ $slot }}
    </div>
</div>
