<?php

use App\Models\CoworkerTraining;
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

        $query = CoworkerTraining::with(['coworker', 'training'])
            ->whereHas('coworker', function ($q) use ($companyId) {
                $q->where('client_id', $companyId);
            });

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('coworker', function ($q2) use ($term) {
                    $q2->where('firstname', 'like', $term)
                        ->orWhere('lastname', 'like', $term)
                        ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", [$term]);
                })->orWhereHas('training', function ($q2) use ($term) {
                    $q2->where('title', 'like', $term);
                })->orWhere('airport', 'like', $term);
            });
        }

        return $query->orderByRaw('expires_at IS NULL, expires_at ASC');
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
    $airportMeta = [
        'CDG' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'ORY' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'LBG' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    ];
@endphp

<x-ui.card padding="none">
    <div class="space-y-3 border-b border-border px-5 py-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <h2 class="text-base font-semibold text-foreground">Formations</h2>
                <x-ui.badge variant="default" :dot="false">{{ $this->totalCount }}</x-ui.badge>
            </div>
            <a
                href="{{ route('trainings.show', ['companyId' => $companyId]) }}"
                wire:navigate
                class="text-xs font-semibold text-accent transition hover:text-accent-content"
            >
                Voir tout
            </a>
        </div>
        <x-ui.input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="Rechercher une formation, un collaborateur…"
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
            {{ trim($search) !== '' ? 'Aucun résultat.' : 'Aucune formation attribuée.' }}
        </div>
    @else
        <ul class="divide-y divide-border">
            @foreach ($this->items as $ct)
                @php
                    $expiresAt = $ct->expires_at;
                    $isExpired = $expiresAt && $expiresAt->isPast();
                    $isSoon = $expiresAt && ! $isExpired && $expiresAt->lt(now()->addMonths(6));
                    if ($isExpired) {
                        $statusLabel = 'Expirée';
                        $statusVariant = 'rejected';
                    } elseif ($isSoon) {
                        $statusLabel = 'Expire bientôt';
                        $statusVariant = 'pending';
                    } else {
                        $statusLabel = $expiresAt ? 'Active' : 'À vie';
                        $statusVariant = 'approved';
                    }
                @endphp
                <li wire:key="ct-{{ $ct->id }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="truncate font-medium text-foreground">{{ $ct->training?->title ?? '—' }}</span>
                            <x-ui.badge :variant="$statusVariant" :dot="false">{{ $statusLabel }}</x-ui.badge>
                            @if ($ct->airport)
                                <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$ct->airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                    {{ $ct->airport }}
                                </span>
                            @endif
                        </div>
                        <div class="mt-0.5 truncate text-xs text-foreground-muted">
                            {{ $ct->coworker?->firstname }} {{ $ct->coworker?->lastname }}
                            @if ($expiresAt)
                                · Expire le {{ $expiresAt->format('d/m/Y') }}
                            @endif
                        </div>
                    </div>
                    @if ($ct->coworker_id)
                        <a
                            href="{{ route('coworkers.show', ['coworkerId' => $ct->coworker_id]) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex flex-shrink-0 items-center gap-1 text-xs font-semibold text-foreground transition hover:text-accent"
                        >
                            Voir
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6 21 12m0 0-7.5 6M21 12H3" />
                            </svg>
                        </a>
                    @endif
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
