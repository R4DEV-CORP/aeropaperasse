@blaze

@props([
    'name',
    'maxWidth' => 'md',
    'closable' => true,
    'title' => null,
    'description' => null,
])

@php
    $widths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
    ];
    $width = $widths[$maxWidth] ?? $widths['md'];

    $hasFooter = isset($footer);
@endphp

<div
    x-data="{ open: false }"
    x-show="open"
    x-cloak
    @open-flyout.window="if ($event.detail?.name === '{{ $name }}') open = true"
    @close-flyout.window="if ($event.detail?.name === '{{ $name }}') open = false"
    @keydown.escape.window="{{ $closable ? 'open = false' : '' }}"
    class="fixed inset-0 z-50 flex justify-end"
    role="dialog"
    aria-modal="true"
    @if ($title) aria-labelledby="flyout-title-{{ $name }}" @endif
    style="display: none;"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition.opacity
        @if ($closable) @click="open = false" @endif
        class="fixed inset-0 bg-slate-900/40"
    ></div>

    {{-- Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition transform ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition transform ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="relative z-10 flex h-full w-full {{ $width }} flex-col bg-white shadow-2xl"
        {{ $attributes }}
    >
        @if ($title || $closable)
            <div class="flex items-start justify-between gap-4 border-b border-border px-5 py-4">
                <div class="min-w-0">
                    @if ($title)
                        <h2 id="flyout-title-{{ $name }}" class="text-base font-semibold text-foreground">
                            {{ $title }}
                        </h2>
                    @endif
                    @if ($description)
                        <p class="mt-0.5 text-xs text-foreground-muted">{{ $description }}</p>
                    @endif
                </div>
                @if ($closable)
                    <button
                        type="button"
                        @click="open = false"
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md text-foreground-subtle transition hover:bg-slate-100 hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                        aria-label="Fermer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        @endif

        <div class="flex-1 overflow-y-auto px-5 py-5">
            {{ $slot }}
        </div>

        @if ($hasFooter)
            <div class="border-t border-border bg-slate-50/60 px-5 py-3">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
