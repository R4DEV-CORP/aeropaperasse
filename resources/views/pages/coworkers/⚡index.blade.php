<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Client;
use App\Models\Coworker;
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
        ['label' => 'Collaborateurs & utilisateurs'],
    ],
])]
#[Title('Collaborateurs & utilisateurs')]
class extends Component
{
    use InteractsWithToasts, WithoutUrlPagination, WithPagination;

    public string $search = '';

    public ?int $selectedClientId = null;

    public ?string $selectedRole = null;

    public ?string $selectedKind = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedClientId(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedRole(): void
    {
        $this->resetPage();
    }

    public function filterByKind(?string $kind): void
    {
        $this->selectedKind = $kind === $this->selectedKind ? null : $kind;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['selectedClientId', 'selectedRole', 'selectedKind', 'search']);
        $this->resetPage();
    }

    private function baseQuery()
    {
        $query = Coworker::query()->with(['client', 'user']);

        $authUser = auth()->user();

        if ($authUser->isAdmin() && ! $authUser->isSAdmin()) {
            $query->where(function ($q) {
                $q->whereNull('user_id')
                    ->orWhereHas('user', function ($u) {
                        $u->where('role', '!=', 'rem_super_admin');
                    });
            });
        }

        if (! $authUser->isAdmin()) {
            $query->where('client_id', $authUser->client_id);
        }

        return $query;
    }

    #[Computed]
    public function statistics(): array
    {
        $base = $this->baseQuery();

        $total = (clone $base)->count();
        $withUser = (clone $base)->whereNotNull('user_id')->count();
        $withoutUser = $total - $withUser;
        $withFormation = (clone $base)->whereHas('user', fn ($q) => $q->where('can_access_formation', true))->count();
        $left = (clone $base)->where('has_leave', true)->count();

        return [
            'total' => $total,
            'with_user' => $withUser,
            'without_user' => $withoutUser,
            'with_formation' => $withFormation,
            'left' => $left,
        ];
    }

    #[Computed]
    public function clients()
    {
        if (! auth()->user()->isAdmin()) {
            return collect();
        }

        return Client::orderBy('company_name')->get();
    }

    #[Computed]
    public function coworkers()
    {
        $query = $this->baseQuery();

        if ($this->selectedClientId && auth()->user()->isAdmin()) {
            $query->where('client_id', $this->selectedClientId);
        }

        if ($this->selectedRole) {
            if ($this->selectedRole === 'none') {
                $query->whereNull('user_id');
            } else {
                $query->whereHas('user', fn ($q) => $q->where('role', $this->selectedRole));
            }
        }

        if ($this->selectedKind === 'with_user') {
            $query->whereNotNull('user_id');
        } elseif ($this->selectedKind === 'without_user') {
            $query->whereNull('user_id');
        } elseif ($this->selectedKind === 'with_formation') {
            $query->whereHas('user', fn ($q) => $q->where('can_access_formation', true));
        } elseif ($this->selectedKind === 'left') {
            $query->where('has_leave', true);
        }

        if (! empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$search}%"])
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('lastname')->orderBy('firstname')->paginate(15);
    }

