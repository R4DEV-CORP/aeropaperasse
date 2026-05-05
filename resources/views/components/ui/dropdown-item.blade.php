@blaze

@props([
    'href' => null,
    'variant' => 'default',
])

@php
    $base = 'flex w-full items-center gap-2.5 px-3 py-2 text-sm text-left transition focus:outline-none';

    $variants = [
        'default' => 'text-foreground hover:bg-slate-50 focus-visible:bg-slate-50',
        'success' => 'text-emerald-700 hover:bg-emerald-50 focus-visible:bg-emerald-50',
        'danger' => 'text-red-600 hover:bg-red-50 focus-visible:bg-red-50',
        'info' => 'text-blue-700 hover:bg-blue-50 focus-visible:bg-blue-50',
    ];

    $classes = "$base {$variants[$variant]}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} role="menuitem" @click="open = false">
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes]) }} role="menuitem" @click="open = false">
        {{ $slot }}
    </button>
@endif
