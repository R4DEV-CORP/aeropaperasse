<?php

use App\Actions\ActivityRequest\RenewActivityRequestAction;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Mail\ActivityRequestStatusUpdated;
use App\Models\ActivityRequest;
use App\Models\ActivityRequestAttachment;
use App\Services\ActivityRequestDocumentService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => "Demandes d'activité", 'href' => '/activity-requests'],
        ['label' => 'Détail'],
    ],
])]
#[Title("Détail de la demande d'activité")]
class extends Component
{
    use InteractsWithToasts;

    public int $activityRequestId;

    public function mount(int $activityRequestId): void
    {
        if (auth()->user()->isClient()) {
            $this->redirect(route('clients.view', ['slug' => auth()->user()->client->slug]));

            return;
        }

        $activityRequest = ActivityRequest::find($activityRequestId);

        if ($activityRequest === null) {
            abort(404);
        }

        if (! auth()->user()->isAdmin() && $activityRequest->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        $this->activityRequestId = $activityRequest->id;
    }

    #[Computed]
    public function activityRequest(): ActivityRequest
    {
        return ActivityRequest::with(['client', 'creator', 'attachments', 'badgeRequests.coworker', 'vehiclePasses'])
            ->findOrFail($this->activityRequestId);
    }

    #[On('activity-request-rejected')]
    public function refresh(): void
    {
        unset($this->activityRequest);
    }

    public function approve(): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $activityRequest = ActivityRequest::find($this->activityRequestId);
        if ($activityRequest === null || $activityRequest->status !== 'pending') {
            return;
        }

        $activityRequest->update([
            'previous_status' => $activityRequest->status,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $email = $activityRequest->creator?->email;
        if ($activityRequest->client?->notification_email) {
            $email = $activityRequest->client->notification_email;
        }

        if ($email) {
            Mail::to($email)->send(new ActivityRequestStatusUpdated($activityRequest, $activityRequest->client));
        }

        $this->refresh();
        $this->toast('Demande approuvée avec succès.', 'success', 'Demande approuvée');
    }

    public function reopenDraft(): void
    {
        if (! auth()->user()->isSAdmin()) {
            return;
        }

        $activityRequest = ActivityRequest::find($this->activityRequestId);
        if ($activityRequest === null || $activityRequest->status !== 'rejected') {
            return;
        }

        $activityRequest->update([
            'previous_status' => $activityRequest->status,
            'status' => 'draft',
            'draft_at' => now(),
            'reject_reason' => null,
        ]);

        $this->toast('Demande rouverte en brouillon.', 'success', 'Demande rouverte');
        $this->redirectRoute('activity-requests.form', ['activityRequestId' => $activityRequest->id], navigate: true);
    }

    public function renew(RenewActivityRequestAction $action): void
    {
        $activityRequest = ActivityRequest::find($this->activityRequestId);
        if ($activityRequest === null) {
            return;
        }

        if (! auth()->user()->isAdmin() && $activityRequest->client_id !== auth()->user()->client_id) {
            return;
        }

        $result = $action->execute($activityRequest, auth()->id());

        if (! $result->isSuccessful()) {
            $this->toast($result->getMessage(), 'danger', 'Renouvellement impossible');

            return;
        }

        $this->toast($result->getMessage(), 'success', 'Demande renouvelée');
        $this->redirectRoute('activity-requests.show', ['activityRequestId' => $result->getActivityRequest()->id], navigate: true);
    }

    public function downloadDocument(int $attachmentId)
    {
        $attachment = ActivityRequestAttachment::where('id', $attachmentId)
            ->where('activity_request_id', $this->activityRequestId)
            ->first();

        if ($attachment === null || ! Storage::disk('public')->exists($attachment->path)) {
            $this->toast('Document non disponible.', 'warning');

            return null;
        }

        return response()->download(Storage::disk('public')->path($attachment->path), basename($attachment->path));
    }

    public function downloadAllDocuments()
    {
        $service = new ActivityRequestDocumentService;
        $zipPath = $service->createDocumentsZip($this->activityRequest);

        if (! $zipPath) {
            $this->toast('Aucun document disponible pour cette demande.', 'warning');

            return null;
        }

        $zipFileName = 'demande-activite-'.$this->activityRequest->id.'-'.now()->timestamp.'.zip';

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}; ?>

@php
    $statusMeta = [
        'draft' => ['label' => 'Brouillon', 'variant' => 'draft'],
        'pending' => ['label' => 'En attente', 'variant' => 'pending'],
        'approved' => ['label' => 'Approuvée', 'variant' => 'approved'],
        'rejected' => ['label' => 'Rejetée', 'variant' => 'rejected'],
    ];

    $airportMeta = [
        'CDG' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'ORY' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'LBG' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    ];

    $documentSections = [
        ['type' => \App\Models\ActivityRequestAttachment::TYPE_AAO_REQUEST, 'label' => 'Demande AAO'],
        ['type' => \App\Models\ActivityRequestAttachment::TYPE_KBIS, 'label' => 'Extrait KBIS'],
        ['type' => \App\Models\ActivityRequestAttachment::TYPE_PRINCIPALS, 'label' => "Donneurs d'ordre"],
        ['type' => \App\Models\ActivityRequestAttachment::TYPE_SAFETY_REFERENT, 'label' => 'Référent sûreté'],
        ['type' => \App\Models\ActivityRequestAttachment::TYPE_SECURITY_REFERENT, 'label' => 'Référent sécurité'],
        ['type' => \App\Models\ActivityRequestAttachment::TYPE_CTA, 'label' => 'CTA'],
    ];

    $badgeStatusMeta = [
        'draft' => ['label' => 'Brouillon', 'variant' => 'draft'],
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
@endphp

@php
    $ar = $this->activityRequest;
    $status = $statusMeta[$ar->status] ?? ['label' => $ar->status, 'variant' => 'default'];
    $client = $ar->client;
    $airport = $ar->airport;
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isSAdmin = $user->isSAdmin();
    $attachmentsByType = $ar->attachments->groupBy('type');
    $activeBadgeCount = $ar->getActiveBadgeRequestsCount();
    $remainingBadge = $ar->getRemainingBadgeQuota();
    $activeVehicleCount = $ar->getActiveVehiclePassesCount();
    $remainingVehicle = $ar->getRemainingVehiclePassQuota();
@endphp

<div class="space-y-6 p-8">
    {{-- Header compact : retour + titre + statut + download --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('activity-requests.index') }}"
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
                Demande d'activité <span class="font-mono font-semibold text-foreground-muted">#{{ $ar->id }}</span>
            </h1>
            <x-ui.badge :variant="$status['variant']">{{ $status['label'] }}</x-ui.badge>
            @if ($ar->renewal)
                <x-ui.badge variant="info">Renouvellement</x-ui.badge>
            @endif
        </div>

        @if ($isAdmin)
            <x-ui.button variant="secondary" size="sm" wire:click="downloadAllDocuments">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Télécharger les documents
            </x-ui.button>
        @endif
    </div>

    {{-- Card contextuelle : action requise selon le statut --}}
    @php
        $actionContexts = [
            'pending' => [
                'title' => 'Décision requise',
                'description' => 'Vérifiez la demande puis approuvez-la ou rejetez-la avec un motif.',
                'role' => 'admin',
                'accent' => 'amber',
            ],
            'approved' => [
                'title' => 'Demande approuvée',
                'description' => 'Vous pouvez renouveler cette demande pour créer une nouvelle demande pré-remplie.',
                'role' => 'any',
                'accent' => 'emerald',
            ],
            'rejected' => [
                'title' => 'Demande rejetée',
                'description' => 'Vous pouvez la rouvrir en brouillon pour la corriger.',
                'role' => 'sadmin',
                'accent' => 'red',
            ],
        ];

        $ctx = $actionContexts[$ar->status] ?? null;
        $showCtx = $ctx && (
            $ctx['role'] === 'any' ||
            ($ctx['role'] === 'admin' && $isAdmin) ||
            ($ctx['role'] === 'sadmin' && $isSAdmin)
        );

        $accentMap = [
            'amber' => ['bg' => 'bg-amber-50', 'ring' => 'ring-amber-200', 'iconBg' => 'bg-amber-100', 'iconText' => 'text-amber-600'],
            'emerald' => ['bg' => 'bg-emerald-50', 'ring' => 'ring-emerald-200', 'iconBg' => 'bg-emerald-100', 'iconText' => 'text-emerald-600'],
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
                        @if ($isAdmin && $ar->status === 'pending')
                            <x-ui.button variant="success" size="sm" wire:click="approve">Approuver</x-ui.button>
                            <x-ui.button variant="danger" size="sm" wire:click="$dispatch('reject-activity-request', { id: {{ $ar->id }} })">Rejeter</x-ui.button>
                        @endif

                        @if ($ar->status === 'approved')
                            <x-ui.button variant="info" size="sm" wire:click="renew" wire:confirm="Renouveler cette demande approuvée ?">Renouveler la demande</x-ui.button>
                        @endif

                        @if ($isSAdmin && $ar->status === 'rejected')
                            <x-ui.button variant="secondary" size="sm" wire:click="reopenDraft">Rouvrir en brouillon</x-ui.button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bandeau motif de rejet --}}
    @if ($ar->status === 'rejected' && ! empty($ar->reject_reason))
        <x-ui.alert variant="danger">
            <div class="space-y-1">
                <div class="font-semibold">Motif du rejet</div>
                <div class="text-sm">{{ $ar->reject_reason }}</div>
            </div>
        </x-ui.alert>
    @endif

    {{-- Bandeau renouvellement --}}
    @if ($ar->renewal && $ar->last_activity_request_id)
        <x-ui.alert variant="info">
            <div class="flex items-center justify-between gap-4">
                <div>
                    Cette demande est un renouvellement de la demande
                    <span class="font-mono">#{{ $ar->last_activity_request_id }}</span>.
                </div>
                <a
                    href="{{ route('activity-requests.show', ['activityRequestId' => $ar->last_activity_request_id]) }}"
                    wire:navigate
                    class="inline-flex items-center gap-1 text-xs font-semibold text-foreground transition hover:text-brand"
                >
                    Voir la demande d'origine
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Colonne gauche : 2/3 --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Société + Aéroport --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Contexte</h2>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Société</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $client->company_name ?? '—' }}</dd>
                            @if ($client?->trade_name)
                                <dd class="mt-0.5 text-xs text-foreground-muted">{{ $client->trade_name }}</dd>
                            @endif
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
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Description de l'activité</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm text-foreground">{{ $ar->description ?: '—' }}</dd>
                        </div>
                        @if ($ar->customer_names)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Donneurs d'ordre</dt>
                                <dd class="mt-1 whitespace-pre-line text-sm text-foreground">{{ $ar->customer_names }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </x-ui.card>

            {{-- Responsable --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Responsable</h2>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Nom complet</dt>
                            <dd class="mt-1 text-sm text-foreground">
                                {{ trim(($ar->manager_firstname ?? '').' '.($ar->manager_lastname ?? '')) ?: '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Fonction</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $ar->manager_role ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Email</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $ar->manager_email ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Téléphone</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $ar->manager_phone ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>

            {{-- Documents --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Documents</h2>
                    <div class="divide-y divide-border">
                        @foreach ($documentSections as $section)
                            @php
                                $items = $attachmentsByType->get($section['type'], collect());
                            @endphp
                            <div class="space-y-2 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-medium text-foreground">{{ $section['label'] }}</div>
                                    @if ($items->isEmpty())
                                        <span class="text-xs italic text-foreground-subtle">Non fourni</span>
                                    @endif
                                </div>
                                @if ($items->isNotEmpty())
                                    <ul class="space-y-1.5">
                                        @foreach ($items as $att)
                                            @php
                                                $extension = strtolower(pathinfo($att->path, PATHINFO_EXTENSION));
                                                $isViewable = in_array($extension, ['pdf', 'png', 'jpg', 'jpeg'], true);
                                                $exists = Storage::disk('public')->exists($att->path);
                                                $url = $exists && $isViewable ? Storage::disk('public')->url($att->path) : null;
                                            @endphp
                                            <li wire:key="att-{{ $att->id }}" class="flex items-center justify-between gap-3 rounded-md bg-slate-50 px-3 py-2">
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm text-foreground">{{ $att->name ?: basename($att->path) }}</div>
                                                    @if ($att->name && basename($att->path) !== $att->name)
                                                        <div class="truncate text-xs text-foreground-muted">{{ basename($att->path) }}</div>
                                                    @endif
                                                </div>
                                                <div class="flex shrink-0 items-center gap-2">
                                                    @if ($url)
                                                        <a
                                                            href="{{ $url }}"
                                                            target="_blank"
                                                            rel="noopener"
                                                            class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-foreground-muted transition hover:bg-white hover:text-foreground"
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
                                                        wire:click="downloadDocument({{ $att->id }})"
                                                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-foreground-muted transition hover:bg-white hover:text-foreground"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                        </svg>
                                                        Télécharger
                                                    </button>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-ui.card>

            {{-- Demandes de badges liées --}}
            @if ($ar->badgeRequests->isNotEmpty())
                <x-ui.card padding="none">
                    <div class="border-b border-border px-5 py-4">
                        <h2 class="text-base font-semibold text-foreground">Demandes de badges associées</h2>
                        <p class="mt-0.5 text-xs text-foreground-muted">{{ $ar->badgeRequests->count() }} demande(s)</p>
                    </div>
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th>Collaborateur</th>
                                <th>Statut</th>
                                <th>Créée le</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ar->badgeRequests as $br)
                                @php
                                    $brStatus = $badgeStatusMeta[$br->status] ?? ['label' => $br->status, 'variant' => 'default'];
                                @endphp
                                <tr wire:key="br-{{ $br->id }}">
                                    <td>
                                        <div class="font-medium text-foreground">
                                            {{ trim(($br->coworker->firstname ?? '').' '.($br->coworker->lastname ?? '')) ?: '—' }}
                                        </div>
                                        <div class="text-xs text-foreground-muted">{{ $br->coworker->email ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <x-ui.badge :variant="$brStatus['variant']">{{ $brStatus['label'] }}</x-ui.badge>
                                    </td>
                                    <td class="text-foreground-muted">{{ $br->created_at?->format('d/m/Y') }}</td>
                                    <td class="text-right">
                                        <a
                                            href="{{ route('badge-requests.show', ['badgeRequestId' => $br->id]) }}"
                                            wire:navigate
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-foreground transition hover:text-brand"
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
                </x-ui.card>
            @endif
        </div>

        {{-- Colonne droite : 1/3 --}}
        <div class="space-y-6">

            {{-- Quotas --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Quotas</h2>
                    <div class="space-y-3">
                        <div>
                            <div class="flex items-baseline justify-between">
                                <div class="text-sm text-foreground-muted">Badges</div>
                                <div class="text-sm font-semibold text-foreground">{{ $activeBadgeCount }} / {{ $ar->person_count }}</div>
                            </div>
                            <div class="mt-1 text-xs text-foreground-subtle">{{ $remainingBadge }} place(s) restante(s)</div>
                        </div>
                        <div>
                            <div class="flex items-baseline justify-between">
                                <div class="text-sm text-foreground-muted">Laissez-passer véhicules</div>
                                <div class="text-sm font-semibold text-foreground">{{ $activeVehicleCount }} / {{ $ar->vehicule_count }}</div>
                            </div>
                            <div class="mt-1 text-xs text-foreground-subtle">{{ $remainingVehicle }} place(s) restante(s)</div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Suivi du statut --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Suivi du statut</h2>
                    <dl class="space-y-3">
                        @php
                            $timeline = [
                                ['label' => 'Brouillon créé', 'at' => $ar->draft_at],
                                ['label' => 'Soumise', 'at' => $ar->pending_at],
                                ['label' => 'Approuvée', 'at' => $ar->approved_at],
                                ['label' => 'Rejetée', 'at' => $ar->rejected_at],
                            ];
                        @endphp
                        @foreach ($timeline as $step)
                            @if ($step['at'])
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-foreground">{{ $step['label'] }}</div>
                                        <div class="text-xs text-foreground-muted">{{ $step['at']?->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            @endif
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
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Créée par</dt>
                            <dd class="mt-1 text-foreground">{{ $ar->creator?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Date de création</dt>
                            <dd class="mt-1 text-foreground">{{ $ar->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Dernière mise à jour</dt>
                            <dd class="mt-1 text-foreground">{{ $ar->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>
        </div>
    </div>

    <livewire:activity-requests.reject-modal />
</div>
