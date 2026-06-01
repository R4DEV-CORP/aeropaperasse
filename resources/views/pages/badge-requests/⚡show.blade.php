<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Badge;
use App\Models\BadgeRequest;
use App\Services\BadgeRequestDocumentService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Demandes de badges', 'href' => '/badge-requests'],
        ['label' => 'Détail'],
    ],
])]
#[Title('Détail de la demande de badge')]
class extends Component
{
    use InteractsWithToasts;

    public int $badgeRequestId;

    public function mount(int $badgeRequestId): void
    {
        $badgeRequest = BadgeRequest::with(['coworker', 'activityRequest.client', 'creator', 'badge'])
            ->find($badgeRequestId);

        if ($badgeRequest === null) {
            abort(404);
        }

        $user = auth()->user();
        if (! $user->isTenantManager()) {
            $clientId = $badgeRequest->activityRequest?->client_id;
            if ($clientId !== $user->contextualClientId()) {
                abort(403);
            }
            if ($user->isClient() && $badgeRequest->activityRequest?->created_by !== $user->id) {
                abort(403);
            }
        }

        $this->badgeRequestId = $badgeRequest->id;
    }

    #[Computed]
    public function badgeRequest(): BadgeRequest
    {
        return BadgeRequest::with(['coworker', 'activityRequest.client', 'creator', 'badge'])
            ->findOrFail($this->badgeRequestId);
    }

    #[On('badge-request-status-updated')]
    public function refresh(): void
    {
        unset($this->badgeRequest);
    }

    public function validateRem(): void
    {
        $this->applyStatusTransition('pending_rem', 'pending_adp', 'Demande validée.');
    }

    public function approveAdp(): void
    {
        $this->applyStatusTransition('pending_adp', 'approved_adp', 'Demande approuvée par ADP.');
    }

    public function startFabrication(): void
    {
        $this->applyStatusTransition('approved_adp', 'pending_fabrication', 'Fabrication lancée.');
    }

    public function markReadyForDelivery(): void
    {
        if (! auth()->user()->canChangeRequestStatus()) {
            return;
        }

        $badgeRequest = BadgeRequest::find($this->badgeRequestId);
        if ($badgeRequest === null || $badgeRequest->status !== 'pending_fabrication') {
            return;
        }

        $badgeRequest->update([
            'status' => 'ready_for_delivery',
            'ready_for_delivery_at' => now(),
        ]);

        Badge::firstOrCreate(
            ['badge_request_id' => $badgeRequest->id],
            ['status' => 'active', 'expiry_date' => null],
        );

        $this->refresh();
        $this->toast('Badge prêt à être remis.', 'success', 'Statut mis à jour');
    }

    public function reopenDraft(): void
    {
        if (! auth()->user()->isSAdmin()) {
            return;
        }

        $badgeRequest = BadgeRequest::find($this->badgeRequestId);
        if ($badgeRequest === null || ! in_array($badgeRequest->status, ['rejected_rem', 'rejected_adp'], true)) {
            return;
        }

        $badgeRequest->update([
            'status' => 'draft',
            'draft_at' => now(),
            'reject_reason' => null,
        ]);

        $this->toast('Demande rouverte en brouillon.', 'success', 'Demande rouverte');
        $this->redirectRoute('badge-requests.form', ['badgeRequestId' => $badgeRequest->id], navigate: true);
    }

    protected function applyStatusTransition(string $expectedStatus, string $newStatus, string $successMessage): void
    {
        if (! auth()->user()->canChangeRequestStatus()) {
            return;
        }

        $badgeRequest = BadgeRequest::find($this->badgeRequestId);
        if ($badgeRequest === null || $badgeRequest->status !== $expectedStatus) {
            return;
        }

        $badgeRequest->update([
            'status' => $newStatus,
            $newStatus.'_at' => now(),
        ]);

        $this->refresh();
        $this->toast($successMessage, 'success', 'Statut mis à jour');
    }

    public function downloadDocument(string $documentType)
    {
        $service = new BadgeRequestDocumentService;
        $relativePath = $service->getDocumentPath($this->badgeRequest, $documentType);

        if (! $relativePath || ! Storage::disk('public')->exists($relativePath)) {
            $this->toast('Document non disponible.', 'warning');

            return null;
        }

        return response()->download(Storage::disk('public')->path($relativePath), basename($relativePath));
    }

