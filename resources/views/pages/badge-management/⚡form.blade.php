<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Suivi des badges', 'href' => '/badge-management'],
        ['label' => 'Nouveau badge'],
    ],
])]
#[Title('Nouveau badge')]
class extends Component
{
    public string $mode = 'linked';

    public function mount(?string $mode = null): void
    {
        if (auth()->user()->isClient()) {
            $this->redirect(route('clients.view', ['slug' => auth()->user()->client->slug]));

            return;
        }

        $this->mode = in_array($mode, ['linked', 'standalone'], true) ? $mode : 'linked';
    }
}; ?>

<div class="flex min-h-full flex-col">
    {{-- Sous-header : Retour / titre + switch de mode --}}
    <div class="px-4 pt-8 pb-4 sm:px-6 lg:px-8">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('badge-management.index') }}"
                    wire:navigate
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-foreground-muted transition hover:text-foreground"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Retour
                </a>
                <span class="text-foreground-subtle">/</span>
                <h1 class="text-base font-semibold text-foreground">
                    {{ $mode === 'linked' ? 'Lier un badge à une demande' : 'Créer un badge indépendant' }}
                </h1>
            </div>

            {{-- Switch de mode --}}
            <div class="inline-flex rounded-md border border-border bg-white p-0.5 text-xs font-medium">
                <a
                    href="{{ route('badge-management.form', ['mode' => 'linked']) }}"
                    wire:navigate
                    class="rounded px-3 py-1.5 transition {{ $mode === 'linked' ? 'bg-accent text-accent-foreground' : 'text-foreground-muted hover:text-foreground' }}"
                >
                    Lier à une demande
                </a>
                <a
                    href="{{ route('badge-management.form', ['mode' => 'standalone']) }}"
                    wire:navigate
                    class="rounded px-3 py-1.5 transition {{ $mode === 'standalone' ? 'bg-accent text-accent-foreground' : 'text-foreground-muted hover:text-foreground' }}"
                >
                    Indépendant
                </a>
            </div>
        </div>
    </div>

    {{-- Le formulaire gère son propre centrage interne et sa barre d'actions sticky --}}
    @if ($mode === 'linked')
        <livewire:badge-management.create-linked-form :key="'create-linked-form'" />
    @else
        <livewire:badge-management.create-standalone-form :key="'create-standalone-form'" />
    @endif
</div>
