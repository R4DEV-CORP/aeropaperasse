<?php

use App\Models\BadgeRequest;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Demandes de badges'],
    ],
])]
#[Title('Demandes de badges')]
class extends Component
{
    use WithoutUrlPagination, WithPagination;

    public string $search = '';

    public ?string $selectedAirport = null;

    public ?string $selectedStatus = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedAirport(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function filterByStatus(?string $status): void
    {
        $this->selectedStatus = $status === $this->selectedStatus ? null : $status;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['selectedAirport', 'selectedStatus', 'search']);
        $this->resetPage();
    }

    #[Computed]
    public function draftBadgeRequests()
    {
        $query = BadgeRequest::with(['coworker', 'activityRequest.client'])
            ->where('status', 'draft');

        if (! auth()->user()->isAdmin()) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('client_id', auth()->user()->client_id);
            });
        }

        if (auth()->user()->isClient()) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('created_by', auth()->user()->id);
            });
        }

        return $query->orderBy('updated_at', 'desc')->take(50)->get();
    }

    #[Computed]
    public function statistics(): array
    {
        $query = BadgeRequest::query()->where('status', '!=', 'draft');

        if (! auth()->user()->isAdmin()) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('client_id', auth()->user()->client_id);
            });
        }

        return [
            'pending_rem' => (clone $query)->where('status', 'pending_rem')->count(),
            'pending_adp' => (clone $query)->where('status', 'pending_adp')->count(),
            'approved_adp' => (clone $query)->where('status', 'approved_adp')->count(),
            'pending_fabrication' => (clone $query)->where('status', 'pending_fabrication')->count(),
            'rejected_rem' => (clone $query)->where('status', 'rejected_rem')->count(),
            'rejected_adp' => (clone $query)->where('status', 'rejected_adp')->count(),
            'ready_for_delivery' => (clone $query)->where('status', 'ready_for_delivery')->count(),
            'delivered' => (clone $query)->where('status', 'delivered')->count(),
        ];
    }

    #[Computed]
    public function badgeRequests()
    {
        $query = BadgeRequest::with(['coworker', 'activityRequest.client'])
            ->where('status', '!=', 'draft');

        if (! auth()->user()->isAdmin()) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('client_id', auth()->user()->client_id);
            });
        }

        if (auth()->user()->isClient()) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('created_by', auth()->user()->id);
            });
        }

        if ($this->selectedAirport) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('airport', $this->selectedAirport);
            });
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        if (! empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('coworker', function ($q) use ($search) {
                    $q->where('firstname', 'like', '%'.$search.'%')
                        ->orWhere('lastname', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ['%'.$search.'%']);
                })->orWhereHas('activityRequest.client', function ($q) use ($search) {
                    $q->where('company_name', 'like', '%'.$search.'%');
                });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }
}; ?>

@php
    $statusMeta = [
        'pending_rem' => ['label' => 'En attente REM', 'variant' => 'pending'],
        'pending_adp' => ['label' => 'En attente ADP', 'variant' => 'pending'],
        'approved_adp' => ['label' => 'Approuvé ADP', 'variant' => 'approved'],
        'pending_fabrication' => ['label' => 'En fabrication', 'variant' => 'in-progress'],
        'rejected_rem' => ['label' => 'Dossier incomplet', 'variant' => 'rejected'],
        'rejected_adp' => ['label' => 'Rejeté ADP', 'variant' => 'rejected'],
        'ready_for_delivery' => ['label' => 'Prêt à être Remis', 'variant' => 'ready'],
        'delivered' => ['label' => 'Remis', 'variant' => 'default'],
        'terminated' => ['label' => 'Terminé', 'variant' => 'in-progress'],
    ];

    $airportMeta = [
        'CDG' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'ORY' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'LBG' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    ];
@endphp

