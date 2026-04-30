@blaze

@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'id' => null,
    'type' => 'text',
    'required' => false,
    'togglePassword' => false,
])

@php
    $id = $id ?? 'input-' . uniqid();
    $hasError = ! empty($error);
    $borderClass = $hasError
        ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
        : 'border-border focus:border-accent focus:ring-accent';
    $inputClass = "block w-full rounded border bg-slate-50 px-3 py-2.5 text-sm text-foreground placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-1 $borderClass" . ($togglePassword ? ' pr-10' : '');
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

    @if ($togglePassword)
        <div class="relative" x-data="{ visible: false }">
            <input
                id="{{ $id }}"
                :type="visible ? 'text' : '{{ $type }}'"
                @required($required)
                {{ $attributes->merge(['class' => $inputClass]) }}
            />
            <button
                type="button"
                x-on:click="visible = !visible"
                :aria-label="visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                :aria-pressed="visible"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-foreground-muted hover:text-foreground focus:text-foreground focus:outline-none"
            >
                <svg x-show="!visible" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <svg x-show="visible" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
            </button>
        </div>
    @else
        <input
            id="{{ $id }}"
            type="{{ $type }}"
            @required($required)
            {{ $attributes->merge(['class' => $inputClass]) }}
        />
    @endif

    @if ($hint && ! $hasError)
        <p class="text-xs text-foreground-subtle">{{ $hint }}</p>
    @endif
    @if ($hasError)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
