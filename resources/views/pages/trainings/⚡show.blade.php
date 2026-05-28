<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Formations', 'href' => '/trainings'],
        ['label' => 'Détail'],
    ],
])]
#[Title('Formations — Société')]
class extends Component
{
    use InteractsWithToasts;

    public int $companyId;

    public function mount(int $companyId): void
    {
        $user = auth()->user();

        if ($user->isClient() && ! $user->can_access_formation) {
            $this->redirect(route('companies.show', ['companyId' => $user->client_id]), navigate: true);

            return;
        }

        if (! $user->isAdmin() && $user->client_id !== $companyId) {
            abort(403);
        }

        $client = Client::find($companyId);
        if (! $client) {
            abort(404);
        }

        $this->companyId = $client->id;
    }

    #[Computed]
    public function client(): Client
    {
        return Client::with('coworkers')->findOrFail($this->companyId);
    }

    private function trainingsQuery()
    {
        $coworkerIds = $this->client->coworkers->pluck('id');

        return DB::table('coworker_trainings')
            ->join('coworkers', 'coworker_trainings.coworker_id', '=', 'coworkers.id')
            ->join('trainings', 'coworker_trainings.training_id', '=', 'trainings.id')
            ->select(
                'coworker_trainings.id',
                'coworker_trainings.coworker_id',
                'coworker_trainings.training_id',
                'coworker_trainings.airport',
                'coworker_trainings.started_at',
                'coworker_trainings.expires_at',
                'coworker_trainings.certificate_path',
                'coworkers.firstname as coworker_firstname',
                'coworkers.lastname as coworker_lastname',
                'trainings.title as training_title',
                'trainings.requires_airport as training_requires_airport',
            )
            ->whereIn('coworker_trainings.coworker_id', $coworkerIds);
    }

    #[Computed]
    public function activeTrainings()
    {
        return $this->trainingsQuery()
            ->where(function ($q) {
                $q->whereNull('coworker_trainings.expires_at')
                    ->orWhere('coworker_trainings.expires_at', '>', now()->addMonths(6));
            })
            ->orderBy('coworkers.lastname')
            ->orderBy('coworker_trainings.expires_at')
            ->get();
    }

    #[Computed]
    public function soonExpiringTrainings()
    {
        return $this->trainingsQuery()
            ->whereNotNull('coworker_trainings.expires_at')
            ->whereBetween('coworker_trainings.expires_at', [now(), now()->addMonths(6)])
            ->orderBy('coworker_trainings.expires_at')
            ->get();
    }

    #[Computed]
    public function expiredTrainings()
    {
        return $this->trainingsQuery()
            ->whereNotNull('coworker_trainings.expires_at')
            ->where('coworker_trainings.expires_at', '<', now())
            ->orderBy('coworker_trainings.expires_at', 'desc')
            ->get();
    }

    #[On('training-assigned')]
    #[On('training-certificate-uploaded')]
    #[On('training-airport-updated')]
    public function refresh(): void
    {
        unset($this->client, $this->activeTrainings, $this->soonExpiringTrainings, $this->expiredTrainings);
    }
}; ?>

@php
    $authUser = auth()->user();
    $isAdmin = $authUser->isAdmin();
    $client = $this->client;

    $airportMeta = [
        'CDG' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'ORY' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'LBG' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    ];

    $sections = [
        [
            'title' => 'Formations actives',
            'items' => $this->activeTrainings,
            'variant' => 'approved',
            'empty' => 'Aucune formation active.',
            'badge' => 'Active',
        ],
        [
            'title' => 'Formations qui expirent (< 6 mois)',
            'items' => $this->soonExpiringTrainings,
            'variant' => 'pending',
            'empty' => 'Aucune formation n\'expire dans les 6 prochains mois.',
            'badge' => 'Expire bientôt',
        ],
        [
            'title' => 'Formations expirées',
            'items' => $this->expiredTrainings,
            'variant' => 'rejected',
            'empty' => 'Aucune formation expirée.',
            'badge' => 'Expirée',
        ],
    ];
@endphp