<div class="space-y-6 p-8">
    {{-- Page header --}}
    <div class="space-y-1">
        <h1 class="text-2xl font-bold text-foreground">Demandes de badges</h1>
        <p class="text-sm text-foreground-muted">Gérez toutes les demandes de badges depuis cette interface.</p>
    </div>

    {{-- Stats grid --}}
    @php
        $statCards = [
            ['key' => 'pending_rem', 'variant' => 'pending', 'label' => 'En attente REM', 'ring' => 'ring-amber-500'],
            ['key' => 'pending_adp', 'variant' => 'pending', 'label' => 'En attente ADP', 'ring' => 'ring-amber-500'],
            ['key' => 'approved_adp', 'variant' => 'approved', 'label' => 'Approuvé ADP', 'ring' => 'ring-emerald-500'],
            ['key' => 'pending_fabrication', 'variant' => 'in-progress', 'label' => 'En fabrication', 'ring' => 'ring-violet-500'],
            ['key' => 'rejected_rem', 'variant' => 'rejected', 'label' => 'Dossier incomplet', 'ring' => 'ring-red-500'],
            ['key' => 'rejected_adp', 'variant' => 'rejected', 'label' => 'Rejeté ADP', 'ring' => 'ring-red-500'],
            ['key' => 'ready_for_delivery', 'variant' => 'ready', 'label' => 'Prêt à être Remis', 'ring' => 'ring-blue-500'],
            ['key' => 'delivered', 'variant' => 'default', 'label' => 'Remis', 'ring' => 'ring-slate-500'],
        ];
    @endphp
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($statCards as $card)
            <button
                type="button"
                wire:click="filterByStatus('{{ $card['key'] }}')"
                class="group rounded-lg text-left transition focus:outline-none {{ $this->selectedStatus === $card['key'] ? 'ring-2 '.$card['ring'] : '' }}"
            >
                <x-ui.stat-card
                    :variant="$card['variant']"
                    :label="$card['label']"
                    :value="$this->statistics[$card['key']]"
                    class="transition group-hover:border-slate-300 group-hover:shadow-sm"
                />
            </button>
        @endforeach
    </div>

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <x-ui.input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Rechercher une demande, un demandeur, une société..."
                >
                    <x-slot:leadingIcon>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </x-slot:leadingIcon>
                </x-ui.input>
            </div>

            @if (! auth()->user()->isClient())
                <x-ui.button variant="primary" :href="route('badge-requests.form')" wire:navigate>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nouvelle demande
                </x-ui.button>
            @endif
        </div>

        <x-ui.segmented
            name="selectedAirport"
            :value="$selectedAirport"
            :options="[
                ['value' => null, 'label' => 'Tous les aéroports'],
                ['value' => 'CDG', 'label' => 'CDG'],
                ['value' => 'ORY', 'label' => 'ORY'],
                ['value' => 'LBG', 'label' => 'LBG'],
            ]"
        />
    </div>

    {{-- Active filter chips --}}
    @if ($selectedStatus || $selectedAirport || ! empty($search))
        @php
            $chipClass = 'inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-foreground transition hover:bg-slate-200';
        @endphp
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-foreground-muted">Filtres :</span>

            @if (! empty($search))
                <button type="button" wire:click="$set('search', '')" class="{{ $chipClass }}">
                    Recherche : <span class="font-semibold">"{{ $search }}"</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3 text-foreground-muted">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif

            @if ($selectedStatus)
                <button type="button" wire:click="$set('selectedStatus', null)" class="{{ $chipClass }}">
                    Statut : <span class="font-semibold">{{ $statusMeta[$selectedStatus]['label'] ?? $selectedStatus }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3 text-foreground-muted">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif

            @if ($selectedAirport)
                <button type="button" wire:click="$set('selectedAirport', null)" class="{{ $chipClass }}">
                    Aéroport : <span class="font-semibold">{{ $selectedAirport }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3 text-foreground-muted">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif

            <button type="button" wire:click="resetFilters" class="text-xs font-medium text-foreground-muted underline transition hover:text-foreground">
                Tout réinitialiser
            </button>
        </div>
    @endif

    {{-- Drafts accordion --}}
    @php
        $draftsCount = $this->draftBadgeRequests->count();
        $hasDrafts = $draftsCount > 0;
    @endphp
    <div
        x-data="{ open: false }"
        x-cloak
        class="overflow-hidden rounded border border-border bg-white shadow-sm"
    >
        @if ($hasDrafts)
            <button
                type="button"
                @click="open = !open"
                :aria-expanded="open"
                class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
            >
                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md bg-slate-100 text-foreground-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </span>

                <div class="flex flex-shrink-0 items-baseline gap-2">
                    <h2 class="text-sm font-semibold text-foreground">Brouillons</h2>
                    <x-ui.badge variant="draft" :dot="false">{{ $draftsCount }}</x-ui.badge>
                    <span class="hidden text-xs text-foreground-muted sm:inline">— demandes en cours de rédaction</span>
                </div>

                <div class="ml-auto flex flex-shrink-0 items-center gap-2 text-xs font-medium text-foreground-muted">
                    <span x-text="open ? 'Replier' : 'Ouvrir'"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 transition-transform duration-200" :class="open && 'rotate-180'">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>
            </button>
        @else
            <div class="flex items-center gap-3 px-4 py-3">
                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md bg-slate-50 text-foreground-subtle">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </span>
                <div class="flex flex-shrink-0 items-baseline gap-2">
                    <h2 class="text-sm font-semibold text-foreground-muted">Brouillons</h2>
                    <span class="text-xs text-foreground-subtle">Aucune demande en cours de rédaction</span>
                </div>
            </div>
        @endif

        @if ($hasDrafts)
            <div x-show="open" x-collapse class="border-t border-border">
                <div class="max-h-[360px] overflow-y-auto">
                    <x-ui.table>
                        <thead class="sticky top-0 z-10 bg-white">
                            <tr>
                                <th>Demandeur</th>
                                <th>Contact</th>
                                <th>Société</th>
                                <th>Aéroport</th>
                                <th>Modifié</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->draftBadgeRequests as $draft)
                                @php
                                    $coworker = $draft->coworker;
                                    $fullName = trim(($coworker->firstname ?? '').' '.($coworker->lastname ?? ''));
                                    $email = $coworker->email ?? null;
                                    $phone = $coworker->phone ?? null;
                                    $company = $draft->activityRequest->client->company_name ?? null;
                                    $airport = $draft->activityRequest->airport ?? null;
                                @endphp
                                <tr wire:key="draft-{{ $draft->id }}" class="transition hover:bg-slate-50">
                                    <td>
                                        @if ($fullName !== '')
                                            <div class="font-medium text-foreground">{{ $fullName }}</div>
                                        @else
                                            <div class="font-medium italic text-foreground-subtle">Demandeur non renseigné</div>
                                        @endif
                                        <div class="text-xs text-foreground-muted">
                                            Créé le {{ $draft->created_at->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-foreground">{{ $email ?: '—' }}</div>
                                        <div class="text-xs text-foreground-muted">{{ $phone ?: '—' }}</div>
                                    </td>
                                    <td>
                                        @if ($company)
                                            <span class="text-foreground">{{ $company }}</span>
                                        @else
                                            <span class="text-foreground-subtle">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($airport)
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                                {{ $airport }}
                                            </span>
                                        @else
                                            <span class="text-foreground-subtle">—</span>
                                        @endif
                                    </td>
                                    <td class="text-foreground-muted">
                                        <div>{{ $draft->updated_at->diffForHumans() }}</div>
                                        <div class="text-xs text-foreground-subtle">{{ $draft->updated_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="text-right">
                                        <a
                                            href="{{ route('badge-requests.form', ['badgeRequestId' => $draft->id]) }}"
                                            wire:navigate
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-foreground transition hover:text-brand"
                                        >
                                            Reprendre
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                </div>
            </div>
        @endif
    </div>

    {{-- Table card --}}
    <x-ui.card padding="none" class="relative overflow-hidden">
        <div
            wire:loading.delay.short
            wire:target="search,selectedAirport,selectedStatus,filterByStatus,resetFilters"
            class="absolute inset-x-0 top-0 z-10 h-0.5 overflow-hidden bg-blue-100"
        >
            <div class="h-full w-1/3 animate-pulse bg-blue-500"></div>
        </div>

        <div class="border-b border-border px-5 py-4">
            <h2 class="text-base font-semibold text-foreground">Demandes récentes</h2>
            <p class="mt-0.5 text-xs text-foreground-muted">{{ $this->badgeRequests->total() }} {{ $this->badgeRequests->total() > 1 ? 'demandes' : 'demande' }}</p>
        </div>

        <x-ui.table>
            <thead>
                <tr>
                    <th>N° demande d'activité</th>
                    <th>Demandeur</th>
                    <th>Contact</th>
                    <th>Statut</th>
                    <th>Date de création</th>
                    <th>Aéroport</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->badgeRequests as $badgeRequest)
                    @php
                        $status = $statusMeta[$badgeRequest->status] ?? ['label' => $badgeRequest->status, 'variant' => 'default'];
                        $airport = $badgeRequest->activityRequest->airport ?? null;
                    @endphp
                    <tr wire:key="badge-request-{{ $badgeRequest->id }}">
                        <td>
                            @if ($badgeRequest->activityRequest)
                                <span class="font-mono text-xs text-foreground-muted">#{{ $badgeRequest->activityRequest->id }}</span>
                            @else
                                <span class="text-foreground-subtle">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="font-medium text-foreground">{{ $badgeRequest->coworker->firstname }} {{ $badgeRequest->coworker->lastname }}</div>
                            <div class="text-xs text-foreground-muted">{{ $badgeRequest->activityRequest->client->company_name ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="text-foreground">{{ $badgeRequest->coworker->email }}</div>
                            <div class="text-xs text-foreground-muted">{{ $badgeRequest->coworker->phone }}</div>
                        </td>
                        <td>
                            <x-ui.badge :variant="$status['variant']">{{ $status['label'] }}</x-ui.badge>
                        </td>
                        <td class="text-foreground-muted">{{ $badgeRequest->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if ($airport)
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                    {{ $airport }}
                                </span>
                            @else
                                <span class="text-foreground-subtle">—</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <x-ui.dropdown align="right">
                                <x-slot:trigger>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-foreground-muted transition hover:bg-slate-100 hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                                        aria-label="Actions"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                        </svg>
                                    </button>
                                </x-slot:trigger>

                                <x-ui.dropdown-item wire:click="$dispatch('view-badge-request', { id: {{ $badgeRequest->id }} })">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Voir les détails
                                </x-ui.dropdown-item>

                                <x-ui.dropdown-item wire:click="$dispatch('download-badge-request', { id: {{ $badgeRequest->id }} })">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    Télécharger documents
                                </x-ui.dropdown-item>
                            </x-ui.dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-foreground-muted">
                            <div class="py-12">Aucune demande à afficher.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        @if ($this->badgeRequests->hasPages())
            <div class="border-t border-border px-5 py-3">
                {{ $this->badgeRequests->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
