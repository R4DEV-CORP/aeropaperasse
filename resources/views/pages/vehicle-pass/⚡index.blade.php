<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\VehiclePass;
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
        ['label' => 'Laissez-passer'],
    ],
])]
#[Title('Laissez-passer')]
class extends Component
{
    use InteractsWithToasts, WithoutUrlPagination, WithPagination;

    public string $search = '';

    public ?string $selectedAirport = null;

    public ?string $selectedStatus = null;

    public function mount(): void
    {
        if (auth()->user()->isClient()) {
            $this->redirect(route('companies.show', ['companyId' => auth()->user()->client_id]), navigate: true);
        }
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

    public function filterByStatus(?string $status): void
    {
        $this->selectedStatus = $status === $this->selectedStatus ? null : $status;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'selectedAirport', 'selectedStatus']);
        $this->resetPage();
    }

    private function baseQuery()
    {
        $query = VehiclePass::with(['client', 'activityRequest']);

        if (! auth()->user()->isAdmin()) {
            $query->where('client_id', auth()->user()->client_id);
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
            'pending' => $counts->get('pending', 0),
            'approved' => $counts->get('approved', 0),
            'rejected' => $counts->get('rejected', 0),
        ];
    }

    #[Computed]
    public function items()
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
                $q->where('plate_number', 'like', "%{$search}%")
                    ->orWhere('car_brand', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($s) use ($search) {
                        $s->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    #[On('vehicle-pass-approved')]
    #[On('vehicle-pass-rejected')]
    #[On('vehicle-pass-created')]
    public function refreshList(): void
    {
        unset($this->statistics, $this->items);
    }
}; ?>

@php
    $statusMeta = [
        'pending' => ['label' => 'En attente', 'variant' => 'pending'],
        'approved' => ['label' => 'Approuvée', 'variant' => 'approved'],
        'rejected' => ['label' => 'Rejetée', 'variant' => 'rejected'],
    ];

    $airportMeta = [
        'CDG' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'ORY' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'LBG' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    ];
@endphp

<div class="space-y-6 p-8">
    {{-- Page header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-foreground">Laissez-passer</h1>
            <p class="text-sm text-foreground-muted">Gérez les demandes de laissez-passer véhicule.</p>
        </div>

        <x-ui.split-button variant="primary">
            <x-slot:label>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nouvelle demande
            </x-slot:label>

            <x-ui.dropdown-item :href="route('vehicle-pass.form', ['mode' => 'linked'])" wire:navigate>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                </svg>
                Lier à une demande d'activité
            </x-ui.dropdown-item>
            <x-ui.dropdown-item :href="route('vehicle-pass.form', ['mode' => 'standalone'])" wire:navigate>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Créer indépendamment
            </x-ui.dropdown-item>
        </x-ui.split-button>
    </div>

    {{-- Stats grid --}}
    @php
        $statCards = [
            ['key' => 'pending', 'variant' => 'pending', 'label' => 'En attente', 'ring' => 'ring-amber-500'],
            ['key' => 'approved', 'variant' => 'approved', 'label' => 'Approuvées', 'ring' => 'ring-emerald-500'],
            ['key' => 'rejected', 'variant' => 'rejected', 'label' => 'Rejetées', 'ring' => 'ring-red-500'],
        ];
    @endphp
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
                    placeholder="Rechercher par immatriculation, marque ou société..."
                >
                    <x-slot:leadingIcon>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </x-slot:leadingIcon>
                </x-ui.input>
            </div>
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
            <h2 class="text-base font-semibold text-foreground">Liste des demandes</h2>
            <p class="mt-0.5 text-xs text-foreground-muted">{{ $this->items->total() }} {{ $this->items->total() > 1 ? 'demandes' : 'demande' }}</p>
        </div>

        <x-ui.table>
            <thead>
                <tr>
                    <th>Société</th>
                    <th>Véhicule</th>
                    <th>Aéroport</th>
                    <th>Statut</th>
                    <th>Création</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->items as $vp)
                    @php
                        $status = $statusMeta[$vp->status] ?? ['label' => $vp->status, 'variant' => 'default'];
                        $isAdmin = auth()->user()->isAdmin();
                        $isPending = $vp->status === 'pending';
                    @endphp
                    <tr wire:key="vp-{{ $vp->id }}">
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="font-medium text-foreground">{{ $vp->client?->company_name ?? '—' }}</div>
                                @if (! $vp->activity_request_id)
                                    <x-ui.badge variant="default">Indépendante</x-ui.badge>
                                @endif
                            </div>
                            @if ($vp->client?->trade_name && $vp->client?->trade_name !== $vp->client?->company_name)
                                <div class="text-xs text-foreground-muted">{{ $vp->client->trade_name }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="font-mono text-sm text-foreground">{{ $vp->plate_number ?? '—' }}</div>
                            <div class="text-xs text-foreground-muted">{{ $vp->car_brand ?? '—' }}</div>
                        </td>
                        <td>
                            @if ($vp->airport)
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$vp->airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                    {{ $vp->airport }}
                                </span>
                            @else
                                <span class="text-foreground-subtle">—</span>
                            @endif
                        </td>
                        <td>
                            <x-ui.badge :variant="$status['variant']">{{ $status['label'] }}</x-ui.badge>
                        </td>
                        <td class="text-foreground-muted">{{ $vp->created_at->format('d/m/Y') }}</td>
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

                                <x-ui.dropdown-item :href="route('vehicle-pass.show', ['vehiclePassId' => $vp->id])" wire:navigate>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Voir le détail
                                </x-ui.dropdown-item>

                                @if ($isAdmin && $isPending)
                                    <x-ui.dropdown-item variant="success" wire:click="$dispatch('open-approve-vehicle-pass', { id: {{ $vp->id }} })">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        Approuver
                                    </x-ui.dropdown-item>

                                    <x-ui.dropdown-item variant="danger" wire:click="$dispatch('open-reject-vehicle-pass', { id: {{ $vp->id }} })">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                        Rejeter
                                    </x-ui.dropdown-item>
                                @endif
                            </x-ui.dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-foreground-muted">
                            <div class="py-12">
                                @if (! empty($search) || $selectedStatus || $selectedAirport)
                                    Aucune demande ne correspond aux filtres en cours.
                                @else
                                    Aucune demande de laissez-passer.
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        @if ($this->items->hasPages())
            <div class="border-t border-border px-5 py-3">
                {{ $this->items->links() }}
            </div>
        @endif
    </x-ui.card>

    {{-- Modales partagées --}}
    <livewire:vehicle-pass.approve-modal />
    <livewire:vehicle-pass.reject-modal />
</div>
