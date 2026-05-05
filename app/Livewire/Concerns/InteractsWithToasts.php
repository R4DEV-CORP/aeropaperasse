<?php

namespace App\Livewire\Concerns;

trait InteractsWithToasts
{
    /**
     * Dispatche un toast vers le toast-stack global.
     *
     * @param  'info'|'success'|'warning'|'danger'  $variant
     */
    public function toast(string $message, string $variant = 'info', ?string $title = null, int $ttl = 6000): void
    {
        $this->dispatch(
            'toast',
            message: $message,
            variant: $variant,
            title: $title,
            ttl: $ttl,
        );
    }
}
