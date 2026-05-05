<?php

use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    /** @var array<int, array{id:string,message:string,variant:string,title:?string,ttl:int}> */
    public array $toasts = [];

    public function mount(): void
    {
        $flash = session()->pull('toast');

        if (! $flash) {
            return;
        }

        $payloads = is_array($flash) && array_is_list($flash) ? $flash : [$flash];

        foreach ($payloads as $payload) {
            if (is_array($payload)) {
                $this->push($payload);
            }
        }
    }

    #[On('toast')]
    public function onToast(string $message, string $variant = 'info', ?string $title = null, int $ttl = 6000): void
    {
        $this->push([
            'message' => $message,
            'variant' => $variant,
            'title' => $title,
            'ttl' => $ttl,
        ]);
    }

    public function dismiss(string $id): void
    {
        $this->toasts = array_values(array_filter(
            $this->toasts,
            fn (array $toast): bool => $toast['id'] !== $id,
        ));
    }

    /**
     * @param  array{message?:string,variant?:string,title?:?string,ttl?:int}  $payload
     */
    protected function push(array $payload): void
    {
        $variant = $payload['variant'] ?? 'info';

        if (! in_array($variant, ['info', 'success', 'warning', 'danger'], true)) {
            $variant = 'info';
        }

        $this->toasts[] = [
            'id' => (string) Str::uuid(),
            'message' => (string) ($payload['message'] ?? ''),
            'variant' => $variant,
            'title' => isset($payload['title']) ? (string) $payload['title'] : null,
            'ttl' => max(1000, (int) ($payload['ttl'] ?? 6000)),
        ];
    }
}; ?>

<div
    aria-live="polite"
    aria-atomic="false"
    class="pointer-events-none fixed inset-x-0 bottom-4 z-50 flex flex-col items-end gap-2 px-4 sm:left-auto sm:right-4 sm:px-0"
>
    @foreach ($toasts as $toast)
        <div
            wire:key="toast-{{ $toast['id'] }}"
            x-data="{ visible: false }"
            x-init="
                $nextTick(() => visible = true);
                setTimeout(() => $wire.dismiss(@js($toast['id'])), {{ $toast['ttl'] }});
            "
            x-show="visible"
            x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition duration-150 ease-in"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-cloak
        >
            <x-ui.toast
                :variant="$toast['variant']"
                :title="$toast['title']"
                wire:click="dismiss('{{ $toast['id'] }}')"
            >
                {{ $toast['message'] }}
            </x-ui.toast>
        </div>
    @endforeach
</div>
