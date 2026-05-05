@blaze

@props([
    'label' => null,
    'description' => null,
    'hint' => null,
    'error' => null,
    'id' => null,
    'name' => null,
    'value' => '1',
    'checked' => false,
    'required' => false,
    'disabled' => false,
])

@php
    if (! $id && $label) {
        $id = 'checkbox-'.md5($label);
    }
    $hasError = ! empty($error);
@endphp

<div class="space-y-1.5">
    <label
        for="{{ $id }}"
        @class([
            'group flex cursor-pointer items-start gap-3 rounded border px-3 py-2.5 transition has-[:checked]:border-accent has-[:checked]:bg-accent/5 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-accent has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-60',
            'border-border bg-white hover:border-slate-300' => ! $hasError,
            'border-red-400 bg-red-50/40' => $hasError,
        ])
    >
        <span class="relative mt-0.5 flex h-4 w-4 flex-shrink-0 items-center justify-center">
            <input
                type="checkbox"
                id="{{ $id }}"
                @if ($name) name="{{ $name }}" @endif
                value="{{ $value }}"
                @checked($checked)
                @required($required)
                @disabled($disabled)
                {{ $attributes->merge(['class' => 'peer h-4 w-4 cursor-pointer rounded border-border bg-white text-accent transition focus:ring-2 focus:ring-accent focus:ring-offset-0 focus:outline-none disabled:cursor-not-allowed']) }}
            >
        </span>

        <span class="min-w-0 flex-1">
            @if ($label)
                <span class="block text-sm font-medium text-foreground transition group-has-[:checked]:text-accent">
                    {{ $label }}
                    @if ($required)
                        <span class="text-red-500" aria-hidden="true">*</span>
                    @endif
                </span>
            @endif
            @if ($description)
                <span class="mt-0.5 block text-xs text-foreground-muted">{{ $description }}</span>
            @endif
        </span>
    </label>

    @if ($hint && ! $hasError)
        <p class="text-xs text-foreground-subtle">{{ $hint }}</p>
    @endif
    @if ($hasError)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
