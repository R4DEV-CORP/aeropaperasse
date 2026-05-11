<?php

use App\Livewire\Concerns\InteractsWithToasts;
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
        ['label' => 'Sociétés'],
    ],
])]
#[Title('Sociétés')]
class extends Component
{
    use InteractsWithToasts, WithoutUrlPagination, WithPagination;

    public string $search = '';

    public ?string $airlineFilter = null;

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            $this->redirect(route('companies.show', ['companyId' => $user->client_id]), navigate: true);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAirlineFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'airlineFilter']);
        $this->resetPage();
    }

    private function baseQuery()
    {
        return Client::query()
            ->withCount(['coworkers', 'users', 'activityRequests']);
    }

    #[Computed]
    public function statistics(): array
    {
        $total = Client::query()->count();
        $airlines = Client::query()->where('is_airline_company', true)->count();
        $coworkers = \App\Models\Coworker::query()->count();

        return [
            'total' => $total,
            'airlines' => $airlines,
            'coworkers' => $coworkers,
        ];
    }

    #[Computed]
    public function items()
    {
        if (! empty($this->search)) {
            $ids = Client::search($this->search)->keys()->all();

            $query = $this->baseQuery()->whereIn('id', $ids);
        } else {
            $query = $this->baseQuery();
        }

        if ($this->airlineFilter === 'yes') {
            $query->where('is_airline_company', true);
        } elseif ($this->airlineFilter === 'no') {
            $query->where('is_airline_company', false);
        }

        return $query->orderBy('company_name')->paginate(15);
    }

    #[On('company-saved')]
    #[On('company-deleted')]
    public function refreshList(): void
    {
        unset($this->statistics, $this->items);
    }
}; ?>

<div class="space-y-6 p-8">
    {{-- Page header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-foreground">Sociétés</h1>
            <p class="text-sm text-foreground-muted">Gérez les sociétés et leurs accès.</p>
        </div>

        <x-ui.button :href="route('companies.form')" wire:navigate variant="primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nouvelle société
        </x-ui.button>
    </div>

    {{-- Stats grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-ui.stat-card
            variant="default"
            label="Total sociétés"
            :value="$this->statistics['total']"
        />
        <x-ui.stat-card
            variant="approved"
            label="Compagnies aériennes"
            :value="$this->statistics['airlines']"
        />
        <x-ui.stat-card
            variant="in-progress"
            label="Total collaborateurs"
            :value="$this->statistics['coworkers']"
        />
    </div>

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <x-ui.input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Rechercher par nom, nom commercial ou SIRET..."
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
            name="airlineFilter"
            :value="$airlineFilter"
            :options="[
                ['value' => null, 'label' => 'Toutes'],
                ['value' => 'yes', 'label' => 'Compagnies aériennes'],
                ['value' => 'no', 'label' => 'Non aériennes'],
            ]"
        />
    </div>

    {{-- Active filter chips --}}
    @if (! empty($search) || $airlineFilter)
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

            @if ($airlineFilter)
                <button type="button" wire:click="$set('airlineFilter', null)" class="{{ $chipClass }}">
                    {{ $airlineFilter === 'yes' ? 'Compagnies aériennes' : 'Non aériennes' }}
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
            wire:target="search,airlineFilter,resetFilters"
            class="absolute inset-x-0 top-0 z-10 h-0.5 overflow-hidden bg-blue-100"
        >
            <div class="h-full w-1/3 animate-pulse bg-blue-500"></div>
        </div>

        <div class="border-b border-border px-5 py-4">
            <h2 class="text-base font-semibold text-foreground">Liste des sociétés</h2>
            <p class="mt-0.5 text-xs text-foreground-muted">{{ $this->items->total() }} {{ $this->items->total() > 1 ? 'sociétés' : 'société' }}</p>
        </div>

        <x-ui.table>
            <thead>
                <tr>
                    <th>Raison sociale</th>
                    <th>SIRET</th>
                    <th>Ville</th>
                    <th>Collaborateurs</th>
                    <th>Créée le</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->items as $company)
                    <tr wire:key="company-{{ $company->id }}">
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="min-w-0">
                                    <div class="font-medium text-foreground">{{ $company->company_name }}</div>
                                    @if ($company->trade_name && $company->trade_name !== $company->company_name)
                                        <div class="text-xs text-foreground-muted">{{ $company->trade_name }}</div>
                                    @endif
                                </div>
                                @if ($company->is_airline_company)
                                    <x-ui.tooltip text="Compagnie aérienne" placement="top">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200" aria-label="Compagnie aérienne">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3.5 w-3.5">
                                                <path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 0 0-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5L21 16Z" />
                                            </svg>
                                        </span>
                                    </x-ui.tooltip>
                                @endif
                            </div>
                        </td>
                        <td class="font-mono text-xs text-foreground-muted">{{ $company->siret_number }}</td>
                        <td class="text-foreground">{{ $company->city }}</td>
                        <td class="text-foreground">{{ $company->coworkers_count }}</td>
                        <td class="text-foreground-muted">{{ $company->created_at->format('d/m/Y') }}</td>
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

                                <x-ui.dropdown-item :href="route('companies.show', ['companyId' => $company->id])" wire:navigate>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Voir le détail
                                </x-ui.dropdown-item>

                                <x-ui.dropdown-item :href="route('companies.form', ['companyId' => $company->id])" wire:navigate>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                    </svg>
                                    Modifier
                                </x-ui.dropdown-item>

                                <x-ui.dropdown-item variant="danger" wire:click="$dispatch('open-delete-company', { id: {{ $company->id }} })">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                    Supprimer
                                </x-ui.dropdown-item>
                            </x-ui.dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-foreground-muted">
                            <div class="py-12">
                                @if (! empty($search) || $airlineFilter)
                                    Aucune société ne correspond aux filtres en cours.
                                @else
                                    Aucune société pour le moment.
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
    <livewire:companies.delete-modal />
</div>
