@blaze

@php
    $tableClass = 'w-full text-left text-sm
        [&_thead_th]:px-4 [&_thead_th]:py-3 [&_thead_th]:text-[11px] [&_thead_th]:font-semibold [&_thead_th]:uppercase [&_thead_th]:tracking-wider [&_thead_th]:text-foreground-subtle
        [&_tbody_td]:px-4 [&_tbody_td]:py-4 [&_tbody_td]:align-middle [&_tbody_td]:text-foreground
        [&_tbody_tr]:border-t [&_tbody_tr]:border-border';
@endphp

<div {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <table class="{{ $tableClass }}">
        {{ $slot }}
    </table>
</div>
