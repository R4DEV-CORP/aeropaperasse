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
        return Client::with([
            'contacts',
            'coworkers.user',
            'users',
            'badges',
            'activityRequests',
        ])->findOrFail($this->companyId);
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
        unset($this->company);
        $this->toast('Société mise à jour.', 'success', 'Mise à jour réussie');
    }

    #[On('company-deleted')]
    public function onDeleted(): void
    {
        $this->redirect(route('companies.index'), navigate: true);
    }
}; ?>

@php
    $authUser = auth()->user();
    $isAdmin = $authUser->isAdmin();
    $isClient = $authUser->isClient();
    $canEdit = ! $isClient;
    $canDelete = $isAdmin;

    $company = $this->company;
    $contacts = $company->contacts;
    $safetyReferents = $contacts->where('role', 'safety')->values();
    $securityCorrespondent = $contacts->where('role', 'security')->first();
    $hrContact = $contacts->where('role', 'hr')->first();

    $roleMeta = [
        'admin' => ['label' => 'Admin', 'variant' => 'rejected'],
        'sadmin' => ['label' => 'Sadmin', 'variant' => 'rejected'],
        'sclient' => ['label' => 'SClient', 'variant' => 'in-progress'],
        'aclient' => ['label' => 'AClient', 'variant' => 'in-progress'],
        'client' => ['label' => 'Client', 'variant' => 'ready'],
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

            @if ($canDelete)
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

                    <x-ui.dropdown-item variant="danger" wire:click="$dispatch('open-delete-company', { id: {{ $company->id }} })">
                        Supprimer la société
                    </x-ui.dropdown-item>
                </x-ui.dropdown>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Colonne gauche --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Informations société --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Informations société</h2>
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
                    <h2 class="text-base font-semibold text-foreground">Adresse</h2>
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
                    <h2 class="text-base font-semibold text-foreground">Contacts</h2>

                    {{-- Référents sûreté --}}
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold text-foreground">Référents sûreté</h3>
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

                    {{-- Correspondant sécurité --}}
                    <div class="space-y-3 border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-foreground">Correspondant sécurité</h3>
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

                    {{-- Contact RH --}}
                    <div class="space-y-3 border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-foreground">Contact RH</h3>
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

            {{-- Utilisateurs & Collaborateurs --}}
            <x-ui.card padding="none">
                <div class="border-b border-border px-5 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-foreground">Utilisateurs & Collaborateurs</h2>
                        <span class="text-xs text-foreground-muted">{{ $company->coworkers->count() }}</span>
                    </div>
                </div>

                @if ($company->coworkers->isEmpty())
                    <div class="px-5 py-12 text-center text-sm text-foreground-muted">
                        Aucun utilisateur ou collaborateur.
                    </div>
                @else
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th>Nom & prénom</th>
                                <th>Rôle</th>
                                <th>Contact</th>
                                <th>Statut</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($company->coworkers as $coworker)
                                <tr wire:key="coworker-{{ $coworker->id }}">
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-foreground">{{ $coworker->firstname }} {{ $coworker->lastname }}</span>
                                            @if ($coworker->user_id && $coworker->user && $coworker->user->can_access_formation)
                                                <span title="Accès formations" class="text-blue-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                                        <path d="M9.664 1.319a.75.75 0 0 1 .672 0 41.059 41.059 0 0 1 8.198 5.424.75.75 0 0 1-.254 1.285 31.372 31.372 0 0 0-7.86 3.83.75.75 0 0 1-.84 0 31.508 31.508 0 0 0-2.08-1.287V9.394c0-.244.116-.463.302-.592a35.504 35.504 0 0 1 3.305-2.033.75.75 0 0 0-.714-1.319 37 37 0 0 0-3.446 2.12A2.216 2.216 0 0 0 6 9.393v.38a31.293 31.293 0 0 0-4.28-1.746.75.75 0 0 1-.254-1.285 41.059 41.059 0 0 1 8.198-5.424ZM6 11.459a29.848 29.848 0 0 0-2.455-1.158 41.029 41.029 0 0 0-.39 3.114.75.75 0 0 0 .419.74c.528.256 1.046.53 1.554.82-.21.324-.455.63-.74.91a.75.75 0 0 0 1.06 1.061c.4-.4.733-.836 1-1.298.86.514 1.687 1.07 2.475 1.661a.75.75 0 0 0 .91 0 30.31 30.31 0 0 1 4.913-3.034.75.75 0 0 0 .42-.74 41.013 41.013 0 0 0-.391-3.114 29.84 29.84 0 0 0-5.59 2.875.75.75 0 0 1-.84 0c-.5-.34-1.013-.66-1.535-.957a.748.748 0 0 0 .118-.418v-.418Z" />
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if ($coworker->user_id && $coworker->user)
                                            @php $r = $roleMeta[$coworker->user->role] ?? ['label' => $coworker->user->role, 'variant' => 'default']; @endphp
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
                                            <x-ui.badge variant="rejected">Parti le {{ $coworker->departure_date->format('d/m/Y') }}</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="approved">Actif</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a
                                            href="{{ route('coworkers.show', ['coworkerId' => $coworker->id]) }}"
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
            {{-- Documents --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Documents</h2>
                    @php
                        $docs = [
                            ['label' => 'KBIS', 'path' => $company->kbis_document],
                            ['label' => 'Référents sûreté', 'path' => $company->safety_document],
                            ['label' => 'Correspondant sécurité', 'path' => $company->security_document],
                        ];
                    @endphp
                    <ul class="space-y-2">
                        @foreach ($docs as $doc)
                            <li class="flex items-center justify-between gap-3 rounded-md border border-border px-3 py-2">
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-foreground">{{ $doc['label'] }}</div>
                                    @if ($doc['path'])
                                        <div class="text-xs text-foreground-muted">Document fourni</div>
                                    @else
                                        <div class="text-xs text-foreground-muted">Non fourni</div>
                                    @endif
                                </div>
                                @if ($doc['path'])
                                    <a
                                        href="{{ asset('storage/'.$doc['path']) }}"
                                        target="_blank"
                                        rel="noopener"
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

            {{-- Statistiques --}}
            <x-ui.card>
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-foreground">Statistiques</h2>
                    <dl class="space-y-3">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-foreground-muted">Collaborateurs</dt>
                            <dd class="text-sm font-semibold text-foreground">{{ $company->coworkers->count() }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-foreground-muted">Utilisateurs</dt>
                            <dd class="text-sm font-semibold text-foreground">{{ $company->users->count() }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-foreground-muted">Badges actifs</dt>
                            <dd class="text-sm font-semibold text-foreground">{{ $company->getActiveBadgeCount() }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-foreground-muted">Formations actives</dt>
                            <dd class="text-sm font-semibold text-foreground">{{ $company->getActiveTrainingCount() }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-foreground-muted">Demandes d'activité</dt>
                            <dd class="text-sm font-semibold text-foreground">{{ $company->activityRequests->count() }}</dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-border pt-3">
                            <dt class="text-sm text-foreground-muted">Créée le</dt>
                            <dd class="text-sm text-foreground">{{ $company->created_at->format('d/m/Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>
        </div>
    </div>

    <livewire:companies.delete-modal />
</div>
