<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Badge;
use App\Models\Client;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Suivi des badges'],
    ],
])]
#[Title('Suivi des badges')]
class extends Component
{
    use InteractsWithToasts, WithoutUrlPagination, WithPagination;

    public string $search = '';

    public ?string $selectedAirport = null;

    public ?string $selectedStatus = null;

    public ?int $selectedClientId = null;

    public function mount(): void
    {
        $this->checkAndUpdateExpiredBadges();
    }

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

    public function updatedSelectedClientId(): void
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
        $this->reset(['selectedAirport', 'selectedStatus', 'selectedClientId', 'search']);
        $this->resetPage();
    }

    #[Computed]
    public function clients()
    {
        if (! auth()->user()->isTenantManager()) {
            return collect();
        }

        return Client::orderBy('company_name')->get();
    }

    private function baseQuery()
    {
        $query = Badge::with([
            'client',
            'coworker',
            'badgeRequest.coworker',
            'badgeRequest.activityRequest.client',
        ]);

        if (! auth()->user()->isTenantManager()) {
            $query->where(function ($q) {
                $q->whereHas('badgeRequest.activityRequest', function ($s) {
                    $s->where('client_id', auth()->user()->contextualClientId());
                })->orWhere('client_id', auth()->user()->contextualClientId());
            });
        }

        if ($this->selectedClientId && auth()->user()->isTenantManager()) {
            $clientId = $this->selectedClientId;
            $query->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                    ->orWhereHas('badgeRequest.activityRequest', function ($s) use ($clientId) {
                        $s->where('client_id', $clientId);
                    });
            });
        }

        if (auth()->user()->isClient()) {
            $query->where(function ($q) {
                $q->whereHas('badgeRequest.coworker', function ($s) {
                    $s->where('user_id', auth()->user()->id);
                })->orWhereHas('coworker', function ($s) {
                    $s->where('user_id', auth()->user()->id);
                });
            });
        }

        return $query;
    }

    #[Computed]
    public function statistics(): array
    {
        $counts = $this->baseQuery()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'total' => $counts->sum(),
            'active' => $counts->get('active', 0),
            'expired' => $counts->get('expired', 0),
            'returned' => $counts->get('returned', 0),
            'not_returned' => $counts->get('not_returned', 0),
        ];
    }

    #[Computed]
    public function badges()
    {
        $query = $this->baseQuery();

        if ($this->selectedAirport) {
            $query->where('airport', $this->selectedAirport);
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        if (! empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('coworker', function ($s) use ($search) {
                    $s->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$search}%"]);
                })->orWhereHas('badgeRequest.coworker', function ($s) use ($search) {
                    $s->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$search}%"]);
                })->orWhereHas('client', function ($s) use ($search) {
                    $s->where('company_name', 'like', "%{$search}%");
                })->orWhereHas('badgeRequest.activityRequest.client', function ($s) use ($search) {
                    $s->where('company_name', 'like', "%{$search}%");
                });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    #[On('badge-updated')]
    public function refreshList(): void
    {
        unset($this->statistics, $this->badges);
    }

    public function notReturnedBadge(int $badgeId): void
    {
        if (! auth()->user()->isTenantManager()) {
            return;
        }

        $badge = Badge::find($badgeId);
        if ($badge === null || $badge->status !== 'expired') {
            return;
        }

        $badge->previous_status = $badge->status;
        $badge->status = 'not_returned';
        $badge->returned_at = null;
        $badge->save();

        $this->refreshList();
        $this->toast('Badge marqué comme non restitué.', 'success', 'Statut mis à jour');
    }

    /**
     * Bascule en `expired` les badges actifs dont la date d'expiration est passée.
     * Appelé au mount() — une seule fois par chargement de page.
     */
    private function checkAndUpdateExpiredBadges(): void
    {
        $today = now()->toDateString();

        Badge::query()
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $today)
            ->each(function (Badge $badge) {
                $badge->previous_status = $badge->status;
                $badge->status = 'expired';
                $badge->save();
            });
    }
}; ?>