<div class="space-y-6 p-8">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            @if ($isAdmin)
                <a
                    href="{{ route('trainings.index') }}"
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
            <h1 class="text-xl font-bold leading-tight text-foreground">{{ $client->company_name }}</h1>
            <span class="text-xs text-foreground-muted">{{ $client->coworkers->count() }} collaborateur(s)</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-ui.button :href="route('companies.show', ['companyId' => $client->id])" wire:navigate variant="secondary" size="sm">
                Voir la société
            </x-ui.button>
            <x-ui.button wire:click="$dispatch('open-assign-training', { clientId: {{ $client->id }} })" variant="primary" size="sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Attribuer une formation
            </x-ui.button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-ui.stat-card
            variant="approved"
            label="Actives"
            :value="$this->activeTrainings->count()"
        />
        <x-ui.stat-card
            variant="pending"
            label="Expirent < 6 mois"
            :value="$this->soonExpiringTrainings->count()"
        />
        <x-ui.stat-card
            variant="rejected"
            label="Expirées"
            :value="$this->expiredTrainings->count()"
        />
    </div>

    {{-- Sections --}}
    @foreach ($sections as $section)
        @php $count = $section['items']->count(); @endphp
        <x-ui.card padding="none">
            <details class="group" @if ($count <= 5) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-4 group-open:border-b group-open:border-border [&::-webkit-details-marker]:hidden">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 text-foreground-muted transition-transform duration-150 group-open:rotate-90">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                        <h2 class="text-base font-semibold text-foreground">{{ $section['title'] }}</h2>
                    </div>
                    <span class="text-xs text-foreground-muted">{{ $count }}</span>
                </summary>

                @if ($section['items']->isEmpty())
                <div class="px-5 py-12 text-center text-sm text-foreground-muted">
                    {{ $section['empty'] }}
                </div>
            @else
                <x-ui.table>
                    <thead>
                        <tr>
                            <th>Collaborateur</th>
                            <th>Formation</th>
                            <th>Aéroport</th>
                            <th>Début</th>
                            <th>Expiration</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($section['items'] as $row)
                            @php
                                $startedAt = $row->started_at ? \Carbon\Carbon::parse($row->started_at) : null;
                                $expiresAt = $row->expires_at ? \Carbon\Carbon::parse($row->expires_at) : null;
                                $hasCert = ! empty($row->certificate_path);
                            @endphp
                            <tr wire:key="ct-{{ $row->id }}">
                                <td>
                                    <a href="{{ route('coworkers.show', ['coworkerId' => $row->coworker_id]) }}" wire:navigate class="font-medium text-foreground transition hover:text-accent">
                                        {{ $row->coworker_firstname }} {{ $row->coworker_lastname }}
                                    </a>
                                </td>
                                <td class="text-foreground">{{ $row->training_title }}</td>
                                <td>
                                    @if ($row->airport)
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$row->airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                            {{ $row->airport }}
                                        </span>
                                    @else
                                        <span class="text-foreground-subtle">—</span>
                                    @endif
                                </td>
                                <td class="text-foreground-muted">{{ $startedAt?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    @if ($expiresAt)
                                        <span class="text-foreground-muted">{{ $expiresAt->format('d/m/Y') }}</span>
                                    @else
                                        <x-ui.badge variant="in-progress">À vie</x-ui.badge>
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

                                        <x-ui.dropdown-item wire:click="$dispatch('open-upload-certificate', { coworkerTrainingId: {{ $row->id }} })">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-7.5-9V18m0-10.5L8.25 9.75m3.75-2.25L15.75 9.75M3 9V6.75A2.25 2.25 0 0 1 5.25 4.5h13.5A2.25 2.25 0 0 1 21 6.75V9" />
                                            </svg>
                                            {{ $hasCert ? 'Remplacer le certificat' : 'Uploader le certificat' }}
                                        </x-ui.dropdown-item>

                                        @if ($hasCert)
                                            <x-ui.dropdown-item :href="tenant_asset($row->certificate_path)" target="_blank">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                </svg>
                                                Télécharger le certificat
                                            </x-ui.dropdown-item>
                                        @endif

                                        @if ($row->training_requires_airport && $isAdmin)
                                            <x-ui.dropdown-item wire:click="$dispatch('open-edit-airport', { coworkerTrainingId: {{ $row->id }}, currentAirport: '{{ $row->airport ?? '' }}' })">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-foreground-subtle">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                </svg>
                                                Modifier l'aéroport
                                            </x-ui.dropdown-item>
                                        @endif
                                    </x-ui.dropdown>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
            </details>
        </x-ui.card>
    @endforeach

    {{-- Modales partagées --}}
    <livewire:trainings.assign-modal />
    <livewire:trainings.upload-certificate-modal />
    <livewire:trainings.edit-airport-modal />
</div>
