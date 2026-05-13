<?php

use App\Models\BadgeRequest;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public int $companyId;

    public string $search = '';

    public int $perPage = 5;

    public function mount(int $companyId): void
    {
        $this->companyId = $companyId;
    }

    public function updatedSearch(): void
    {
        $this->perPage = 5;
    }

    public function showMore(): void
    {
        $this->perPage += 5;
    }

    private function baseQuery()
    {
        $companyId = $this->companyId;

        $query = BadgeRequest::with(['coworker', 'activityRequest'])
            ->whereHas('activityRequest', function ($q) use ($companyId) {
                $q->where('client_id', $companyId);
            });

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('coworker', function ($q2) use ($term) {
                    $q2->where('firstname', 'like', $term)
                        ->orWhere('lastname', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", [$term]);
                })->orWhere('id', 'like', $term)
                    ->orWhere('status', 'like', $term);
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->baseQuery()->count();
    }

    #[Computed]
    public function items()
    {
        return $this->baseQuery()->limit($this->perPage)->get();
    }
}; ?>

@php
    $statusMeta = [
        'draft' => ['label' => 'Brouillon', 'variant' => 'draft'],
        'pending_rem' => ['label' => 'En attente REM', 'variant' => 'pending'],
        'rejected_rem' => ['label' => 'Rejetée REM', 'variant' => 'rejected'],
        'pending_adp' => ['label' => 'En attente ADP', 'variant' => 'pending'],
        'approved_adp' => ['label' => 'Approuvée ADP', 'variant' => 'approved'],
        'rejected_adp' => ['label' => 'Rejetée ADP', 'variant' => 'rejected'],
        'pending_fabrication' => ['label' => 'En fabrication', 'variant' => 'in-progress'],
        'ready_for_delivery' => ['label' => 'Prête', 'variant' => 'ready'],
        'delivered' => ['label' => 'Remise', 'variant' => 'approved'],
        'terminated' => ['label' => 'Terminée', 'variant' => 'default'],
    ];
@endphp

<x-ui.card padding="none">
    <div class="space-y-3 border-b border-border px-5 py-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <h2 class="text-base font-semibold text-foreground">Demandes de badges</h2>
                <x-ui.badge variant="default" :dot="false">{{ $this->totalCount }}</x-ui.badge>
            </div>
            <a
                href="{{ route('badge-requests.index') }}"
                wire:navigate
                class="text-xs font-semibold text-accent transition hover:text-accent-content"
            >
                Voir tout
            </a>
        </div>
        <x-ui.input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="Rechercher un collaborateur, un statut…"
        >
            <x-slot:leadingIcon>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </x-slot:leadingIcon>
        </x-ui.input>
    </div>

    @if ($this->items->isEmpty())
        <div class="px-5 py-10 text-center text-sm text-foreground-muted">
            {{ trim($search) !== '' ? 'Aucun résultat.' : 'Aucune demande de badge.' }}
        </div>
    @else
        <ul class="divide-y divide-border">
            @foreach ($this->items as $br)
                @php
                    $status = $statusMeta[$br->status] ?? ['label' => $br->status, 'variant' => 'default'];
                    $coworkerName = $br->coworker ? trim($br->coworker->firstname.' '.$br->coworker->lastname) : null;
                @endphp
                <li wire:key="br-{{ $br->id }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs text-foreground-muted">#{{ $br->id }}</span>
                            <span class="truncate font-medium text-foreground">{{ $coworkerName ?: '—' }}</span>
                            <x-ui.badge :variant="$status['variant']" :dot="false">{{ $status['label'] }}</x-ui.badge>
                        </div>
                        <div class="mt-0.5 truncate text-xs text-foreground-muted">
                            Demande d'activité #{{ $br->activity_request_id }} · {{ $br->created_at?->format('d/m/Y') }}
                        </div>
                    </div>
                    <a
                        href="{{ route('badge-requests.show', ['badgeRequestId' => $br->id]) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex flex-shrink-0 items-center gap-1 text-xs font-semibold text-foreground transition hover:text-accent"
                    >
                        Voir
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6 21 12m0 0-7.5 6M21 12H3" />
                        </svg>
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($this->totalCount > $this->perPage)
            <div class="border-t border-border bg-slate-50/50 px-5 py-3 text-center">
                <button
                    type="button"
                    wire:click="showMore"
                    wire:loading.attr="disabled"
                    wire:target="showMore"
                    class="text-xs font-semibold text-accent transition hover:text-accent-content disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="showMore">Afficher plus ({{ $this->totalCount - $this->perPage }} restants)</span>
                    <span wire:loading wire:target="showMore">Chargement…</span>
                </button>
            </div>
        @endif
    @endif
</x-ui.card>
