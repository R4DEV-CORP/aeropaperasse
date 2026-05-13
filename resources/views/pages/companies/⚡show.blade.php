<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Client;
use App\Services\ClientOverviewPdfService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Sociétés', 'href' => '/companies'],
        ['label' => 'Détail'],
    ],
])]
#[Title('Détail de la société')]
class extends Component
{
    use InteractsWithToasts;

    public int $companyId;

    public function mount(int $companyId): void
    {
        $company = Client::find($companyId);
        if (! $company) {
            abort(404);
        }

        $authUser = auth()->user();

        if (! $authUser->isAdmin() && $company->id !== $authUser->client_id) {
            abort(403);
        }

        $this->companyId = $company->id;
    }

    #[Computed]
    public function company(): Client
    {
        return Client::with(['contacts'])->findOrFail($this->companyId);
    }

    #[Computed]
    public function stats(): array
    {
        $company = $this->company;

        return [
            'coworkers' => $company->coworkers()->count(),
            'users' => $company->users()->count(),
            'activeBadges' => $company->getActiveBadgeCount(),
            'activeTrainings' => $company->getActiveTrainingCount(),
            'activityRequests' => $company->activityRequests()->count(),
        ];
    }

    public function downloadOverview()
    {
        $service = app(ClientOverviewPdfService::class);
        $pdf = $service->generateOverview($this->companyId);

        $company = $this->company;
        $filename = "Bilan_{$company->company_name}_".date('Y-m-d').'.pdf';

        $tempDir = storage_path('app/temp');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir.'/'.uniqid().'_'.$filename;
        $pdf->save($tempPath);

        $this->toast('Bilan généré.', 'success', 'Téléchargement');

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    #[On('company-saved')]
    public function refresh(): void
    {
        unset($this->company, $this->stats);
        $this->toast('Société mise à jour.', 'success', 'Mise à jour réussie');
    }
}; ?>

@php
    $authUser = auth()->user();
    $isAdmin = $authUser->isAdmin();
    $isClient = $authUser->isClient();
    $canEdit = ! $isClient;

    $company = $this->company;
    $stats = $this->stats;
    $contacts = $company->contacts;
    $safetyReferents = $contacts->where('role', 'safety')->values();
    $securityCorrespondent = $contacts->where('role', 'security')->first();
    $hrContact = $contacts->where('role', 'hr')->first();

    $docs = [
        ['label' => 'KBIS', 'path' => $company->kbis_document],
        ['label' => 'Référents sûreté', 'path' => $company->safety_document],
        ['label' => 'Correspondant sécurité', 'path' => $company->security_document],
    ];
@endphp

<div class="space-y-6 p-8">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            @if ($isAdmin)
                <a
                    href="{{ route('companies.index') }}"
                    wire:navigate
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-foreground-muted transition hover:text-foreground"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Retour
                </a>
                <span class="text-foreground-subtle">/</span>
            @endif
            <h1 class="text-xl font-bold leading-tight text-foreground">{{ $company->company_name }}</h1>
            @if ($company->is_airline_company)
                <x-ui.badge variant="approved">Compagnie aérienne</x-ui.badge>
            @else
                <x-ui.badge variant="default">Non aérienne</x-ui.badge>
            @endif
            @if ($company->siret_number)
                <span class="font-mono text-xs text-foreground-muted">SIRET {{ $company->siret_number }}</span>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-ui.button wire:click="downloadOverview" variant="secondary" size="sm">
                <span wire:loading.remove wire:target="downloadOverview" class="inline-flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Télécharger le bilan
                </span>
                <span wire:loading wire:target="downloadOverview">Génération…</span>
            </x-ui.button>

            @if ($canEdit)
                <x-ui.button :href="route('companies.form', ['companyId' => $company->id])" wire:navigate variant="primary" size="sm">
                    Modifier
                </x-ui.button>
            @endif
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <x-ui.stat-card variant="default" label="Collaborateurs" :value="$stats['coworkers']" />
        <x-ui.stat-card variant="ready" label="Utilisateurs" :value="$stats['users']" />
        <x-ui.stat-card variant="approved" label="Badges actifs" :value="$stats['activeBadges']" />
        <x-ui.stat-card variant="in-progress" label="Formations actives" :value="$stats['activeTrainings']" />
        <x-ui.stat-card variant="pending" label="Demandes d'activité" :value="$stats['activityRequests']" />
    </div>

