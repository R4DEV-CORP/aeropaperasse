<?php

use App\Models\Coworker;
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
        $query = Coworker::with('user')->where('client_id', $this->companyId);

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('firstname', 'like', $term)
                    ->orWhere('lastname', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        return $query->orderBy('has_leave')->orderBy('lastname')->orderBy('firstname');
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
    $roleMeta = [
        'rem_admin' => ['label' => 'Administrateur REM', 'variant' => 'rejected'],
        'rem_super_admin' => ['label' => 'Super admin REM', 'variant' => 'rejected'],
        'sclient' => ['label' => 'SClient', 'variant' => 'in-progress'],
        'aclient' => ['label' => 'AClient', 'variant' => 'in-progress'],
        'client' => ['label' => 'Client', 'variant' => 'ready'],
    ];
@endphp

<x-ui.card padding="none">
    <div class="space-y-3 border-b border-border px-5 py-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <h2 class="text-base font-semibold text-foreground">Utilisateurs & Collaborateurs</h2>
                <x-ui.badge variant="default" :dot="false">{{ $this->totalCount }}</x-ui.badge>
            </div>
            <a
                href="{{ route('coworkers.index') }}"
                wire:navigate
                class="text-xs font-semibold text-accent transition hover:text-accent-content"
            >
                Voir tout
            </a>
        </div>
        <x-ui.input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="Rechercher un collaborateur…"
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
            {{ trim($search) !== '' ? 'Aucun résultat.' : 'Aucun collaborateur.' }}
        </div>
    @else
        <ul class="divide-y divide-border">
            @foreach ($this->items as $coworker)
                @php
                    $role = $coworker->user
                        ? ($roleMeta[$coworker->user->role] ?? ['label' => $coworker->user->role, 'variant' => 'default'])
                        : ['label' => 'Collaborateur', 'variant' => 'draft'];
                @endphp
                <li wire:key="coworker-{{ $coworker->id }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-medium text-foreground">{{ $coworker->firstname }} {{ $coworker->lastname }}</span>
                            <x-ui.badge :variant="$role['variant']" :dot="false">{{ $role['label'] }}</x-ui.badge>
                            @if ($coworker->has_leave)
                                <x-ui.badge variant="rejected" :dot="false">{{ $coworker->departure_date ? 'Parti le '.$coworker->departure_date->format('d/m/Y') : 'Parti' }}</x-ui.badge>
                            @endif
                        </div>
                        <div class="mt-0.5 truncate text-xs text-foreground-muted">
                            {{ $coworker->email ?: '—' }}{{ $coworker->phone ? ' · '.$coworker->phone : '' }}
                        </div>
                    </div>
                    <a
                        href="{{ route('coworkers.show', ['coworkerId' => $coworker->id]) }}"
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
