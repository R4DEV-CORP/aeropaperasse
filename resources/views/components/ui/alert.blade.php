@blaze

@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $variants = [
        'info' => [
            'container' => 'bg-blue-50 border-blue-200 text-blue-900',
            'title' => 'text-blue-900',
        ],
        'success' => [
            'container' => 'bg-emerald-50 border-emerald-200 text-emerald-900',
            'title' => 'text-emerald-900',
        ],
        'warning' => [
            'container' => 'bg-amber-50 border-amber-200 text-amber-900',
            'title' => 'text-amber-900',
        ],
        'danger' => [
            'container' => 'bg-red-50 border-red-200 text-red-900',
            'title' => 'text-red-900',
        ],
    ];

    $v = $variants[$variant];
@endphp

<div role="alert" {{ $attributes->merge(['class' => "rounded border px-4 py-3 text-sm {$v['container']}"]) }}>
    @if ($title)
        <p class="font-semibold {{ $v['title'] }}">{{ $title }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div @class(['mt-1' => $title])>
            {{ $slot }}
        </div>
    @endif
</div>
