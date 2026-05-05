@blaze

@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'name' => null,
    'value' => null,
    'options' => [],
    'columns' => 1,
    'required' => false,
    'disabled' => false,
])

@php
    $hasError = ! empty($error);

    $normalizedOptions = collect($options)
        ->map(fn ($opt) => [
            'value' => $opt['value'] ?? null,
            'label' => (string) ($opt['label'] ?? ''),
            'description' => $opt['description'] ?? null,
        ])
        ->values()
        ->all();

    $gridCols = match ((int) $columns) {
        2 => 'sm:grid-cols-2',
        3 => 'sm:grid-cols-3',
        4 => 'sm:grid-cols-4',
        default => '',
    };
@endphp

<fieldset class="space-y-1.5">
    @if ($label)
        <legend class="block text-sm font-medium text-foreground-muted">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </legend>
    @endif

    <div class="grid grid-cols-1 gap-2 {{ $gridCols }}">
        @foreach ($normalizedOptions as $option)
            <label class="group cursor-pointer rounded border border-border bg-white px-3 py-2.5 text-center transition hover:border-slate-300 has-[:checked]:border-accent has-[:checked]:bg-accent/5 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-accent has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-60">
                <input
                    type="radio"
                    class="sr-only"
                    @if ($name) name="{{ $name }}" @endif
                    value="{{ $option['value'] }}"
                    @checked((string) $value === (string) $option['value'])
                    @disabled($disabled)
                    {{ $attributes->whereStartsWith('wire:model') }}
                >

                <div class="text-sm font-semibold text-foreground transition group-has-[:checked]:text-accent">
                    {{ $option['label'] }}
                </div>

                @if ($option['description'])
                    <div class="mt-0.5 text-xs text-foreground-muted">
                        {{ $option['description'] }}
                    </div>
                @endif
            </label>
        @endforeach
    </div>

    @if ($hint && ! $hasError)
        <p class="text-xs text-foreground-subtle">{{ $hint }}</p>
    @endif
    @if ($hasError)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif
</fieldset>
