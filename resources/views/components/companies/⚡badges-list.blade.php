<?php

use App\Models\Badge;
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

        $query = Badge::with(['coworker', 'badgeRequest.coworker'])
            ->where(function ($q) use ($companyId) {
                $q->where('client_id', $companyId)
                    ->orWhereHas('badgeRequest.activityRequest', function ($q2) use ($companyId) {
                        $q2->where('client_id', $companyId);
                    });
            });

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('badge_number', 'like', $term)
                    ->orWhere('airport', 'like', $term)
                    ->orWhere('status', 'like', $term)
                    ->orWhereHas('coworker', function ($q2) use ($term) {
                        $q2->where('firstname', 'like', $term)
                            ->orWhere('lastname', 'like', $term)
                            ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", [$term]);
                    })
                    ->orWhereHas('badgeRequest.coworker', function ($q2) use ($term) {
                        $q2->where('firstname', 'like', $term)
                            ->orWhere('lastname', 'like', $term)
                            ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", [$term]);
                    });
            });
        }

        return $query->orderByRaw("FIELD(status, 'active', 'expiring_soon', 'expired', 'returned', 'lost')")
            ->orderBy('expiry_date', 'asc');
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
        'active' => ['label' => 'Actif', 'variant' => 'approved'],
        'expiring_soon' => ['label' => 'Expire bientôt', 'variant' => 'pending'],
        'expired' => ['label' => 'Expiré', 'variant' => 'rejected'],
        'returned' => ['label' => 'Restitué', 'variant' => 'default'],
        'lost' => ['label' => 'Perdu', 'variant' => 'rejected'],
    ];

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
                <h2 class="text-base font-semibold text-foreground">Badges</h2>
                <x-ui.badge variant="default" :dot="false">{{ $this->totalCount }}</x-ui.badge>
            </div>
            <a
                href="{{ route('badge-management.index') }}"
                wire:navigate
                class="text-xs font-semibold text-accent transition hover:text-accent-content"
            >
                Voir tout
            </a>
        </div>
        <x-ui.input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="Rechercher un badge, un porteur…"
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
            {{ trim($search) !== '' ? 'Aucun résultat.' : 'Aucun badge.' }}
        </div>
    @else
        <ul class="divide-y divide-border">
            @foreach ($this->items as $badge)
                @php
                    $status = $statusMeta[$badge->status] ?? ['label' => $badge->status, 'variant' => 'default'];
                    $bearer = $badge->coworker ?? $badge->badgeRequest?->coworker;
                    $bearerName = $bearer ? trim($bearer->firstname.' '.$bearer->lastname) : null;
                @endphp
                <li wire:key="badge-{{ $badge->id }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs text-foreground">{{ $badge->badge_number ?: '—' }}</span>
                            <x-ui.badge :variant="$status['variant']" :dot="false">{{ $status['label'] }}</x-ui.badge>
                            @if ($badge->airport)
                                <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$badge->airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                    {{ $badge->airport }}
                                </span>
                            @endif
                        </div>
                        <div class="mt-0.5 truncate text-xs text-foreground-muted">
                            {{ $bearerName ?: 'Sans porteur' }}
                            @if ($badge->expiry_date)
                                · Expire le {{ $badge->expiry_date->format('d/m/Y') }}
                            @endif
                        </div>
                    </div>
                    <a
                        href="{{ route('badge-management.show', ['badgeId' => $badge->id]) }}"
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
