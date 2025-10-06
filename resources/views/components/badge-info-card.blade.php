@pure

@props([
    'title' => null,
    'value' => null,
    'bgColor' => null,
])

<div class="p-4 rounded-lg bg-{{ $bgColor }}">
    <flux:heading>{{ $title }}</flux:heading>
    <flux:text class="text-xl text-black font-bold">{{ $value }}</flux:text>
</div>