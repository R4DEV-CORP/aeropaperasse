@blaze

@props([
    'align' => 'right',
    'width' => 'w-48',
])

<div
    x-data="{
        open: false,
        align: '{{ $align }}',
        top: 'auto',
        bottom: 'auto',
        left: 'auto',
        right: 'auto',
        toggle() {
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
    {{ $attributes->merge(['class' => 'inline-block']) }}
>
    <div x-ref="trigger" @click="toggle">
        {{ $trigger }}
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
