<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
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
        ['label' => 'Formations'],
    ],
])]
#[Title('Formations')]
class extends Component
{
    use InteractsWithToasts, WithoutUrlPagination, WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->isClient() && ! $user->can_access_formation) {
            $this->redirect(route('companies.show', ['companyId' => $user->client_id]), navigate: true);

            return;
        }

        if (! $user->isAdmin()) {
            $this->redirect(route('trainings.show', ['companyId' => $user->client_id]), navigate: true);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    #[Computed]
    public function statistics(): array
    {
        return [
            'clientCount' => Client::query()->count(),
            'coworkerCount' => \App\Models\Coworker::query()->where('has_leave', false)->count(),
            'activeTrainings' => DB::table('coworker_trainings')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->count(),
            'soonExpiring' => DB::table('coworker_trainings')
                ->whereNotNull('expires_at')
                ->whereBetween('expires_at', [now(), now()->addMonths(6)])
                ->count(),
            'expired' => DB::table('coworker_trainings')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count(),
        ];
    }

    #[Computed]
    public function items()
    {
        $query = Client::query()->withCount(['coworkers as active_coworkers_count' => function ($q) {
            $q->where('has_leave', false);
        }]);

        if (! empty($this->search)) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('company_name', 'like', "%{$s}%")
                    ->orWhere('trade_name', 'like', "%{$s}%")
                    ->orWhere('siret_number', 'like', "%{$s}%");
            });
        }

        $clients = $query->orderBy('company_name')->paginate(15);

        $clientIds = $clients->getCollection()->pluck('id')->all();
        $countsByClient = DB::table('coworker_trainings')
            ->join('coworkers', 'coworker_trainings.coworker_id', '=', 'coworkers.id')
            ->whereIn('coworkers.client_id', $clientIds)
            ->where(function ($q) {
                $q->whereNull('coworker_trainings.expires_at')
                    ->orWhere('coworker_trainings.expires_at', '>=', now());
            })
            ->selectRaw('coworkers.client_id, COUNT(*) as count')
            ->groupBy('coworkers.client_id')
            ->pluck('count', 'client_id');

        $clients->getCollection()->transform(function ($c) use ($countsByClient) {
            $c->active_trainings_count = (int) ($countsByClient[$c->id] ?? 0);

            return $c;
        });

        return $clients;
    }

    #[On('training-assigned')]
    public function refreshList(): void
    {
        unset($this->items, $this->statistics);
    }
}; ?>

<div class="space-y-6 p-8">
    {{-- Page header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-foreground">Formations</h1>
            <p class="text-sm text-foreground-muted">Catalogue des formations attribuées à vos collaborateurs.</p>
        </div>

        <x-ui.button wire:click="$dispatch('open-assign-training', { clientId: null })" variant="primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Attribuer une formation
        </x-ui.button>
    </div>

    {{-- Stats grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-ui.stat-card
            variant="default"
            label="Sociétés"
            :value="$this->statistics['clientCount']"
        />
        <x-ui.stat-card
            variant="default"
            label="Collaborateurs actifs"
            :value="$this->statistics['coworkerCount']"
        />
        <x-ui.stat-card
            variant="approved"
            label="Formations actives"
            :value="$this->statistics['activeTrainings']"
        />
        <x-ui.stat-card
            variant="pending"
            label="Expirent < 6 mois"
            :value="$this->statistics['soonExpiring']"
        />
        <x-ui.stat-card
            variant="rejected"
            label="Expirées"
            :value="$this->statistics['expired']"
        />
    </div>

    {{-- Toolbar --}}
    <div class="space-y-3">
        <x-ui.input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="Rechercher une société..."
        >
            <x-slot:leadingIcon>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </x-slot:leadingIcon>
        </x-ui.input>
    </div>

    @if (! empty($search))
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-foreground-muted">Filtres :</span>
            <button type="button" wire:click="$set('search', '')" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-foreground transition hover:bg-slate-200">
                Recherche : <span class="font-semibold">"{{ $search }}"</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3 text-foreground-muted">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
            <button type="button" wire:click="resetFilters" class="text-xs font-medium text-foreground-muted underline transition hover:text-foreground">
                Tout réinitialiser
            </button>
        </div>
    @endif

    {{-- Table --}}
    <x-ui.card padding="none" class="relative overflow-hidden">
        <div
            wire:loading.delay.short
            wire:target="search,resetFilters"
            class="absolute inset-x-0 top-0 z-10 h-0.5 overflow-hidden bg-blue-100"
        >
            <div class="h-full w-1/3 animate-pulse bg-blue-500"></div>
        </div>

        <div class="border-b border-border px-5 py-4">
            <h2 class="text-base font-semibold text-foreground">Sociétés</h2>
            <p class="mt-0.5 text-xs text-foreground-muted">{{ $this->items->total() }} {{ $this->items->total() > 1 ? 'sociétés' : 'société' }}</p>
        </div>

        <x-ui.table>
            <thead>
                <tr>
                    <th>Société</th>
                    <th>SIRET</th>
                    <th>Collaborateurs actifs</th>
                    <th>Formations actives</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->items as $client)
                    <tr wire:key="client-{{ $client->id }}">
                        <td>
                            <div class="font-medium text-foreground">{{ $client->company_name }}</div>
                            @if ($client->trade_name && $client->trade_name !== $client->company_name)
                                <div class="text-xs text-foreground-muted">{{ $client->trade_name }}</div>
                            @endif
                        </td>
                        <td class="font-mono text-xs text-foreground-muted">{{ $client->siret_number }}</td>
                        <td class="text-foreground">{{ $client->active_coworkers_count }}</td>
                        <td>
                            @if ($client->active_trainings_count > 0)
                                <x-ui.badge variant="approved">{{ $client->active_trainings_count }}</x-ui.badge>
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

                                <x-ui.dropdown-item :href="route('trainings.show', ['companyId' => $client->id])" wire:navigate>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Voir les formations
                                </x-ui.dropdown-item>

                                <x-ui.dropdown-item variant="success" wire:click="$dispatch('open-assign-training', { clientId: {{ $client->id }} })">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Attribuer une formation
                                </x-ui.dropdown-item>
                            </x-ui.dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-foreground-muted">
                            <div class="py-12">
                                @if (! empty($search))
                                    Aucune société ne correspond à la recherche.
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

    {{-- Modale partagée --}}
    <livewire:trainings.assign-modal />
</div>
