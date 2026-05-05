@blaze

@props([
    'name' => null,
    'value' => null,
    'options' => [],
    'placeholder' => 'Sélectionnez…',
    'searchable' => false,
    'searchPlaceholder' => 'Rechercher…',
    'emptyText' => 'Aucun résultat.',
    'label' => null,
    'hint' => null,
    'error' => null,
    'id' => null,
    'required' => false,
    'disabled' => false,
    'optionsTarget' => null,
])

@php
    if (! $id && $label) {
        $id = 'select-'.md5($label);
    }

    $hasError = ! empty($error);
    $borderClass = $hasError
        ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
        : 'border-border focus:border-accent focus:ring-accent';

    $normalizedOptions = collect($options)
        ->map(function ($opt) {
            if (is_array($opt)) {
                return [
                    'value' => $opt['value'] ?? null,
                    'label' => (string) ($opt['label'] ?? ''),
                    'hint' => isset($opt['hint']) ? (string) $opt['hint'] : null,
                ];
            }

            return ['value' => $opt, 'label' => (string) $opt, 'hint' => null];
        })
        ->values()
        ->all();

    $wireModelProperty = null;
    foreach (['wire:model.live', 'wire:model.blur', 'wire:model.change', 'wire:model'] as $key) {
        if ($attributes->has($key)) {
            $wireModelProperty = $attributes->get($key);
            break;
        }
    }
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-foreground-muted">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div
        x-data="{
            open: false,
            search: '',
            value: @js($value),
            options: @js($normalizedOptions),
            triggerRect: { top: 0, left: 0, width: 0, bottom: 0, height: 0 },
            get filteredOptions() {
                if (! this.search.trim()) return this.options;
                const q = this.search.toLowerCase();
                return this.options.filter(o =>
                    String(o.label).toLowerCase().includes(q)
                    || (o.hint && String(o.hint).toLowerCase().includes(q))
                );
            },
            get selectedLabel() {
                const found = this.options.find(o => String(o.value) === String(this.value));
                return found ? found.label : '';
            },
            get popoverStyle() {
                const r = this.triggerRect;
                return `position:fixed; top:${r.bottom + 4}px; left:${r.left}px; width:${r.width}px;`;
            },
            isSelected(option) {
                return this.value !== null && this.value !== '' && String(this.value) === String(option.value);
            },
            updateRect() {
                if (this.$refs.trigger) {
                    this.triggerRect = this.$refs.trigger.getBoundingClientRect();
                }
            },
            select(option) {
                this.value = option.value;
                this.open = false;
                this.search = '';
                this.$nextTick(() => {
                    if (this.$refs.hidden) {
                        this.$refs.hidden.dispatchEvent(new Event('input', { bubbles: true }));
                        this.$refs.hidden.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            },
            toggle() {
                if ({{ $disabled ? 'true' : 'false' }}) return;
                this.open = ! this.open;
                if (this.open) {
                    this.updateRect();
                    if ({{ $searchable ? 'true' : 'false' }}) {
                        this.$nextTick(() => this.$refs.search?.focus());
                    }
                }
            },
            init() {
                @if ($wireModelProperty)
                    this.$watch('$wire.{{ $wireModelProperty }}', (val) => {
                        if (String(val ?? '') !== String(this.value ?? '')) {
                            this.value = val;
                        }
                    });
                @endif
            },
            replaceOptions(newOptions, newValue) {
                this.options = Array.isArray(newOptions) ? newOptions : [];
                if (newValue !== undefined && newValue !== null) {
                    this.value = newValue;
                }
            },
        }"
        @keydown.escape.stop="open = false"
        @click.outside="open = false"
        @scroll.window.passive="if (open) updateRect()"
        @resize.window.passive="if (open) updateRect()"
        @if ($optionsTarget)
            @select-options-updated.window="if ($event.detail?.target === '{{ $optionsTarget }}') replaceOptions($event.detail.options, $event.detail.value)"
        @endif
        class="relative"
    >
        <button
            type="button"
            id="{{ $id }}"
            x-ref="trigger"
            @click="toggle()"
            :aria-expanded="open"
            aria-haspopup="listbox"
            @disabled($disabled)
            class="flex w-full items-center justify-between gap-2 rounded border bg-white px-3 py-2.5 text-left text-sm focus:outline-none focus:ring-1 disabled:cursor-not-allowed disabled:opacity-50 {{ $borderClass }}"
        >
            <span class="min-w-0 flex-1 truncate">
                <template x-if="value === null || value === '' || value === undefined">
                    <span class="text-slate-400">{{ $placeholder }}</span>
                </template>
                <template x-if="value !== null && value !== '' && value !== undefined">
                    <span class="text-foreground" x-text="selectedLabel"></span>
                </template>
            </span>
            <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 flex-shrink-0 text-foreground-subtle transition-transform">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        {{-- Hidden input forwards wire:model and posts the value with native form submits --}}
        <input
            type="hidden"
            x-ref="hidden"
            @if ($name) name="{{ $name }}" @endif
            :value="value ?? ''"
            {{ $attributes->whereStartsWith('wire:model') }}
        >

        <template x-teleport="body">
        <div
            x-show="open"
            x-transition.opacity.duration.150ms
            x-cloak
            :style="popoverStyle"
            class="z-50 overflow-hidden rounded-md border border-border bg-white shadow-lg ring-1 ring-black/5"
        >
            @if ($searchable)
                <div class="border-b border-border bg-slate-50/80 p-2">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-foreground-subtle">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>
                        <input
                            x-ref="search"
                            x-model="search"
                            @click.stop
                            @keydown.escape.stop="open = false"
                            type="text"
                            placeholder="{{ $searchPlaceholder }}"
                            class="block w-full rounded border border-border bg-white py-1.5 pr-3 pl-8 text-sm text-foreground placeholder:text-slate-400 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                        >
                    </div>
                </div>
            @endif

            <ul role="listbox" class="max-h-60 overflow-y-auto py-1">
                <template x-for="option in filteredOptions" :key="String(option.value)">
                    <li
                        role="option"
                        :aria-selected="isSelected(option)"
                        @click="select(option)"
                        class="flex cursor-pointer items-start justify-between gap-2 px-3 py-2 text-sm transition hover:bg-slate-100"
                        :class="isSelected(option) ? 'bg-accent/5 text-accent' : 'text-foreground'"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="truncate" :class="isSelected(option) && 'font-medium'" x-text="option.label"></div>
                            <template x-if="option.hint">
                                <div class="truncate text-xs text-foreground-subtle" x-text="option.hint"></div>
                            </template>
                        </div>
                        <svg x-show="isSelected(option)" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="mt-0.5 h-4 w-4 flex-shrink-0 text-accent">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </li>
                </template>
                <li x-show="filteredOptions.length === 0" class="px-3 py-2 text-sm italic text-foreground-subtle">
                    {{ $emptyText }}
                </li>
            </ul>
        </div>
        </template>
    </div>

    @if ($hint && ! $hasError)
        <p class="text-xs text-foreground-subtle">{{ $hint }}</p>
    @endif
    @if ($hasError)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