    #[On('coworker-updated')]
    #[On('coworker-created')]
    #[On('coworker-deleted')]
    public function refreshList(): void
    {
        unset($this->statistics, $this->coworkers);
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
    $authUser = auth()->user();
    $isAdmin = $authUser->isAdmin();
    $isClient = $authUser->isClient();
@endphp

<div class="space-y-6 p-8">
    {{-- Page header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-foreground">Collaborateurs & utilisateurs</h1>
            <p class="text-sm text-foreground-muted">Gérez les collaborateurs et leurs comptes utilisateurs.</p>
        </div>

        @if (! $isClient)
            <x-ui.button :href="route('coworkers.form')" wire:navigate variant="primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
                Nouveau
            </x-ui.button>
        @endif
    </div>

    {{-- Stats grid --}}
    @php
        $statCards = [
            ['key' => 'with_user', 'variant' => 'ready', 'label' => 'Avec compte'],
            ['key' => 'without_user', 'variant' => 'draft', 'label' => 'Sans compte'],
            ['key' => 'with_formation', 'variant' => 'in-progress', 'label' => 'Accès formation'],
            ['key' => 'left', 'variant' => 'rejected', 'label' => 'Partis'],
        ];
        $ringColors = [
            'with_user' => 'ring-blue-500',
            'without_user' => 'ring-slate-500',
            'with_formation' => 'ring-violet-500',
            'left' => 'ring-red-500',
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
                wire:click="filterByKind('{{ $card['key'] }}')"
                class="group rounded-lg text-left transition focus:outline-none {{ $selectedKind === $card['key'] ? 'ring-2 '.$ringColors[$card['key']] : '' }}"
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
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
            <div class="flex-1">
                <x-ui.input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Rechercher par nom, email ou téléphone..."
                >
                    <x-slot:leadingIcon>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </x-slot:leadingIcon>
                </x-ui.input>
            </div>

            @if ($isAdmin)
                @php
                    $clientOptions = [['value' => null, 'label' => 'Toutes les sociétés']];
                    foreach ($this->clients as $c) {
                        $clientOptions[] = [
                            'value' => $c->id,
                            'label' => $c->company_name,
                            'hint' => $c->siret_number,
                        ];
                    }
                    $roleOptions = [
                        ['value' => null, 'label' => 'Tous les rôles'],
                        ['value' => 'none', 'label' => 'Sans compte'],
                        ['value' => 'client', 'label' => 'Client'],
                        ['value' => 'sclient', 'label' => 'SClient'],
                        ['value' => 'aclient', 'label' => 'AClient'],
                        ['value' => 'rem_admin', 'label' => 'Administrateur REM'],
                    ];
                    if ($authUser->isSAdmin()) {
                        $roleOptions[] = ['value' => 'rem_super_admin', 'label' => 'Super admin REM'];
                    }
                @endphp
                <div class="lg:w-64">
                    <x-ui.select
                        :value="$selectedClientId"
                        wire:model.live="selectedClientId"
                        :options="$clientOptions"
                        placeholder="Toutes les sociétés"
                        searchable
                        search-placeholder="Filtrer par société…"
                    />
                </div>
                <div class="lg:w-48">
                    <x-ui.select
                        :value="$selectedRole"
                        wire:model.live="selectedRole"
                        :options="$roleOptions"
                        placeholder="Tous les rôles"
                    />
                </div>
            @endif
        </div>
    </div>

    {{-- Active filter chips --}}
    @if ($selectedKind || $selectedClientId || $selectedRole || ! empty($search))
        @php
            $chipClass = 'inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-foreground transition hover:bg-slate-200';
            $kindLabels = [
                'with_user' => 'Avec compte',
                'without_user' => 'Sans compte',
                'with_formation' => 'Accès formation',
                'left' => 'Partis',
            ];
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

            @if ($selectedKind)
                <button type="button" wire:click="$set('selectedKind', null)" class="{{ $chipClass }}">
                    Type : <span class="font-semibold">{{ $kindLabels[$selectedKind] ?? $selectedKind }}</span>
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

            @if ($selectedRole)
                <button type="button" wire:click="$set('selectedRole', null)" class="{{ $chipClass }}">
                    Rôle : <span class="font-semibold">{{ $selectedRole === 'none' ? 'Sans compte' : ($roleMeta[$selectedRole]['label'] ?? $selectedRole) }}</span>
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
            wire:target="search,selectedClientId,selectedRole,selectedKind,filterByKind,resetFilters"
            class="absolute inset-x-0 top-0 z-10 h-0.5 overflow-hidden bg-blue-100"
        >
            <div class="h-full w-1/3 animate-pulse bg-blue-500"></div>
        </div>

        <div class="border-b border-border px-5 py-4">
            <h2 class="text-base font-semibold text-foreground">Liste</h2>
            <p class="mt-0.5 text-xs text-foreground-muted">
                {{ $this->coworkers->total() }} {{ $this->coworkers->total() > 1 ? 'collaborateurs' : 'collaborateur' }}
            </p>
        </div>

        <x-ui.table>
            <thead>
                <tr>
                    <th>Nom</th>
                    @if ($isAdmin)
                        <th>Société</th>
                    @endif
                    <th>Type</th>
                    <th>Contact</th>
                    <th>Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->coworkers as $coworker)
                    @php
                        $u = $coworker->user;
                        $hasUser = $u !== null;
                        $canEdit = ! $hasUser || ! $isClient;
                        $canResetPassword = $isAdmin && $hasUser;
                        $canMakeUser = $isAdmin && ! $hasUser;
                        $canRemoveAccount = $isAdmin && $hasUser;
                        $canHasLeave = ! $coworker->has_leave;
                        $canDelete = $isAdmin;
                    @endphp
                    <tr wire:key="coworker-{{ $coworker->id }}">
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('coworkers.show', ['coworkerId' => $coworker->id]) }}" wire:navigate class="font-medium text-foreground hover:text-accent">
                                    {{ $coworker->firstname }} {{ $coworker->lastname }}
                                </a>
                                @if ($hasUser && $u->can_access_formation)
                                    <span title="Accès aux formations" class="text-violet-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                            <path d="M10 1.5a1 1 0 0 1 .447.106l8 4a1 1 0 0 1 0 1.788l-1.95 .975L10 11.118 3.503 8.369l-1.95-.975a1 1 0 0 1 0-1.788l8-4A1 1 0 0 1 10 1.5Z" />
                                            <path d="M5 10.118v3.07a1 1 0 0 0 .553.894l4 2a1 1 0 0 0 .894 0l4-2A1 1 0 0 0 15 13.188v-3.07L10.447 12.4a1 1 0 0 1-.894 0L5 10.118Z" />
                                        </svg>
                                    </span>
                                @endif
                                @if ($hasUser && $u->is_new)
                                    <x-ui.badge variant="info" :dot="false" class="!px-1.5 !py-0 !text-[10px]">Nouveau</x-ui.badge>
                                @endif
                                @if ($hasUser && $u->two_factor_enabled)
                                    <span title="2FA activée" class="text-emerald-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </td>
                        @if ($isAdmin)
                            <td>
                                <div class="font-medium text-foreground">{{ $coworker->client?->company_name ?? '—' }}</div>
                                @if ($coworker->client?->trade_name)
                                    <div class="text-xs text-foreground-muted">{{ $coworker->client->trade_name }}</div>
                                @endif
                            </td>
                        @endif
                        <td>
                            @if ($hasUser)
                                @php $r = $roleMeta[$u->role] ?? ['label' => $u->role, 'variant' => 'default']; @endphp
                                <x-ui.badge :variant="$r['variant']">{{ $r['label'] }}</x-ui.badge>
                            @else
                                <x-ui.badge variant="draft">Collaborateur</x-ui.badge>
                            @endif
                        </td>
                        <td>
                            <div class="text-foreground">{{ $coworker->email ?: '—' }}</div>
                            <div class="text-xs text-foreground-muted">{{ $coworker->phone ?: '—' }}</div>
                        </td>
                        <td>
                            @if ($coworker->has_leave)
                                <x-ui.badge variant="rejected">
                                    {{ $coworker->departure_date ? 'Parti le '.$coworker->departure_date->format('d/m/Y') : 'Parti' }}
                                </x-ui.badge>
                            @else
                                <x-ui.badge variant="approved">Actif</x-ui.badge>
                            @endif
                        </td>
                        <td class="text-right">
                            <x-ui.dropdown align="right" width="w-56">
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

                                <x-ui.dropdown-item :href="route('coworkers.show', ['coworkerId' => $coworker->id])" wire:navigate>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Voir le détail
                                </x-ui.dropdown-item>

                                @if ($canEdit)
                                    <x-ui.dropdown-item :href="route('coworkers.form', ['coworkerId' => $coworker->id])" wire:navigate>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                        </svg>
                                        Modifier
                                    </x-ui.dropdown-item>
                                @endif

                                @if ($canMakeUser)
                                    <x-ui.dropdown-item variant="success" wire:click="$dispatch('open-make-user', { id: {{ $coworker->id }} })">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                        </svg>
                                        Créer un compte
                                    </x-ui.dropdown-item>
                                @endif

                                @if ($canResetPassword)
                                    <x-ui.dropdown-item variant="info" wire:click="$dispatch('open-reset-password', { id: {{ $coworker->id }} })">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                                        </svg>
                                        Réinitialiser le mot de passe
                                    </x-ui.dropdown-item>
                                @endif

                                @if ($canRemoveAccount)
                                    <x-ui.dropdown-item variant="info" wire:click="$dispatch('open-remove-account', { id: {{ $coworker->id }} })">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                        </svg>
                                        Retirer le compte
                                    </x-ui.dropdown-item>
                                @endif

                                @if ($canHasLeave)
                                    <x-ui.dropdown-item wire:click="$dispatch('open-has-leave', { id: {{ $coworker->id }} })">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                        </svg>
                                        Marquer comme parti
                                    </x-ui.dropdown-item>
                                @endif

                                @if ($canDelete)
                                    <x-ui.dropdown-item variant="danger" wire:click="$dispatch('open-delete-coworker', { id: {{ $coworker->id }} })">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                        Supprimer
                                    </x-ui.dropdown-item>
                                @endif
                            </x-ui.dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 6 : 5 }}" class="text-center text-foreground-muted">
                            <div class="py-12">
                                @if (! empty($search) || $selectedClientId || $selectedRole || $selectedKind)
                                    Aucun collaborateur ne correspond aux filtres en cours.
                                @else
                                    Aucun collaborateur à afficher.
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        @if ($this->coworkers->hasPages())
            <div class="border-t border-border px-5 py-3">
                {{ $this->coworkers->links() }}
            </div>
        @endif
    </x-ui.card>

    {{-- Modales partagées (déclenchées par events) --}}
    <livewire:coworkers.has-leave-modal />
    <livewire:coworkers.reset-password-modal />
    <livewire:coworkers.make-user-modal />
    <livewire:coworkers.remove-account-modal />
    <livewire:coworkers.delete-modal />
</div>
