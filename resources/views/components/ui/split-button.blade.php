@blaze

@props([
    'variant' => 'primary',
    'size' => 'md',
    'align' => 'right',
    'width' => 'w-56',
    'disabled' => false,
])

@php
    $base = 'inline-flex items-center font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'bg-accent text-accent-foreground hover:bg-accent-content focus:ring-accent',
        'secondary' => 'bg-white text-foreground ring-1 ring-border hover:bg-slate-50 focus:ring-slate-400',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-500',
        'info' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    ];

    $dividerColors = [
        'primary' => 'bg-black/10',
        'secondary' => 'bg-border',
        'success' => 'bg-black/15',
        'danger' => 'bg-black/15',
        'warning' => 'bg-black/15',
        'info' => 'bg-black/15',
    ];

    $sizes = [
        'sm' => ['py' => 'py-1.5', 'pxLeft' => 'pl-3 pr-2.5', 'pxRight' => 'px-2', 'text' => 'text-sm'],
        'md' => ['py' => 'py-2.5', 'pxLeft' => 'pl-4 pr-3', 'pxRight' => 'px-2.5', 'text' => 'text-sm'],
        'lg' => ['py' => 'py-3', 'pxLeft' => 'pl-5 pr-3.5', 'pxRight' => 'px-3', 'text' => 'text-base'],
    ];
    $sz = $sizes[$size] ?? $sizes['md'];

    $variantClass = $variants[$variant] ?? $variants['primary'];
    $dividerClass = $dividerColors[$variant] ?? $dividerColors['primary'];

    $leftClass = "$base $variantClass {$sz['py']} {$sz['pxLeft']} {$sz['text']} gap-2 rounded-l";
    $rightClass = "$base $variantClass {$sz['py']} {$sz['pxRight']} rounded-r";
@endphp

<div
    x-data="{
        open: false,
        align: '{{ $align }}',
        top: 'auto',
        bottom: 'auto',
        left: 'auto',
        right: 'auto',
        toggle() {
            if ({{ $disabled ? 'true' : 'false' }}) return;
            if (! this.open) {
                this.computePosition();
            }
            this.open = ! this.open;
        },
        computePosition() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            const dropUp = spaceBelow < 240 && spaceAbove > spaceBelow;

            if (dropUp) {
                this.top = 'auto';
                this.bottom = (window.innerHeight - rect.top + 4) + 'px';
            } else {
                this.top = (rect.bottom + 4) + 'px';
                this.bottom = 'auto';
            }

            if (this.align === 'right') {
                this.left = 'auto';
                this.right = (window.innerWidth - rect.right) + 'px';
            } else {
                this.left = rect.left + 'px';
                this.right = 'auto';
            }
        },
    }"
    @scroll.window="open && (open = false)"
    @resize.window="open && (open = false)"
    {{ $attributes->merge(['class' => 'inline-flex']) }}
>
    <div x-ref="trigger" class="inline-flex">
        <button
            type="button"
            @click="toggle"
            @disabled($disabled)
            class="{{ $leftClass }}"
        >
            {{ $label ?? $slot }}
        </button>
        <span aria-hidden="true" class="w-px {{ $dividerClass }}"></span>
        <button
            type="button"
            @click="toggle"
            @disabled($disabled)
            :aria-expanded="open"
            aria-haspopup="menu"
            aria-label="Plus d'options"
            class="{{ $rightClass }}"
        >
            <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 transition-transform">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
    </div>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            x-transition
            @click.outside="open = false"
            @keydown.escape.window="open = false"
            @wheel.window="open && (open = false)"
            @touchmove.window="open && (open = false)"
            :style="`top: ${top}; bottom: ${bottom}; left: ${left}; right: ${right};`"
            class="fixed z-50 {{ $width }} rounded-md border border-border bg-white py-1 shadow-lg ring-1 ring-black/5"
            role="menu"
        >
            {{ $slot }}
        </div>
    </template>
</div>
