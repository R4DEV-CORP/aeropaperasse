<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Badge;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Suivi des badges', 'href' => '/badge-management'],
        ['label' => 'Détail'],
    ],
])]
#[Title('Détail du badge')]
class extends Component
{
    use InteractsWithToasts;

    public int $badgeId;

    public function mount(int $badgeId): void
    {
        $badge = Badge::with([
            'client',
            'coworker',
            'badgeRequest.coworker',
            'badgeRequest.activityRequest.client',
        ])->find($badgeId);

        if ($badge === null) {
            abort(404);
        }

        $user = auth()->user();
        if (! $user->isAdmin()) {
            $effectiveClient = $badge->getEffectiveClient();
            if (! $effectiveClient || $effectiveClient->id !== $user->client_id) {
                abort(403);
            }

            if ($user->isClient()) {
                $effectiveCoworker = $badge->getEffectiveCoworker();
                if (! $effectiveCoworker || $effectiveCoworker->user_id !== $user->id) {
                    abort(403);
                }
            }
        }

        $this->badgeId = $badge->id;
    }

    #[Computed]
    public function badge(): Badge
    {
        return Badge::with([
            'client',
            'coworker',
            'badgeRequest.coworker',
            'badgeRequest.activityRequest.client',
        ])->findOrFail($this->badgeId);
    }

    #[On('badge-updated')]
    public function refresh(): void
    {
        unset($this->badge);
    }

