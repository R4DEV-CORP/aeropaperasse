@blaze

@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'id' => null,
    'rows' => 4,
    'required' => false,
])

@php
    if (! $id && $label) {
        $id = 'textarea-'.md5($label);
    }
    $hasError = ! empty($error);
    $borderClass = $hasError
        ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
        : 'border-border focus:border-accent focus:ring-accent';
    $textareaClass = "block w-full rounded border bg-white px-3 py-2.5 text-sm text-foreground placeholder:text-slate-400 focus:outline-none focus:ring-1 $borderClass";
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

    <textarea
        @if ($id) id="{{ $id }}" @endif
        rows="{{ $rows }}"
        @required($required)
        {{ $attributes->merge(['class' => $textareaClass]) }}
    >{{ $slot }}</textarea>

    @if ($hint && ! $hasError)
        <p class="text-xs text-foreground-subtle">{{ $hint }}</p>
    @endif
    @if ($hasError)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
