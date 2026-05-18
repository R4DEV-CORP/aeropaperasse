<?php

use App\Models\BadgeRequest;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Demandes de badges', 'href' => '/badge-requests'],
        ['label' => 'Formulaire'],
    ],
])]
#[Title('Demande de badge')]
class extends Component
{
    public ?int $badgeRequestId = null;

    public string $mode = 'create';

    public function mount(?int $badgeRequestId = null): void
    {
        if (auth()->user()->isClient()) {
            $this->redirect(route('companies.show', ['companyId' => auth()->user()->client_id]), navigate: true);

            return;
        }

        $this->badgeRequestId = $badgeRequestId;

        if ($this->badgeRequestId) {
            $status = BadgeRequest::whereKey($this->badgeRequestId)->value('status');

            $this->mode = match ($status) {
                'rejected_rem', 'rejected_adp' => 'correction',
                'draft' => 'draft',
                default => 'create',
            };
        }
    }
}; ?>

@php
    $pageTitle = match ($mode) {
        'correction' => 'Corriger la demande de badge',
        'draft' => 'Reprendre le brouillon',
        default => 'Nouvelle demande de badge',
    };
@endphp

<div class="flex min-h-full flex-col">
    {{-- Sous-header : Retour / titre --}}
    <div class="px-4 pt-8 pb-4 sm:px-6 lg:px-8">
        <div class="mx-auto flex w-full max-w-3xl items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('badge-requests.index') }}"
                    wire:navigate
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-foreground-muted transition hover:text-foreground"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Retour
                </a>
                <span class="text-foreground-subtle">/</span>
                <h1 class="text-base font-semibold text-foreground">{{ $pageTitle }}</h1>
            </div>
        </div>
    </div>

    {{-- Le formulaire gère son propre centrage interne et sa barre d'actions sticky --}}
    <livewire:badge-requests.create-form :badge-request-id="$badgeRequestId" />
</div>