    {{-- Section: Identité --}}
    <div class="space-y-3">
        <div class="flex items-baseline justify-between">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-foreground-muted">Identité</h2>
            <span class="text-xs text-foreground-subtle">Créée le {{ $company->created_at->format('d/m/Y') }}</span>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- Informations société --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h3 class="text-base font-semibold text-foreground">Informations société</h3>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Raison sociale</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $company->company_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Nom commercial</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $company->trade_name ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">SIRET</dt>
                            <dd class="mt-1 font-mono text-sm text-foreground">{{ $company->siret_number ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Sous-traitant de</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $company->subcontractor_of ?: 'Aucun' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Email de notification</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $company->notification_email ?: 'Aucun' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>

            {{-- Adresse --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h3 class="text-base font-semibold text-foreground">Adresse</h3>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Adresse</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $company->address ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Code postal</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $company->zip_code ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Ville</dt>
                            <dd class="mt-1 text-sm text-foreground">{{ $company->city ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>

            {{-- Contacts --}}
            <x-ui.card>
                <div class="space-y-5">
                    <h3 class="text-base font-semibold text-foreground">Contacts</h3>

                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-semibold text-foreground">Référents sûreté</h4>
                            <span class="text-xs text-foreground-muted">{{ $safetyReferents->count() }}</span>
                        </div>
                        @if ($safetyReferents->isEmpty())
                            <p class="text-sm text-foreground-muted">Aucun référent renseigné.</p>
                        @else
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                @foreach ($safetyReferents as $ref)
                                    <div wire:key="safety-{{ $ref->id }}" class="rounded-md border border-border bg-slate-50/50 p-3">
                                        <div class="text-sm font-medium text-foreground">{{ $ref->firstname }} {{ $ref->lastname }}</div>
                                        <div class="mt-1 text-xs text-foreground-muted">{{ $ref->email }}</div>
                                        <div class="text-xs text-foreground-muted">{{ $ref->phone }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="space-y-3 border-t border-border pt-4">
                        <h4 class="text-sm font-semibold text-foreground">Correspondant sécurité</h4>
                        @if (! $securityCorrespondent)
                            <p class="text-sm text-foreground-muted">Non renseigné.</p>
                        @else
                            <div class="rounded-md border border-border bg-slate-50/50 p-3">
                                <div class="text-sm font-medium text-foreground">{{ $securityCorrespondent->firstname }} {{ $securityCorrespondent->lastname }}</div>
                                <div class="mt-1 text-xs text-foreground-muted">{{ $securityCorrespondent->email }}</div>
                                <div class="text-xs text-foreground-muted">{{ $securityCorrespondent->phone }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-3 border-t border-border pt-4">
                        <h4 class="text-sm font-semibold text-foreground">Contact RH</h4>
                        @if (! $hrContact)
                            <p class="text-sm text-foreground-muted">Non renseigné.</p>
                        @else
                            <div class="rounded-md border border-border bg-slate-50/50 p-3">
                                <div class="text-sm font-medium text-foreground">{{ $hrContact->firstname }} {{ $hrContact->lastname }}</div>
                                <div class="mt-1 text-xs text-foreground-muted">{{ $hrContact->email }}</div>
                                <div class="text-xs text-foreground-muted">{{ $hrContact->phone }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            {{-- Documents --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h3 class="text-base font-semibold text-foreground">Documents</h3>
                    <ul class="space-y-2">
                        @foreach ($docs as $doc)
                            <li class="flex items-center justify-between gap-3 rounded-md border border-border px-3 py-2">
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-foreground">{{ $doc['label'] }}</div>
                                    <div class="text-xs text-foreground-muted">{{ $doc['path'] ? 'Document fourni' : 'Non fourni' }}</div>
                                </div>
                                @if ($doc['path'])
                                    <a
                                        href="{{ asset('storage/'.$doc['path']) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-foreground-muted transition hover:bg-slate-100 hover:text-foreground"
                                        aria-label="Télécharger {{ $doc['label'] }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
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
    </div>

    {{-- Section: Ressources --}}
    <div class="space-y-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-foreground-muted">Ressources</h2>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <livewire:companies.coworkers-list :company-id="$company->id" :key="'coworkers-list-'.$company->id" />
            <livewire:companies.activity-requests-list :company-id="$company->id" :key="'ar-list-'.$company->id" />
            <livewire:companies.badge-requests-list :company-id="$company->id" :key="'br-list-'.$company->id" />
            <livewire:companies.badges-list :company-id="$company->id" :key="'b-list-'.$company->id" />
            <livewire:companies.vehicle-passes-list :company-id="$company->id" :key="'vp-list-'.$company->id" />
            <livewire:companies.trainings-list :company-id="$company->id" :key="'t-list-'.$company->id" />
        </div>
    </div>
</div>