@php
    $statusMeta = [
        'active' => ['label' => 'Actif', 'variant' => 'approved'],
        'expired' => ['label' => 'Expiré', 'variant' => 'rejected'],
        'returned' => ['label' => 'Restitué', 'variant' => 'ready'],
        'not_returned' => ['label' => 'Non restitué', 'variant' => 'pending'],
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
        <h1 class="text-2xl font-bold text-foreground">Suivi des badges</h1>
        <p class="text-sm text-foreground-muted">Gérez tous les badges depuis cette interface.</p>
    </div>

    {{-- Stats grid --}}
    @php
        $statCards = [
            ['key' => 'active', 'variant' => 'approved', 'label' => 'Actifs', 'ring' => 'ring-emerald-500'],
            ['key' => 'expired', 'variant' => 'rejected', 'label' => 'Expirés', 'ring' => 'ring-red-500'],
            ['key' => 'returned', 'variant' => 'ready', 'label' => 'Restitués', 'ring' => 'ring-blue-500'],
            ['key' => 'not_returned', 'variant' => 'pending', 'label' => 'Non restitués', 'ring' => 'ring-amber-500'],
        ];
    @endphp
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-ui.stat-card
            variant="default"
            label="Total"
            :value="$this->statistics['total']"
        />

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
                    placeholder="Rechercher par nom de collaborateur ou de société..."
                >
                    <x-slot:leadingIcon>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </x-slot:leadingIcon>
                </x-ui.input>
            </div>

            @if (auth()->user()->isTenantManager())
                @php
                    $clientOptions = [['value' => null, 'label' => 'Toutes les sociétés']];
                    foreach ($this->clients as $c) {
                        $clientOptions[] = [
                            'value' => $c->id,
                            'label' => $c->company_name,
                            'hint' => $c->siret_number,
                        ];
                    }
                @endphp
                <div class="sm:w-64">
                    <x-ui.select
                        :value="$selectedClientId"
                        wire:model.live="selectedClientId"
                        :options="$clientOptions"
                        placeholder="Toutes les sociétés"
                        searchable
                        search-placeholder="Filtrer par société…"
                    />
                </div>
            @endif

            @if (! auth()->user()->isClient())
                <x-ui.split-button variant="primary">
                    <x-slot:label>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Ajouter un badge
                    </x-slot:label>

                    <x-ui.dropdown-item :href="route('badge-management.form', ['mode' => 'linked'])" wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                        </svg>
                        Lier à une demande de badge
                    </x-ui.dropdown-item>
                    <x-ui.dropdown-item :href="route('badge-management.form', ['mode' => 'standalone'])" wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Créer indépendamment
                    </x-ui.dropdown-item>
                </x-ui.split-button>
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
    @if ($selectedStatus || $selectedAirport || $selectedClientId || ! empty($search))
        @php
            $chipClass = 'inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-foreground transition hover:bg-slate-200';
            $selectedClientLabel = null;
            if ($selectedClientId) {
                $selectedClientLabel = optional($this->clients->firstWhere('id', $selectedClientId))->company_name;
            }
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

            @if ($selectedClientId && $selectedClientLabel)
                <button type="button" wire:click="$set('selectedClientId', null)" class="{{ $chipClass }}">
                    Société : <span class="font-semibold">{{ $selectedClientLabel }}</span>
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

    {{-- Table card --}}
    <x-ui.card padding="none" class="relative overflow-hidden">
        <div
            wire:loading.delay.short
            wire:target="search,selectedAirport,selectedStatus,selectedClientId,filterByStatus,resetFilters"
            class="absolute inset-x-0 top-0 z-10 h-0.5 overflow-hidden bg-blue-100"
        >
            <div class="h-full w-1/3 animate-pulse bg-blue-500"></div>
        </div>

        <div class="border-b border-border px-5 py-4">
            <h2 class="text-base font-semibold text-foreground">Liste des badges</h2>
            <p class="mt-0.5 text-xs text-foreground-muted">{{ $this->badges->total() }} {{ $this->badges->total() > 1 ? 'badges' : 'badge' }}</p>
        </div>

        <x-ui.table>
            <thead>
                <tr>
                    <th>N° badge</th>
                    <th>Détenteur</th>
                    <th>Contact</th>
                    <th>Aéroport</th>
                    <th>Statut</th>
                    <th>Création</th>
                    <th>Expiration</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->badges as $badge)
                    @php
                        $status = $statusMeta[$badge->status] ?? ['label' => $badge->status, 'variant' => 'default'];
                        $coworker = $badge->getEffectiveCoworker();
                        $client = $badge->getEffectiveClient();
                        $isAdmin = auth()->user()->isTenantManager();
                        $isClient = auth()->user()->isClient();
                        $canReturn = ! $isClient && in_array($badge->status, ['active', 'expired', 'not_returned'], true);
                    @endphp
                    <tr wire:key="badge-{{ $badge->id }}">
                        <td>
                            @if ($badge->badge_number)
                                <span class="font-mono text-xs text-foreground-muted">{{ $badge->badge_number }}</span>
                            @else
                                <span class="text-foreground-subtle">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="font-medium text-foreground">
                                {{ trim(($coworker?->firstname ?? '').' '.($coworker?->lastname ?? '')) ?: '—' }}
                            </div>
                            <div class="text-xs text-foreground-muted">{{ $client?->company_name ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="text-foreground">{{ $coworker?->email ?? '—' }}</div>
                            <div class="text-xs text-foreground-muted">{{ $coworker?->phone ?? '—' }}</div>
                        </td>
                        <td>
                            @if ($badge->airport)
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$badge->airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                    {{ $badge->airport }}
                                </span>
                            @else
                                <span class="text-foreground-subtle">—</span>
                            @endif
                        </td>
                        <td>
                            <x-ui.badge :variant="$status['variant']">{{ $status['label'] }}</x-ui.badge>
                        </td>
                        <td class="text-foreground-muted">{{ $badge->created_at->format('d/m/Y') }}</td>
                        <td class="text-foreground-muted">
                            {{ $badge->expiry_date ? $badge->expiry_date->format('d/m/Y') : '—' }}
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

                                <x-ui.dropdown-item :href="route('badge-management.show', ['badgeId' => $badge->id])" wire:navigate>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Voir le détail
                                </x-ui.dropdown-item>

                                @if ($canReturn)
                                    <x-ui.dropdown-item variant="info" wire:click="$dispatch('return-badge', { id: {{ $badge->id }} })">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                        </svg>
                                        Marquer comme restitué
                                    </x-ui.dropdown-item>
                                @endif

                                @if ($isAdmin)
                                    <x-ui.dropdown-item wire:click="$dispatch('edit-badge-number', { id: {{ $badge->id }} })">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                        </svg>
                                        Modifier numéro / aéroport
                                    </x-ui.dropdown-item>

                                    <x-ui.dropdown-item wire:click="$dispatch('edit-badge-expiry', { id: {{ $badge->id }} })">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                        </svg>
                                        Modifier la date d'expiration
                                    </x-ui.dropdown-item>

                                    @if ($badge->status === 'expired')
                                        <x-ui.dropdown-item variant="danger" wire:click="notReturnedBadge({{ $badge->id }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                            </svg>
                                            Marquer non restitué
                                        </x-ui.dropdown-item>
                                    @endif
                                @endif
                            </x-ui.dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-foreground-muted">
                            <div class="py-12">
                                @if (! empty($search) || $selectedStatus || $selectedAirport || $selectedClientId)
                                    Aucun badge ne correspond aux filtres en cours.
                                @else
                                    Aucun badge à afficher.
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        @if ($this->badges->hasPages())
            <div class="border-t border-border px-5 py-3">
                {{ $this->badges->links() }}
            </div>
        @endif
    </x-ui.card>

    {{-- Modales partagées (déclenchées par events) --}}
    <livewire:badge-management.return-modal />
    <livewire:badge-management.edit-number-modal />
    <livewire:badge-management.edit-expiry-modal />
</div>
