<?php

use App\Actions\ActivityRequest\RenewActivityRequestAction;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Mail\ActivityRequestStatusUpdated;
use App\Models\ActivityRequest;
use App\Models\Client;
use App\Services\ActivityRequestDocumentService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
        ['label' => "Demandes d'activité"],
    ],
])]
#[Title("Demandes d'activité")]
class extends Component
{
    use InteractsWithToasts;
    use WithoutUrlPagination, WithPagination;

    public string $search = '';

    public ?string $selectedAirport = null;

    public ?string $selectedStatus = null;

    public ?int $selectedClientId = null;

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
        if (! auth()->user()->isAdmin()) {
            return collect();
        }

        return Client::orderBy('company_name')->get();
    }

    #[On('activity-request-rejected')]
    public function refresh(): void
    {
        // Triggers re-render so #[Computed] statistics() and activityRequests() requery.
    }

    #[Computed]
    public function draftActivityRequests()
    {
        $query = ActivityRequest::with('client')
            ->where('status', 'draft');

        if (! auth()->user()->isAdmin()) {
            $query->where('client_id', auth()->user()->client_id);
        }

        return $query->orderBy('updated_at', 'desc')->take(50)->get();
    }

    #[Computed]
    public function statistics(): array
    {
        $query = ActivityRequest::query()->where('status', '!=', 'draft');

        if (! auth()->user()->isAdmin()) {
            $query->where('client_id', auth()->user()->client_id);
        }

        if ($this->selectedClientId && auth()->user()->isAdmin()) {
            $query->where('client_id', $this->selectedClientId);
        }

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
        ];
    }

    #[Computed]
    public function activityRequests()
    {
        if (! empty($this->search)) {
            return $this->buildScoutQuery()->paginate(10);
        }

        $query = ActivityRequest::with('client')
            ->where('status', '!=', 'draft');

        if (! auth()->user()->isAdmin()) {
            $query->where('client_id', auth()->user()->client_id);
        }

        if ($this->selectedClientId && auth()->user()->isAdmin()) {
            $query->where('client_id', $this->selectedClientId);
        }

        if ($this->selectedAirport) {
            $query->where('airport', $this->selectedAirport);
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    private function buildScoutQuery()
    {
        $selectedAirport = $this->selectedAirport;
        $selectedStatus = $this->selectedStatus;
        $selectedClientId = $this->selectedClientId;

        return ActivityRequest::search($this->search)
            ->query(function ($query) use ($selectedAirport, $selectedStatus, $selectedClientId) {
                $query->join('clients', 'activity_requests.client_id', 'clients.id')
                    ->select('activity_requests.*', 'clients.company_name as company_name', 'clients.trade_name as trade_name')
                    ->where('activity_requests.status', '!=', 'draft');

                if (! auth()->user()->isAdmin()) {
                    $query->where('activity_requests.client_id', auth()->user()->client_id);
                }

                if ($selectedClientId && auth()->user()->isAdmin()) {
                    $query->where('activity_requests.client_id', $selectedClientId);
                }

                if ($selectedAirport) {
                    $query->where('activity_requests.airport', $selectedAirport);
                }

                if ($selectedStatus) {
                    $query->where('activity_requests.status', $selectedStatus);
                }
            });
    }

    public function approve(int $activityRequestId): void
    {
        if (! auth()->user()->canChangeRequestStatus()) {
            return;
        }

        $activityRequest = ActivityRequest::findOrFail($activityRequestId);
        $activityRequest->update([
            'previous_status' => $activityRequest->status,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $email = $activityRequest->creator->email;
        if ($activityRequest->client->notification_email) {
            $email = $activityRequest->client->notification_email;
        }

        Mail::to($email)->send(new ActivityRequestStatusUpdated($activityRequest, $activityRequest->client));

        $this->refresh();
        $this->toast('Demande approuvée avec succès.', 'success', 'Demande approuvée');
    }

    public function reopenDraft(int $activityRequestId): void
    {
        if (! auth()->user()->isSAdmin()) {
            return;
        }

        $activityRequest = ActivityRequest::find($activityRequestId);
        if ($activityRequest === null || $activityRequest->status !== 'rejected') {
            return;
        }

        $activityRequest->update([
            'previous_status' => $activityRequest->status,
            'status' => 'draft',
            'draft_at' => now(),
            'reject_reason' => null,
        ]);

        $this->refresh();
        $this->toast('Demande rouverte en brouillon.', 'success', 'Demande rouverte');
    }

    public function renew(int $activityRequestId, RenewActivityRequestAction $action): void
    {
        $activityRequest = ActivityRequest::find($activityRequestId);
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

        $this->refresh();
        $this->toast($result->getMessage(), 'success', 'Demande renouvelée');
    }

    public function downloadDocuments(int $activityRequestId)
    {
        if (! auth()->user()->isAdmin()) {
            return null;
        }

        $activityRequest = ActivityRequest::findOrFail($activityRequestId);

        $documentService = new ActivityRequestDocumentService;
        $zipPath = $documentService->createDocumentsZip($activityRequest);

        if (! $zipPath) {
            $this->toast('Aucun document disponible pour cette demande.', 'warning', 'Téléchargement');

            return null;
        }

        $zipFileName = 'demande-activite-'.$activityRequest->id.'-'.now()->timestamp.'.zip';

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
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
    <div class="space-y-1">
        <h1 class="text-2xl font-bold text-foreground">Demandes d'activité</h1>
        <p class="text-sm text-foreground-muted">Gérez toutes les demandes d'activité depuis cette interface.</p>
    </div>

    {{-- Stats grid --}}
    @php
        $statCards = [
            ['key' => 'pending', 'variant' => 'pending', 'label' => 'En attente', 'ring' => 'ring-amber-500', 'clickable' => true],
            ['key' => 'approved', 'variant' => 'approved', 'label' => 'Approuvées', 'ring' => 'ring-emerald-500', 'clickable' => true],
            ['key' => 'rejected', 'variant' => 'rejected', 'label' => 'Rejetées', 'ring' => 'ring-red-500', 'clickable' => true],
            ['key' => 'total', 'variant' => 'default', 'label' => 'Total', 'ring' => 'ring-slate-400', 'clickable' => false],
        ];
    @endphp
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($statCards as $card)
            @if ($card['clickable'])
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
            @else
                <x-ui.stat-card
                    :variant="$card['variant']"
                    :label="$card['label']"
                    :value="$this->statistics[$card['key']]"
                />
            @endif
        @endforeach
    </div>

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <x-ui.input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Rechercher une société, un responsable..."
                >
                    <x-slot:leadingIcon>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </x-slot:leadingIcon>
                </x-ui.input>
            </div>

            @if (auth()->user()->isAdmin())
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
                <x-ui.button variant="primary" :href="route('activity-requests.form')">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nouvelle demande
                </x-ui.button>
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

    {{-- Drafts accordion --}}
    @php
        $draftsCount = $this->draftActivityRequests->count();
        $hasDrafts = $draftsCount > 0;
    @endphp
    <div
        x-data="{ open: false }"
        x-cloak
        class="overflow-hidden rounded border border-border bg-white shadow-sm"
    >
        @if ($hasDrafts)
            <button
                type="button"
                @click="open = !open"
                :aria-expanded="open"
                class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
            >
                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md bg-slate-100 text-foreground-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </span>

                <div class="flex flex-shrink-0 items-baseline gap-2">
                    <h2 class="text-sm font-semibold text-foreground">Brouillons</h2>
                    <x-ui.badge variant="draft" :dot="false">{{ $draftsCount }}</x-ui.badge>
                    <span class="hidden text-xs text-foreground-muted sm:inline">— demandes en cours de rédaction</span>
                </div>

                <div class="ml-auto flex flex-shrink-0 items-center gap-2 text-xs font-medium text-foreground-muted">
                    <span x-text="open ? 'Replier' : 'Ouvrir'"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 transition-transform duration-200" :class="open && 'rotate-180'">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>
            </button>
        @else
            <div class="flex items-center gap-3 px-4 py-3">
                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md bg-slate-50 text-foreground-subtle">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </span>
                <div class="flex flex-shrink-0 items-baseline gap-2">
                    <h2 class="text-sm font-semibold text-foreground-muted">Brouillons</h2>
                    <span class="text-xs text-foreground-subtle">Aucune demande en cours de rédaction</span>
                </div>
            </div>
        @endif

        @if ($hasDrafts)
            <div x-show="open" x-collapse class="border-t border-border">
                <div class="max-h-[360px] overflow-y-auto">
                    <x-ui.table>
                        <thead class="sticky top-0 z-10 bg-white">
                            <tr>
                                <th>Société</th>
                                <th>Responsable</th>
                                <th>Aéroport</th>
                                <th>Modifié</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->draftActivityRequests as $draft)
                                @php
                                    $fullName = trim(($draft->manager_firstname ?? '').' '.($draft->manager_lastname ?? ''));
                                    $email = $draft->manager_email ?? null;
                                    $phone = $draft->manager_phone ?? null;
                                    $company = $draft->client->company_name ?? null;
                                    $tradeName = $draft->client->trade_name ?? null;
                                    $airport = $draft->airport ?? null;
                                @endphp
                                <tr wire:key="draft-{{ $draft->id }}" class="transition hover:bg-slate-50">
                                    <td>
                                        @if ($company)
                                            <div class="font-medium text-foreground">{{ $company }}</div>
                                            @if ($tradeName)
                                                <div class="text-xs text-foreground-muted">{{ $tradeName }}</div>
                                            @endif
                                        @else
                                            <span class="text-foreground-subtle">—</span>
                                        @endif
                                        <div class="text-xs text-foreground-muted">
                                            Créé le {{ $draft->created_at->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($fullName !== '')
                                            <div class="font-medium text-foreground">{{ $fullName }}</div>
                                        @else
                                            <div class="font-medium italic text-foreground-subtle">Responsable non renseigné</div>
                                        @endif
                                        <div class="text-xs text-foreground-muted">{{ $email ?: '—' }}</div>
                                        <div class="text-xs text-foreground-muted">{{ $phone ?: '—' }}</div>
                                    </td>
                                    <td>
                                        @if ($airport)
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                                {{ $airport }}
                                            </span>
                                        @else
                                            <span class="text-foreground-subtle">—</span>
                                        @endif
                                    </td>
                                    <td class="text-foreground-muted">
                                        <div>{{ $draft->updated_at->diffForHumans() }}</div>
                                        <div class="text-xs text-foreground-subtle">{{ $draft->updated_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="text-right">
                                        <a
                                            href="{{ route('activity-requests.form', ['activityRequestId' => $draft->id]) }}"
                                            wire:navigate
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-foreground transition hover:text-brand"
                                        >
                                            Reprendre
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                </div>
            </div>
        @endif
    </div>

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
            <h2 class="text-base font-semibold text-foreground">Demandes récentes</h2>
            <p class="mt-0.5 text-xs text-foreground-muted">{{ $this->activityRequests->total() }} {{ $this->activityRequests->total() > 1 ? 'demandes' : 'demande' }}</p>
        </div>

        <x-ui.table>
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Société</th>
                    <th>Responsable</th>
                    <th>Statut</th>
                    <th>Description</th>
                    <th>Date de création</th>
                    <th>Aéroport</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->activityRequests as $activityRequest)
                    @php
                        $status = $statusMeta[$activityRequest->status] ?? ['label' => $activityRequest->status, 'variant' => 'default'];
                        $airport = $activityRequest->airport;
                    @endphp
                    <tr wire:key="activity-request-{{ $activityRequest->id }}">
                        <td>
                            <span class="font-mono text-xs text-foreground-muted">#{{ $activityRequest->id }}</span>
                        </td>
                        <td>
                            <div class="font-medium text-foreground">{{ $activityRequest->client->company_name ?? '—' }}</div>
                            <div class="text-xs text-foreground-muted">{{ $activityRequest->client->trade_name }}</div>
                        </td>
                        <td>
                            <div class="font-medium text-foreground">{{ $activityRequest->manager_firstname }} {{ $activityRequest->manager_lastname }}</div>
                            <div class="text-xs text-foreground-muted">{{ $activityRequest->manager_email }}</div>
                            <div class="text-xs text-foreground-muted">{{ $activityRequest->manager_phone }}</div>
                        </td>
                        <td>
                            <x-ui.badge :variant="$status['variant']">{{ $status['label'] }}</x-ui.badge>
                        </td>
                        <td class="max-w-xs">
                            <p class="text-foreground-muted" title="{{ $activityRequest->description }}">{{ Str::limit($activityRequest->description, 50) }}</p>
                        </td>
                        <td class="text-foreground-muted">{{ $activityRequest->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if ($airport)
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                    {{ $airport }}
                                </span>
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

                                <x-ui.dropdown-item :href="route('activity-requests.show', ['activityRequestId' => $activityRequest->id])" wire:navigate>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Voir les détails
                                </x-ui.dropdown-item>

                                @if ($activityRequest->status === 'pending' && auth()->user()->canChangeRequestStatus())
                                    <x-ui.dropdown-item variant="success" wire:click="approve({{ $activityRequest->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        Approuver
                                    </x-ui.dropdown-item>

                                    <x-ui.dropdown-item variant="danger" wire:click="$dispatch('reject-activity-request', { id: {{ $activityRequest->id }} })">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        Rejeter
                                    </x-ui.dropdown-item>
                                @endif

                                @if ($activityRequest->status === 'approved')
                                    <x-ui.dropdown-item wire:click="renew({{ $activityRequest->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        Renouveler la demande
                                    </x-ui.dropdown-item>
                                @endif

                                @if ($activityRequest->status === 'rejected' && auth()->user()->isSAdmin())
                                    <x-ui.dropdown-item wire:click="reopenDraft({{ $activityRequest->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                        </svg>
                                        Rouvrir en brouillon
                                    </x-ui.dropdown-item>
                                @endif

                                @if (auth()->user()->isAdmin())
                                    <x-ui.dropdown-item wire:click="downloadDocuments({{ $activityRequest->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        Télécharger documents
                                    </x-ui.dropdown-item>
                                @endif
                            </x-ui.dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-foreground-muted">
                            <div class="py-12">Aucune demande à afficher.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        @if ($this->activityRequests->hasPages())
            <div class="border-t border-border px-5 py-3">
                {{ $this->activityRequests->links() }}
            </div>
        @endif
    </x-ui.card>

    <livewire:activity-requests.reject-modal />
</div>