    public function downloadReturnDocument()
    {
        $badge = $this->badge;
        if (! $badge->return_document || ! Storage::disk('public')->exists($badge->return_document)) {
            $this->toast('Document de restitution indisponible.', 'warning');

            return null;
        }

        return response()->download(
            Storage::disk('public')->path($badge->return_document),
            basename($badge->return_document)
        );
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

@php
    $b = $this->badge;
    $status = $statusMeta[$b->status] ?? ['label' => $b->status, 'variant' => 'default'];
    $coworker = $b->getEffectiveCoworker();
    $client = $b->getEffectiveClient();
    $br = $b->badgeRequest;
    $ar = $br?->activityRequest;
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isClient = $user->isClient();
    $canReturn = ! $isClient && in_array($b->status, ['active', 'expired', 'not_returned'], true);
    $canEdit = $isAdmin;
@endphp

<div class="space-y-6 p-8">
    {{-- Header : retour + titre + statut + actions --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('badge-management.index') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 text-sm font-medium text-foreground-muted transition hover:text-foreground"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Retour
            </a>
            <span class="text-foreground-subtle">/</span>
            <h1 class="text-xl font-bold leading-tight text-foreground">
                Badge <span class="font-mono font-semibold text-foreground-muted">#{{ $b->id }}</span>
            </h1>
            <x-ui.badge :variant="$status['variant']">{{ $status['label'] }}</x-ui.badge>
        </div>

        @if ($canReturn || $canEdit)
            <div class="flex flex-wrap items-center gap-2">
                @if ($canReturn)
                    <x-ui.button variant="info" size="sm" wire:click="$dispatch('return-badge', { id: {{ $b->id }} })">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                        </svg>
                        Restituer
                    </x-ui.button>
                @endif
                @if ($canEdit)
                    <x-ui.button variant="secondary" size="sm" wire:click="$dispatch('edit-badge-number', { id: {{ $b->id }} })">
                        Modifier numéro / aéroport
                    </x-ui.button>
                    <x-ui.button variant="secondary" size="sm" wire:click="$dispatch('edit-badge-expiry', { id: {{ $b->id }} })">
                        Modifier la date d'expiration
                    </x-ui.button>
                @endif
            </div>
        @endif
    </div>

    {{-- Bandeau d'avertissement si expiré ou non restitué --}}
    @if ($b->status === 'expired')
        <x-ui.alert variant="warning">
            Ce badge a dépassé sa date d'expiration. Il doit être restitué ou marqué comme non restitué.
        </x-ui.alert>
    @elseif ($b->status === 'not_returned')
        <x-ui.alert variant="danger">
            Ce badge n'a pas été restitué après expiration.
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Colonne gauche : 2/3 --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Détenteur --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Détenteur</h2>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Nom complet</dt>
                            <dd class="mt-1 text-sm text-foreground">
                                {{ trim(($coworker?->firstname ?? '').' '.($coworker?->lastname ?? '')) ?: '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Société</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $client?->company_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Email</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $coworker?->email ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Téléphone</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $coworker?->phone ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>

            {{-- Demande de badge liée --}}
            @if ($br)
                <x-ui.card>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base font-semibold text-foreground">Demande de badge</h2>
                            <a
                                href="{{ route('badge-requests.show', ['badgeRequestId' => $br->id]) }}"
                                wire:navigate
                                class="inline-flex items-center gap-1 text-xs font-semibold text-foreground transition hover:text-brand"
                            >
                                Voir la demande
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        </div>
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Numéro</dt>
                                <dd class="mt-1 font-mono text-sm text-foreground">#{{ $br->id }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Statut</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ $br->status }}</dd>
                            </div>
                        </dl>
                    </div>
                </x-ui.card>
            @else
                <x-ui.card>
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-md bg-slate-100 text-foreground-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                        </span>
                        <div>
                            <div class="text-sm font-semibold text-foreground">Badge indépendant</div>
                            <p class="text-xs text-foreground-muted">Ce badge n'est pas rattaché à une demande de badge.</p>
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Demande d'activité liée --}}
            @if ($ar)
                <x-ui.card>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base font-semibold text-foreground">Demande d'activité</h2>
                            <a
                                href="{{ route('activity-requests.show', ['activityRequestId' => $ar->id]) }}"
                                wire:navigate
                                class="inline-flex items-center gap-1 text-xs font-semibold text-foreground transition hover:text-brand"
                            >
                                Voir la demande
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        </div>
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Numéro</dt>
                                <dd class="mt-1 font-mono text-sm text-foreground">#{{ $ar->id }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Aéroport</dt>
                                <dd class="mt-1">
                                    @if ($ar->airport)
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$ar->airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                            {{ $ar->airport }}
                                        </span>
                                    @else
                                        <span class="text-sm text-foreground-subtle">—</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($ar->description)
                                <div class="sm:col-span-2">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Description</dt>
                                    <dd class="mt-1 text-sm text-foreground">{{ $ar->description }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </x-ui.card>
            @endif

            {{-- Document de restitution --}}
            @if ($b->status === 'returned' && $b->return_document)
                <x-ui.card>
                    <div class="space-y-4">
                        <h2 class="text-base font-semibold text-foreground">Document de restitution</h2>
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-foreground">Document signé</div>
                                <div class="truncate text-xs text-foreground-muted">{{ basename($b->return_document) }}</div>
                            </div>
                            <button
                                type="button"
                                wire:click="downloadReturnDocument"
                                class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-foreground-muted transition hover:bg-slate-100 hover:text-foreground"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Télécharger
                            </button>
                        </div>
                    </div>
                </x-ui.card>
            @endif
        </div>

        {{-- Colonne droite : 1/3 --}}
        <div class="space-y-6">
            {{-- Caractéristiques --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Caractéristiques</h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-foreground-muted">N° badge</dt>
                            <dd class="font-mono text-foreground">{{ $b->badge_number ?: '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-foreground-muted">Aéroport</dt>
                            <dd>
                                @if ($b->airport)
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$b->airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                        {{ $b->airport }}
                                    </span>
                                @else
                                    <span class="text-foreground-subtle">—</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-foreground-muted">Date d'expiration</dt>
                            <dd class="text-foreground">{{ $b->expiry_date?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>

            {{-- Suivi du statut --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Suivi du statut</h2>
                    @php
                        $timeline = collect([
                            ['label' => 'Badge créé', 'at' => $b->created_at],
                            ['label' => 'Badge restitué', 'at' => $b->returned_at],
                        ])->filter(fn ($s) => $s['at'])->values();
                    @endphp
                    <dl class="space-y-3">
                        @foreach ($timeline as $step)
                            <div class="flex items-start gap-3">
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-foreground">{{ $step['label'] }}</div>
                                    <div class="text-xs text-foreground-muted">{{ $step['at']->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </x-ui.card>

            {{-- Métadonnées --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Informations</h2>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Date de création</dt>
                            <dd class="mt-1 text-foreground">{{ $b->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Dernière mise à jour</dt>
                            <dd class="mt-1 text-foreground">{{ $b->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Modales partagées --}}
    <livewire:badge-management.return-modal />
    <livewire:badge-management.edit-number-modal />
    <livewire:badge-management.edit-expiry-modal />
</div>
