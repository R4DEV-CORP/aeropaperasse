@blaze

@props([
    'variant' => 'default',
    'dot' => true,
])

@php
    $base = 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset';

    $variants = [
        'default' => 'bg-slate-50 text-foreground-muted ring-border',
        'draft' => 'bg-slate-50 text-slate-700 ring-slate-200',
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'rejected' => 'bg-red-50 text-red-700 ring-red-200',
        'in-progress' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'ready' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'info' => 'bg-sky-50 text-sky-700 ring-sky-200',
    ];

    $dotColors = [
        'default' => 'bg-slate-400',
        'draft' => 'bg-status-draft',
        'pending' => 'bg-status-pending',
        'approved' => 'bg-status-approved',
        'rejected' => 'bg-status-rejected',
        'in-progress' => 'bg-status-in-progress',
        'ready' => 'bg-status-ready',
        'info' => 'bg-sky-500',
    ];

    $classes = "$base {$variants[$variant]}";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotColors[$variant] }}"></span>
    @endif
    {{ $slot }}
</span>
