<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Badge;
use App\Models\BadgeRequest;
use App\Models\Coworker;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Collaborateurs & utilisateurs', 'href' => '/coworkers'],
        ['label' => 'Détail'],
    ],
])]
#[Title('Détail du collaborateur')]
class extends Component
{
    use InteractsWithToasts;

    public int $coworkerId;

    public function mount(int $coworkerId): void
    {
        $coworker = Coworker::with(['user', 'client'])->find($coworkerId);
        if (! $coworker) {
            abort(404);
        }

        $authUser = auth()->user();

        if (! $authUser->isAdmin() && $coworker->client_id !== $authUser->client_id) {
            abort(403);
        }

        if ($authUser->isAdmin() && ! $authUser->isSAdmin() && $coworker->user && $coworker->user->role === 'sadmin') {
            abort(403);
        }

        $this->coworkerId = $coworker->id;
    }

    #[Computed]
    public function coworker(): Coworker
    {
        return Coworker::with([
            'user',
            'client',
            'trainings',
        ])->findOrFail($this->coworkerId);
    }

    #[Computed]
    public function badgeRequests()
    {
        return BadgeRequest::with(['activityRequest', 'badge'])
            ->where('coworker_id', $this->coworkerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function badges()
    {
        return Badge::with(['badgeRequest.activityRequest'])
            ->where(function ($q) {
                $q->where('coworker_id', $this->coworkerId)
                    ->orWhereHas('badgeRequest', fn ($s) => $s->where('coworker_id', $this->coworkerId));
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[On('coworker-updated')]
    #[On('coworker-deleted')]
    public function refresh(): void
    {
        unset($this->coworker, $this->badgeRequests, $this->badges);
    }
}; ?>

@php
    $cw = $this->coworker;
    $u = $cw->user;
    $client = $cw->client;
    $authUser = auth()->user();
    $isAdmin = $authUser->isAdmin();
    $isClient = $authUser->isClient();
    $hasUser = $u !== null;

    $canEdit = ! $hasUser || ! $isClient;
    $canMakeUser = $isAdmin && ! $hasUser;
    $canResetPassword = $isAdmin && $hasUser;
    $canRemoveAccount = $isAdmin && $hasUser;
    $canHasLeave = ! $cw->has_leave && ! $isClient;
    $canDelete = $isAdmin;

    $roleMeta = [
        'admin' => ['label' => 'Admin', 'variant' => 'rejected'],
        'sadmin' => ['label' => 'Sadmin', 'variant' => 'rejected'],
        'sclient' => ['label' => 'SClient', 'variant' => 'in-progress'],
        'aclient' => ['label' => 'AClient', 'variant' => 'in-progress'],
        'client' => ['label' => 'Client', 'variant' => 'ready'],
    ];

    $brStatusMeta = [
        'draft' => ['label' => 'Brouillon', 'variant' => 'draft'],
        'pending_rem' => ['label' => 'À envoyer REM', 'variant' => 'pending'],
        'rejected_rem' => ['label' => 'Refusée REM', 'variant' => 'rejected'],
        'pending_adp' => ['label' => 'En attente ADP', 'variant' => 'pending'],
        'approved_adp' => ['label' => 'Approuvée ADP', 'variant' => 'approved'],
        'rejected_adp' => ['label' => 'Refusée ADP', 'variant' => 'rejected'],
        'pending_fabrication' => ['label' => 'En fabrication', 'variant' => 'in-progress'],
        'ready_for_delivery' => ['label' => 'Prête à livrer', 'variant' => 'ready'],
        'delivered' => ['label' => 'Livrée', 'variant' => 'approved'],
        'terminated' => ['label' => 'Terminée', 'variant' => 'default'],
    ];

    $badgeStatusMeta = [
        'active' => ['label' => 'Actif', 'variant' => 'approved'],
        'expired' => ['label' => 'Expiré', 'variant' => 'rejected'],
        'returned' => ['label' => 'Restitué', 'variant' => 'ready'],
        'not_returned' => ['label' => 'Non restitué', 'variant' => 'pending'],
    ];
@endphp

<div class="space-y-6 p-8">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('coworkers.index') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 text-sm font-medium text-foreground-muted transition hover:text-foreground"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Retour
            </a>
            <span class="text-foreground-subtle">/</span>
            <h1 class="text-xl font-bold leading-tight text-foreground">{{ $cw->firstname }} {{ $cw->lastname }}</h1>
            @if ($hasUser)
                @php $r = $roleMeta[$u->role] ?? ['label' => $u->role, 'variant' => 'default']; @endphp
                <x-ui.badge :variant="$r['variant']">{{ $r['label'] }}</x-ui.badge>
            @else
                <x-ui.badge variant="draft">Collaborateur</x-ui.badge>
            @endif
            @if ($cw->has_leave)
                <x-ui.badge variant="rejected">Parti le {{ $cw->departure_date->format('d/m/Y') }}</x-ui.badge>
            @else
                <x-ui.badge variant="approved">Actif</x-ui.badge>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($canEdit)
                <x-ui.button :href="route('coworkers.form', ['coworkerId' => $cw->id])" wire:navigate variant="secondary" size="sm">
                    Modifier
                </x-ui.button>
            @endif

            @if ($canMakeUser || $canResetPassword || $canRemoveAccount || $canHasLeave || $canDelete)
                <x-ui.dropdown align="right" width="w-56">
                    <x-slot:trigger>
                        <button
                            type="button"
                            class="inline-flex h-9 items-center gap-1.5 rounded-md bg-white px-3 text-sm font-medium text-foreground-muted ring-1 ring-border transition hover:bg-slate-50"
                        >
                            Actions
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </x-slot:trigger>

                    @if ($canMakeUser)
                        <x-ui.dropdown-item variant="success" wire:click="$dispatch('open-make-user', { id: {{ $cw->id }} })">
                            Créer un compte
                        </x-ui.dropdown-item>
                    @endif

                    @if ($canResetPassword)
                        <x-ui.dropdown-item variant="info" wire:click="$dispatch('open-reset-password', { id: {{ $cw->id }} })">
                            Réinitialiser le mot de passe
                        </x-ui.dropdown-item>
                    @endif

                    @if ($canRemoveAccount)
                        <x-ui.dropdown-item variant="info" wire:click="$dispatch('open-remove-account', { id: {{ $cw->id }} })">
                            Retirer le compte
                        </x-ui.dropdown-item>
                    @endif

                    @if ($canHasLeave)
                        <x-ui.dropdown-item wire:click="$dispatch('open-has-leave', { id: {{ $cw->id }} })">
                            Marquer comme parti
                        </x-ui.dropdown-item>
                    @endif

                    @if ($canDelete)
                        <x-ui.dropdown-item variant="danger" wire:click="$dispatch('open-delete-coworker', { id: {{ $cw->id }} })">
                            Supprimer
                        </x-ui.dropdown-item>
                    @endif
                </x-ui.dropdown>
            @endif
        </div>
    </div>

    @if ($cw->has_leave)
        <x-ui.alert variant="warning">
            Ce collaborateur a quitté l'entreprise le {{ $cw->departure_date->format('d/m/Y') }}.
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Colonne gauche --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Identité --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Identité</h2>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Nom complet</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $cw->firstname }} {{ $cw->lastname }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Société</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $client?->company_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Email</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $cw->email ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Téléphone</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $cw->phone ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>

            {{-- Demandes de badge --}}
            <x-ui.card padding="none">
                <div class="border-b border-border px-5 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-foreground">Demandes de badge</h2>
                        <span class="text-xs text-foreground-muted">{{ $this->badgeRequests->count() }} demande(s)</span>
                    </div>
                </div>

                @if ($this->badgeRequests->isEmpty())
                    <div class="px-5 py-12 text-center text-sm text-foreground-muted">
                        Aucune demande de badge.
                    </div>
                @else
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Statut</th>
                                <th>Aéroport</th>
                                <th>Créée</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->badgeRequests as $br)
                                @php $st = $brStatusMeta[$br->status] ?? ['label' => $br->status, 'variant' => 'default']; @endphp
                                <tr wire:key="br-{{ $br->id }}">
                                    <td class="font-mono text-xs text-foreground-muted">#{{ $br->id }}</td>
                                    <td><x-ui.badge :variant="$st['variant']">{{ $st['label'] }}</x-ui.badge></td>
                                    <td class="text-foreground">{{ $br->activityRequest?->airport ?? '—' }}</td>
                                    <td class="text-foreground-muted">{{ $br->created_at->format('d/m/Y') }}</td>
                                    <td class="text-right">
                                        <a
                                            href="{{ route('badge-requests.show', ['badgeRequestId' => $br->id]) }}"
                                            wire:navigate
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-foreground transition hover:text-accent"
                                        >
                                            Voir
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                @endif
            </x-ui.card>

            {{-- Badges --}}
            <x-ui.card padding="none">
                <div class="border-b border-border px-5 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-foreground">Badges</h2>
                        <span class="text-xs text-foreground-muted">{{ $this->badges->count() }} badge(s)</span>
                    </div>
                </div>

                @if ($this->badges->isEmpty())
                    <div class="px-5 py-12 text-center text-sm text-foreground-muted">
                        Aucun badge.
                    </div>
                @else
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th>N° badge</th>
                                <th>Statut</th>
                                <th>Aéroport</th>
                                <th>Expiration</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->badges as $b)
                                @php $st = $badgeStatusMeta[$b->status] ?? ['label' => $b->status, 'variant' => 'default']; @endphp
                                <tr wire:key="b-{{ $b->id }}">
                                    <td class="font-mono text-xs text-foreground-muted">{{ $b->badge_number ?: '—' }}</td>
                                    <td><x-ui.badge :variant="$st['variant']">{{ $st['label'] }}</x-ui.badge></td>
                                    <td class="text-foreground">{{ $b->airport ?: '—' }}</td>
                                    <td class="text-foreground-muted">{{ $b->expiry_date ? $b->expiry_date->format('d/m/Y') : '—' }}</td>
                                    <td class="text-right">
                                        <a
                                            href="{{ route('badge-management.show', ['badgeId' => $b->id]) }}"
                                            wire:navigate
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-foreground transition hover:text-accent"
                                        >
                                            Voir
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                @endif
            </x-ui.card>
        </div>

        {{-- Colonne droite --}}
        <div class="space-y-6">
            {{-- Compte utilisateur --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Compte utilisateur</h2>

                    @if ($hasUser)
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Email</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ $u->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Rôle</dt>
                                <dd class="mt-1">
                                    @php $r = $roleMeta[$u->role] ?? ['label' => $u->role, 'variant' => 'default']; @endphp
                                    <x-ui.badge :variant="$r['variant']">{{ $r['label'] }}</x-ui.badge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Accès formations</dt>
                                <dd class="mt-1">
                                    @if ($u->can_access_formation)
                                        <x-ui.badge variant="approved">Activé</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="default">Désactivé</x-ui.badge>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Double authentification</dt>
                                <dd class="mt-1">
                                    @if ($u->two_factor_enabled)
                                        <x-ui.badge variant="approved">Activée</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="default">Désactivée</x-ui.badge>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Statut</dt>
                                <dd class="mt-1">
                                    @if ($u->is_new)
                                        <x-ui.badge variant="info">Doit changer son mot de passe</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="approved">Actif</x-ui.badge>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    @else
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-md bg-slate-100 text-foreground-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-foreground">Aucun compte</div>
                                <p class="text-xs text-foreground-muted">Ce collaborateur n'a pas d'accès à l'application.</p>
                            </div>
                        </div>

                        @if ($canMakeUser)
                            <x-ui.button variant="secondary" size="sm" wire:click="$dispatch('open-make-user', { id: {{ $cw->id }} })" class="w-full">
                                Créer un compte
                            </x-ui.button>
                        @endif
                    @endif
                </div>
            </x-ui.card>

            {{-- Formations --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-foreground">Formations</h2>
                        <span class="text-xs text-foreground-muted">{{ $cw->trainings->count() }}</span>
                    </div>

                    @if ($cw->trainings->isEmpty())
                        <p class="text-sm text-foreground-muted">Aucune formation attribuée.</p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($cw->trainings as $training)
                                @php
                                    $expiresAt = $training->pivot->expires_at ? \Carbon\Carbon::parse($training->pivot->expires_at) : null;
                                    $isLifetime = $expiresAt === null;
                                    $isExpired = $expiresAt !== null && $expiresAt->isPast();
                                    $variant = $isExpired ? 'rejected' : ($isLifetime ? 'in-progress' : 'approved');
                                    $hint = $isLifetime
                                        ? 'À vie'
                                        : ($isExpired ? 'Expirée le '.$expiresAt->format('d/m/Y') : 'Valide jusqu\'au '.$expiresAt->format('d/m/Y'));
                                @endphp
                                <li wire:key="training-{{ $training->id }}" class="flex items-start justify-between gap-3 rounded border border-border px-3 py-2">
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-foreground">{{ $training->title }}</div>
                                        <div class="text-xs text-foreground-muted">{{ $hint }}</div>
                                    </div>
                                    <x-ui.badge :variant="$variant" :dot="false" class="!text-[10px]">
                                        {{ $isExpired ? 'Expirée' : ($isLifetime ? 'À vie' : 'Valide') }}
                                    </x-ui.badge>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Modales partagées --}}
    <livewire:coworkers.has-leave-modal />
    <livewire:coworkers.reset-password-modal />
    <livewire:coworkers.make-user-modal />
    <livewire:coworkers.remove-account-modal />
    <livewire:coworkers.delete-modal />
</div>