    public function downloadAllDocuments()
    {
        $service = new BadgeRequestDocumentService;
        $zipPath = $service->createDocumentsZip($this->badgeRequest);

        if (! $zipPath) {
            $this->toast('Aucun document disponible pour cette demande.', 'warning');

            return null;
        }

        $zipFileName = 'demande-badge-'.$this->badgeRequest->id.'-'.now()->timestamp.'.zip';

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}; ?>

@php
    $statusMeta = [
        'draft' => ['label' => 'Brouillon', 'variant' => 'draft'],
        'pending_rem' => ['label' => 'En attente', 'variant' => 'pending'],
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

    $documents = [
        ['key' => 'selfie_photo', 'label' => "Photo d'identité"],
        ['key' => 'identification_card', 'label' => "Pièce d'identité"],
        ['key' => 'activity_authorization', 'label' => "Autorisation d'activité"],
        ['key' => 'for_document', 'label' => 'Document FOR'],
        ['key' => 'formation_certificate_document', 'label' => 'Certificat de formation'],
        ['key' => 'invoice_document', 'label' => 'Facture'],
    ];
@endphp

@php
    $br = $this->badgeRequest;
    $status = $statusMeta[$br->status] ?? ['label' => $br->status, 'variant' => 'default'];
    $coworker = $br->coworker;
    $ar = $br->activityRequest;
    $client = $ar?->client;
    $airport = $ar?->airport;
    $user = auth()->user();
    $isAdmin = $user->isTenantManager();
    $isSAdmin = $user->isSAdmin();
    $canChangeStatus = $user->canChangeRequestStatus();
    // Les "client" simples n'ont pas le droit de créer une demande — ils ne peuvent donc pas non plus la corriger.
    $canCorrectRejected = ! $user->isClient();
@endphp

<div class="space-y-6 p-8">
    {{-- Header compact : retour + titre + statut + download --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('badge-requests.index') }}"
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
                Demande de badge <span class="font-mono font-semibold text-foreground-muted">#{{ $br->id }}</span>
            </h1>
            <x-ui.badge :variant="$status['variant']">{{ $status['label'] }}</x-ui.badge>
        </div>

        <x-ui.button variant="secondary" size="sm" wire:click="downloadAllDocuments">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Télécharger les documents
        </x-ui.button>
    </div>

    {{-- Card contextuelle : action requise selon le statut --}}
    @php
        $actionContexts = [
            'pending_rem' => [
                'title' => 'Validation requise',
                'description' => 'Vérifiez le dossier puis validez la demande ou marquez-la comme incomplète.',
                'role' => 'status_changer',
                'accent' => 'amber',
            ],
            'pending_adp' => [
                'title' => 'Approbation ADP requise',
                'description' => 'La demande a été validée, elle attend maintenant la décision ADP.',
                'role' => 'status_changer',
                'accent' => 'amber',
            ],
            'approved_adp' => [
                'title' => 'Prête pour la fabrication',
                'description' => 'La demande est approuvée — vous pouvez lancer la fabrication du badge.',
                'role' => 'status_changer',
                'accent' => 'emerald',
            ],
            'pending_fabrication' => [
                'title' => 'Fabrication en cours',
                'description' => "Dès que le badge sort de fabrication, marquez-le comme prêt à être remis.",
                'role' => 'status_changer',
                'accent' => 'violet',
            ],
            'ready_for_delivery' => [
                'title' => 'Prêt à être remis',
                'description' => 'Le badge peut être remis au demandeur. Une photo de remise sera demandée.',
                'role' => 'status_changer',
                'accent' => 'blue',
            ],
            'rejected_rem' => [
                'title' => 'Demande refusée',
                'description' => 'Corrigez le dossier puis resoumettez la demande pour relancer la validation.',
                'role' => 'rejected',
                'accent' => 'red',
            ],
            'rejected_adp' => [
                'title' => 'Demande refusée par ADP',
                'description' => 'Corrigez le dossier puis resoumettez la demande pour relancer la validation.',
                'role' => 'rejected',
                'accent' => 'red',
            ],
        ];

        $ctx = $actionContexts[$br->status] ?? null;
        $showCtx = $ctx && (
            ($ctx['role'] === 'status_changer' && $canChangeStatus) ||
            ($ctx['role'] === 'rem_super_admin' && $isSAdmin) ||
            ($ctx['role'] === 'rejected' && ($canCorrectRejected || $isSAdmin))
        );

        $accentMap = [
            'amber' => ['bg' => 'bg-amber-50', 'ring' => 'ring-amber-200', 'iconBg' => 'bg-amber-100', 'iconText' => 'text-amber-600'],
            'emerald' => ['bg' => 'bg-emerald-50', 'ring' => 'ring-emerald-200', 'iconBg' => 'bg-emerald-100', 'iconText' => 'text-emerald-600'],
            'violet' => ['bg' => 'bg-violet-50', 'ring' => 'ring-violet-200', 'iconBg' => 'bg-violet-100', 'iconText' => 'text-violet-600'],
            'blue' => ['bg' => 'bg-blue-50', 'ring' => 'ring-blue-200', 'iconBg' => 'bg-blue-100', 'iconText' => 'text-blue-600'],
            'red' => ['bg' => 'bg-red-50', 'ring' => 'ring-red-200', 'iconBg' => 'bg-red-100', 'iconText' => 'text-red-600'],
        ];
        $accent = $showCtx ? $accentMap[$ctx['accent']] : null;
    @endphp

    @if ($showCtx)
        <div class="rounded-lg {{ $accent['bg'] }} ring-1 {{ $accent['ring'] }} p-5">
            <div class="flex items-start gap-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $accent['iconBg'] }} {{ $accent['iconText'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </span>
                <div class="flex-1 space-y-3">
                    <div>
                        <h2 class="text-sm font-semibold text-foreground">{{ $ctx['title'] }}</h2>
                        <p class="mt-0.5 text-sm text-foreground-muted">{{ $ctx['description'] }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($canChangeStatus && $br->status === 'pending_rem')
                            <x-ui.button variant="success" size="sm" wire:click="validateRem">Valider</x-ui.button>
                            <x-ui.button variant="danger" size="sm" wire:click="$dispatch('reject-badge-request', { id: {{ $br->id }}, targetStatus: 'rejected_rem' })">Marquer dossier incomplet</x-ui.button>
                        @endif

                        @if ($canChangeStatus && $br->status === 'pending_adp')
                            <x-ui.button variant="success" size="sm" wire:click="approveAdp">Approuver ADP</x-ui.button>
                            <x-ui.button variant="danger" size="sm" wire:click="$dispatch('reject-badge-request', { id: {{ $br->id }}, targetStatus: 'rejected_adp' })">Rejeter ADP</x-ui.button>
                        @endif

                        @if ($canChangeStatus && $br->status === 'approved_adp')
                            <x-ui.button variant="info" size="sm" wire:click="startFabrication">Lancer la fabrication</x-ui.button>
                        @endif

                        @if ($canChangeStatus && $br->status === 'pending_fabrication')
                            <x-ui.button variant="info" size="sm" wire:click="markReadyForDelivery">Marquer prêt à remettre</x-ui.button>
                        @endif

                        @if ($canChangeStatus && $br->status === 'ready_for_delivery')
                            <x-ui.button variant="info" size="sm" wire:click="$dispatch('deliver-badge-request', { id: {{ $br->id }} })">Marquer comme remis</x-ui.button>
                        @endif

                        @if ($canCorrectRejected && in_array($br->status, ['rejected_rem', 'rejected_adp']))
                            <x-ui.button
                                variant="danger"
                                size="sm"
                                :href="route('badge-requests.form', ['badgeRequestId' => $br->id])"
                                wire:navigate
                            >
                                Corriger et resoumettre
                            </x-ui.button>
                        @endif

                        @if ($isSAdmin && in_array($br->status, ['rejected_rem', 'rejected_adp']))
                            <x-ui.button variant="secondary" size="sm" wire:click="reopenDraft">Rouvrir en brouillon</x-ui.button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bandeau motif de rejet --}}
    @if (in_array($br->status, ['rejected_rem', 'rejected_adp']) && ! empty($br->reject_reason))
        <x-ui.alert variant="danger">
            <div class="space-y-1">
                <div class="font-semibold">Motif du rejet</div>
                <div class="text-sm">{{ $br->reject_reason }}</div>
            </div>
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Colonne gauche : 2/3 --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Demandeur --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Collaborateur</h2>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Nom complet</dt>
                            <dd class="mt-1 text-sm text-foreground">
                                {{ trim(($coworker->firstname ?? '').' '.($coworker->lastname ?? '')) ?: '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Email</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $coworker->email ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Téléphone</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $coworker->phone ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Société</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $client->company_name ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>

            {{-- Demande d'activité --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-foreground">Demande d'activité</h2>
                        @if ($ar)
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
                        @endif
                    </div>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Numéro</dt>
                            <dd class="mt-1 font-mono text-sm text-foreground">#{{ $ar->id ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Aéroport</dt>
                            <dd class="mt-1">
                                @if ($airport)
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                        {{ $airport }}
                                    </span>
                                @else
                                    <span class="text-sm text-foreground-subtle">—</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Responsable</dt>
                            <dd class="mt-1 text-sm text-foreground">
                                {{ trim(($ar->manager_firstname ?? '').' '.($ar->manager_lastname ?? '')) ?: '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Email responsable</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $ar->manager_email ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>

            {{-- Documents --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Documents</h2>
                    <div class="divide-y divide-border">
                        @foreach ($documents as $doc)
                            @php
                                $path = $br->{$doc['key']} ?? null;
                                $hasFile = ! empty($path) && Storage::disk('public')->exists($path);
                                $extension = $hasFile ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
                                $isViewable = in_array($extension, ['pdf', 'png', 'jpg', 'jpeg'], true);
                                $url = $hasFile && $isViewable ? Storage::disk('public')->url($path) : null;
                            @endphp
                            <div class="flex items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-foreground">{{ $doc['label'] }}</div>
                                    @if ($hasFile)
                                        <div class="text-xs text-foreground-muted">{{ basename($path) }}</div>
                                    @else
                                        <div class="text-xs italic text-foreground-subtle">Non fourni</div>
                                    @endif
                                </div>
                                @if ($hasFile)
                                    <div class="flex shrink-0 items-center gap-2">
                                        @if ($isViewable && $url)
                                            <a
                                                href="{{ $url }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-foreground-muted transition hover:bg-slate-100 hover:text-foreground"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                                Voir
                                            </a>
                                        @endif
                                        <button
                                            type="button"
                                            wire:click="downloadDocument('{{ $doc['key'] }}')"
                                            class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-foreground-muted transition hover:bg-slate-100 hover:text-foreground"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                            Télécharger
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-ui.card>

            {{-- Badge physique (si delivered) --}}
            @if (in_array($br->status, ['delivered', 'ready_for_delivery']))
                <x-ui.card>
                    <div class="space-y-4">
                        <h2 class="text-base font-semibold text-foreground">Badge physique</h2>
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Numéro</dt>
                                <dd class="mt-1 font-mono text-sm text-foreground">{{ $br->badge?->badge_number ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Date d'expiration</dt>
                                <dd class="mt-1 text-sm text-foreground">
                                    {{ $br->badge?->expiry_date?->format('d/m/Y') ?? '—' }}
                                </dd>
                            </div>
                        </dl>

                        @if ($br->status === 'delivered' && $br->delivery_photo && Storage::disk('public')->exists($br->delivery_photo))
                            <div class="space-y-2">
                                <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Preuve de remise</div>
                                <a href="{{ Storage::disk('public')->url($br->delivery_photo) }}" target="_blank" rel="noopener" class="inline-block">
                                    <img
                                        src="{{ Storage::disk('public')->url($br->delivery_photo) }}"
                                        alt="Photo de remise"
                                        class="max-h-64 rounded-md border border-border object-cover"
                                    />
                                </a>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            @endif
        </div>

        {{-- Colonne droite : 1/3 --}}
        <div class="space-y-6">
            {{-- Récap statut + dates --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Suivi du statut</h2>
                    <dl class="space-y-3">
                        @php
                            $timeline = [
                                ['label' => 'Brouillon créé', 'at' => $br->draft_at],
                                ['label' => 'Soumise à validation', 'at' => $br->pending_rem_at],
                                ['label' => 'Validée', 'at' => $br->pending_adp_at],
                                ['label' => 'Validation refusée', 'at' => $br->rejected_rem_at],
                                ['label' => 'Approuvé par ADP', 'at' => $br->approved_adp_at],
                                ['label' => 'Refusé par ADP', 'at' => $br->rejected_adp_at],
                                ['label' => 'En fabrication', 'at' => $br->pending_fabrication_at],
                                ['label' => 'Prêt à être remis', 'at' => $br->ready_for_delivery_at],
                                ['label' => 'Remis', 'at' => $br->delivered_at],
                                ['label' => 'Terminé', 'at' => $br->terminated_at],
                            ];
                        @endphp
                        @foreach ($timeline as $step)
                            @if ($step['at'])
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-foreground">{{ $step['label'] }}</div>
                                        <div class="text-xs text-foreground-muted">{{ $step['at']->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </div>
            </x-ui.card>

            {{-- Options --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Options</h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-foreground-muted">Habilitation</dt>
                            <dd>
                                <x-ui.badge :variant="$br->application_authorization ? 'approved' : 'default'">
                                    {{ $br->application_authorization ? 'Oui' : 'Non' }}
                                </x-ui.badge>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-foreground-muted">Formation validée</dt>
                            <dd>
                                <x-ui.badge :variant="$br->validate_training ? 'approved' : 'default'">
                                    {{ $br->validate_training ? 'Oui' : 'Non' }}
                                </x-ui.badge>
                            </dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>

            {{-- Métadonnées --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Informations</h2>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Créé par</dt>
                            <dd class="mt-1 text-foreground">{{ $br->creator?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Date de création</dt>
                            <dd class="mt-1 text-foreground">{{ $br->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Dernière mise à jour</dt>
                            <dd class="mt-1 text-foreground">{{ $br->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>
        </div>
    </div>

    <livewire:badge-requests.reject-modal />
    <livewire:badge-requests.deliver-modal />
</div>
