@blaze

@props([
    'text' => null,
    'placement' => 'bottom',
    'align' => 'center',
])

@php
    $placementClasses = [
        'top'    => 'bottom-full mb-2',
        'bottom' => 'top-full mt-2',
        'left'   => 'right-full mr-2',
        'right'  => 'left-full ml-2',
    ];

    $isVertical = in_array($placement, ['top', 'bottom'], true);

    $alignClasses = $isVertical
        ? [
            'start'  => 'left-0',
            'center' => 'left-1/2 -translate-x-1/2',
            'end'    => 'right-0',
        ]
        : [
            'start'  => 'top-0',
            'center' => 'top-1/2 -translate-y-1/2',
            'end'    => 'bottom-0',
        ];

    $position = ($placementClasses[$placement] ?? $placementClasses['bottom'])
        . ' '
        . ($alignClasses[$align] ?? $alignClasses['center']);

    $hasContent = isset($content) || $text !== null;
@endphp

<span
    x-data="{ tooltipShow: false }"
    @mouseenter="tooltipShow = true"
    @mouseleave="tooltipShow = false"
    @focusin="tooltipShow = true"
    @focusout="tooltipShow = false"
    {{ $attributes->merge(['class' => 'relative inline-flex']) }}
>
    {{ $slot }}

    @if ($hasContent)
        <span
            x-show="tooltipShow"
            x-transition.opacity
            x-cloak
            role="tooltip"
            class="pointer-events-none absolute z-50 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-xs font-medium text-white shadow-lg {{ $position }}"
        >
            {{ $content ?? $text }}
        </span>
    @endif
</span>
