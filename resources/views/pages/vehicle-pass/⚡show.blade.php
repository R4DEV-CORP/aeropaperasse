<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\VehiclePass;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Laissez-passer', 'href' => '/vehicle-pass'],
        ['label' => 'Détail'],
    ],
])]
#[Title('Détail du laissez-passer')]
class extends Component
{
    use InteractsWithToasts;

    public int $vehiclePassId;

    public function mount(int $vehiclePassId): void
    {
        $vp = VehiclePass::find($vehiclePassId);
        if (! $vp) {
            abort(404);
        }

        $authUser = auth()->user();

        if (! $authUser->isAdmin() && $vp->client_id !== $authUser->client_id) {
            abort(403);
        }

        $this->vehiclePassId = $vp->id;
    }

    #[Computed]
    public function vehiclePass(): VehiclePass
    {
        return VehiclePass::with(['client', 'activityRequest', 'createdBy'])
            ->findOrFail($this->vehiclePassId);
    }

    #[On('vehicle-pass-approved')]
    #[On('vehicle-pass-rejected')]
    public function refresh(): void
    {
        unset($this->vehiclePass);
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

    $vp = $this->vehiclePass;
    $status = $statusMeta[$vp->status] ?? ['label' => $vp->status, 'variant' => 'default'];
    $isAdmin = auth()->user()->isAdmin();
    $isPending = $vp->status === 'pending';

    $documents = [
        'certificate_of_registration' => [
            'label' => 'Carte grise',
            'path' => $vp->certificate_of_registration,
        ],
        'company_stamp' => [
            'label' => "Tampon de l'entreprise",
            'path' => $vp->company_stamp,
        ],
    ];
@endphp

<div class="space-y-6 p-8">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('vehicle-pass.index') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 text-sm font-medium text-foreground-muted transition hover:text-foreground"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Retour
            </a>
            <span class="text-foreground-subtle">/</span>
            <h1 class="text-xl font-bold leading-tight text-foreground">Laissez-passer #{{ $vp->id }}</h1>
            <x-ui.badge :variant="$status['variant']">{{ $status['label'] }}</x-ui.badge>
            @if (! $vp->activity_request_id)
                <x-ui.badge variant="default">Indépendante</x-ui.badge>
            @endif
        </div>

        @if ($isAdmin && $isPending)
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button variant="success" size="sm" wire:click="$dispatch('open-approve-vehicle-pass', { id: {{ $vp->id }} })">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Approuver
                </x-ui.button>
                <x-ui.button variant="danger" size="sm" wire:click="$dispatch('open-reject-vehicle-pass', { id: {{ $vp->id }} })">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    Rejeter
                </x-ui.button>
            </div>
        @endif
    </div>

    @if ($vp->status === 'rejected' && $vp->reject_reason)
        <x-ui.alert variant="danger">
            <span class="font-semibold">Raison du rejet :</span> {{ $vp->reject_reason }}
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Colonne gauche --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Véhicule --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Véhicule</h2>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Immatriculation</dt>
                            <dd class="mt-1 font-mono text-sm text-foreground">{{ $vp->plate_number ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Marque</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $vp->car_brand ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Aéroport</dt>
                            <dd class="mt-1">
                                @if ($vp->airport)
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$vp->airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                        {{ $vp->airport }}
                                    </span>
                                @else
                                    <span class="text-foreground-subtle">—</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>

            {{-- Société + demande d'activité liée --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Société & demande d'activité</h2>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Société</dt>
                            <dd class="mt-1 text-sm">
                                @if ($vp->client)
                                    <a href="{{ route('companies.show', ['companyId' => $vp->client->id]) }}" wire:navigate class="inline-flex items-center gap-1 text-foreground transition hover:text-accent">
                                        {{ $vp->client->company_name }}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="text-foreground-muted">—</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Demande d'activité</dt>
                            <dd class="mt-1 text-sm">
                                @if ($vp->activityRequest)
                                    <a href="{{ route('activity-requests.show', ['activityRequestId' => $vp->activityRequest->id]) }}" wire:navigate class="inline-flex items-center gap-1 text-foreground transition hover:text-accent">
                                        Demande #{{ $vp->activityRequest->id }}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="text-foreground-muted">—</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>

            {{-- Documents --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Documents</h2>
                    <ul class="space-y-2">
                        @foreach ($documents as $doc)
                            <li class="flex items-center justify-between gap-3 rounded-md border border-border px-3 py-2">
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-foreground">{{ $doc['label'] }}</div>
                                    @if ($doc['path'])
                                        <div class="text-xs text-foreground-muted">{{ basename($doc['path']) }}</div>
                                    @else
                                        <div class="text-xs text-foreground-muted">Non fourni</div>
                                    @endif
                                </div>
                                @if ($doc['path'])
                                    <a
                                        href="{{ asset('storage/'.$doc['path']) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex h-8 items-center gap-1 rounded-md bg-white px-2.5 text-xs font-semibold text-foreground-muted ring-1 ring-border transition hover:bg-slate-50 hover:text-foreground"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-3.5 w-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        Télécharger
                                    </a>
                                @else
                                    <x-ui.badge variant="default">Absent</x-ui.badge>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </x-ui.card>
        </div>

        {{-- Colonne droite --}}
        <div class="space-y-6">
            {{-- Statut & historique --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Suivi</h2>
                    <ul class="space-y-3">
                        <li class="flex gap-3">
                            <span class="mt-1 flex h-2 w-2 flex-shrink-0 rounded-full bg-slate-300"></span>
                            <div class="min-w-0">
                                <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Créée</div>
                                <div class="text-sm text-foreground">{{ $vp->created_at->format('d/m/Y à H:i') }}</div>
                            </div>
                        </li>
                        @if ($vp->pending_at)
                            <li class="flex gap-3">
                                <span class="mt-1 flex h-2 w-2 flex-shrink-0 rounded-full bg-amber-500"></span>
                                <div class="min-w-0">
                                    <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Soumise</div>
                                    <div class="text-sm text-foreground">{{ $vp->pending_at->format('d/m/Y à H:i') }}</div>
                                </div>
                            </li>
                        @endif
                        @if ($vp->approved_at)
                            <li class="flex gap-3">
                                <span class="mt-1 flex h-2 w-2 flex-shrink-0 rounded-full bg-emerald-500"></span>
                                <div class="min-w-0">
                                    <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Approuvée</div>
                                    <div class="text-sm text-foreground">{{ $vp->approved_at->format('d/m/Y à H:i') }}</div>
                                </div>
                            </li>
                        @endif
                        @if ($vp->rejected_at)
                            <li class="flex gap-3">
                                <span class="mt-1 flex h-2 w-2 flex-shrink-0 rounded-full bg-red-500"></span>
                                <div class="min-w-0">
                                    <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Rejetée</div>
                                    <div class="text-sm text-foreground">{{ $vp->rejected_at->format('d/m/Y à H:i') }}</div>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </x-ui.card>

            {{-- Métadonnées --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Informations</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Créée par</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $vp->createdBy?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Dernière mise à jour</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $vp->updated_at->format('d/m/Y à H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Modales partagées --}}
    <livewire:vehicle-pass.approve-modal />
    <livewire:vehicle-pass.reject-modal />
</div>
