@blaze

@props([
    'padding' => 'md',
])

@php
    $paddings = [
        'none' => '',
        'sm' => 'p-4',
        'md' => 'p-6',
        'lg' => 'p-8',
    ];
@endphp

<div {{ $attributes->merge(['class' => "rounded border border-border bg-white shadow-sm {$paddings[$padding]}"]) }}>
    {{ $slot }}
</div>
