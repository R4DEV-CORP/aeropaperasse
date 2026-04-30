@blaze

@props([
    'length' => 6,
    'label' => null,
    'error' => null,
])

@php
    $hasError = ! empty($error);
    $boxClass = $hasError
        ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
        : 'border-border focus:border-accent focus:ring-accent';
@endphp

<div
    class="space-y-2"
    x-data="{
        digits: Array({{ $length }}).fill(''),
        sync() {
            this.$refs.hidden.value = this.digits.join('');
            this.$refs.hidden.dispatchEvent(new Event('input', { bubbles: true }));
        },
        focusBox(i) {
            const box = this.$refs.boxes.children[i];
            if (box) {
                box.focus();
                box.select();
            }
        },
        onInput(event, index) {
            const value = event.target.value.replace(/[^0-9]/g, '').slice(-1);
            event.target.value = value;
            this.digits[index] = value;
            if (value && index < this.digits.length - 1) {
                this.focusBox(index + 1);
            }
            this.sync();
        },
        onKeydown(event, index) {
            if (event.key === 'Backspace') {
                if (!this.digits[index] && index > 0) {
                    event.preventDefault();
                    this.digits[index - 1] = '';
                    this.$refs.boxes.children[index - 1].value = '';
                    this.focusBox(index - 1);
                    this.sync();
                }
            } else if (event.key === 'ArrowLeft' && index > 0) {
                event.preventDefault();
                this.focusBox(index - 1);
            } else if (event.key === 'ArrowRight' && index < this.digits.length - 1) {
                event.preventDefault();
                this.focusBox(index + 1);
            }
        },
        onPaste(event) {
            event.preventDefault();
            const text = (event.clipboardData || window.clipboardData).getData('text');
            const cleaned = text.replace(/[^0-9]/g, '').slice(0, this.digits.length);
            if (!cleaned) return;
            for (let i = 0; i < this.digits.length; i++) {
                this.digits[i] = cleaned[i] || '';
                this.$refs.boxes.children[i].value = this.digits[i];
            }
            const next = Math.min(cleaned.length, this.digits.length - 1);
            this.focusBox(next);
            this.sync();
        }
    }"
>
    @if ($label)
        <label class="block text-center text-sm font-medium text-foreground-muted">
            {{ $label }}
        </label>
    @endif

    <input type="hidden" x-ref="hidden" {{ $attributes->whereStartsWith('wire:model') }} />

    <div
        x-ref="boxes"
        class="flex justify-center gap-2"
        x-on:paste="onPaste($event)"
    >
        @for ($i = 0; $i < $length; $i++)
            <input
                type="text"
                inputmode="numeric"
                maxlength="1"
                @if ($i === 0) autocomplete="one-time-code" autofocus @else autocomplete="off" @endif
                aria-label="Chiffre {{ $i + 1 }}"
                x-on:input="onInput($event, {{ $i }})"
                x-on:keydown="onKeydown($event, {{ $i }})"
                x-on:focus="$event.target.select()"
                class="h-14 w-12 rounded border bg-slate-50 text-center text-xl font-semibold text-foreground focus:bg-white focus:outline-none focus:ring-1 {{ $boxClass }}"
            />
        @endfor
    </div>

    @if ($hasError)
        <p class="text-center text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
