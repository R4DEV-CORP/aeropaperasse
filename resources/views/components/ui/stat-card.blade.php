@blaze

@props([
    'label',
    'value',
    'variant' => 'default',
])

@php
    $accentColors = [
        'default' => 'bg-slate-300',
        'draft' => 'bg-status-draft',
        'pending' => 'bg-status-pending',
        'approved' => 'bg-status-approved',
        'rejected' => 'bg-status-rejected',
        'in-progress' => 'bg-status-in-progress',
        'ready' => 'bg-status-ready',
    ];

    $dotColors = [
        'default' => 'bg-slate-400',
        'draft' => 'bg-status-draft',
        'pending' => 'bg-status-pending',
        'approved' => 'bg-status-approved',
        'rejected' => 'bg-status-rejected',
        'in-progress' => 'bg-status-in-progress',
        'ready' => 'bg-status-ready',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-lg border border-border bg-white px-4 py-3.5']) }}>
    <span class="absolute inset-y-0 left-0 w-1 {{ $accentColors[$variant] }}"></span>
    <div class="pl-2">
        <div class="flex items-center gap-1.5">
            <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $dotColors[$variant] }}"></span>
            <span class="text-[11px] font-semibold uppercase tracking-wider text-foreground-muted">{{ $label }}</span>
        </div>
        <div class="mt-1.5 text-3xl font-semibold text-foreground">{{ $value }}</div>
    </div>
</div>
