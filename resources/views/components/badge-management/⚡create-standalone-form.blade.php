<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Badge;
use App\Models\Client;
use App\Models\Coworker;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?Client $client = null;

    public ?int $selected_client_id = null;

    public ?int $selected_coworker_id = null;

    public ?string $airport = null;

    public ?string $expiry_date = null;

    public ?string $badge_number = null;

    protected array $rules = [
        'selected_client_id' => 'required|exists:clients,id',
        'selected_coworker_id' => 'required|exists:coworkers,id',
        'airport' => 'required|in:ORY,CDG,LBG',
        'expiry_date' => 'required|date|after:today',
        'badge_number' => 'nullable|string|max:255',
    ];

    protected array $messages = [
        'selected_client_id.required' => 'Veuillez sélectionner un client.',
        'selected_client_id.exists' => 'Le client sélectionné n\'existe pas.',
        'selected_coworker_id.required' => 'Veuillez sélectionner un collaborateur.',
        'selected_coworker_id.exists' => 'Le collaborateur sélectionné n\'existe pas.',
        'airport.required' => 'Veuillez sélectionner un aéroport.',
        'airport.in' => 'L\'aéroport sélectionné n\'est pas valide.',
        'expiry_date.required' => "La date d'expiration est requise.",
        'expiry_date.date' => "La date d'expiration doit être une date valide.",
        'expiry_date.after' => "La date d'expiration doit être postérieure à aujourd'hui.",
        'badge_number.max' => 'Le numéro de badge ne peut pas dépasser 255 caractères.',
    ];

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->isClient()) {
            abort(403);
        }

        if (! $user->isTenantManager()) {
            $this->client = $user->client;
            $this->selected_client_id = $user->contextualClientId();
        }
    }

    public function updatedSelectedClientId($value): void
    {
        $this->selected_coworker_id = null;
        $this->client = $value ? Client::find((int) $value) : null;
    }

    #[Computed]
    public function clients()
    {
        return Client::orderBy('company_name')->get();
    }

    #[Computed]
    public function coworkers()
    {
        if (! $this->selected_client_id) {
            return collect();
        }

        return Coworker::where('client_id', $this->selected_client_id)
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->get();
    }

    public function abandon(): void
    {
        $this->redirectRoute('badge-management.index', navigate: true);
    }

    public function createBadge(): void
    {
        $user = auth()->user();

        if ($user->isClient()) {
            abort(403);
        }

        // sclient ne peut créer un badge que pour son propre client ; les tenant managers (REM staff,
        // owner, tenant_admin) gardent leur sélection libre.
        if (! $user->isTenantManager()) {
            $this->selected_client_id = $user->contextualClientId();
        }

        $this->validate();

        try {
            Badge::create([
                'client_id' => $this->selected_client_id,
                'coworker_id' => $this->selected_coworker_id,
                'badge_request_id' => null,
                'airport' => $this->airport,
                'status' => 'active',
                'expiry_date' => $this->expiry_date,
                'badge_number' => $this->badge_number ?: null,
            ]);

            $this->toast('Badge créé avec succès.', 'success', 'Badge créé');
            $this->redirectRoute('badge-management.index', navigate: true);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du badge standalone', [
                'error' => $e->getMessage(),
                'user_id' => auth()->user()->id,
                'client_id' => $this->selected_client_id,
                'coworker_id' => $this->selected_coworker_id,
            ]);

            $this->toast('Une erreur est survenue lors de la création du badge.', 'danger');
        }
    }
}; ?>

@php
    $isAdmin = auth()->user()->isTenantManager();
    $isTenantManager = auth()->user()->isTenantManager();

    $clientOptions = $isTenantManager
        ? collect($this->clients)
            ->map(fn ($c) => [
                'value' => $c->id,
                'label' => $c->company_name,
                'hint' => 'SIRET '.$c->siret_number,
            ])
            ->all()
        : [];

    $coworkerOptions = collect($this->coworkers)
        ->map(fn ($c) => [
            'value' => $c->id,
            'label' => trim(($c->lastname ?? '').' '.($c->firstname ?? '')),
            'hint' => $c->email,
        ])
        ->all();
@endphp

<form wire:submit.prevent="createBadge" class="flex flex-1 flex-col">
    <div class="flex-1 px-4 pb-8 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-3xl space-y-4">
            <x-ui.alert variant="info">
                Le badge créé indépendamment ne sera pas rattaché à une demande de badge existante.
            </x-ui.alert>

            {{-- Section Client : picker pour tenant managers (admin REM/owner/tenant_admin), info read-only pour sclient --}}
            @if ($isTenantManager)
                <div x-data="{ open: true }" class="overflow-hidden rounded-lg bg-amber-50/50 ring-1 ring-amber-200/70 ring-inset">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-amber-100/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-md bg-amber-100 text-amber-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.249-8.25-3.285Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Sélection du client</div>
                            <div class="text-xs text-foreground-muted">Administration</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-amber-700/80 transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-amber-200/70 p-5">
                        <x-ui.select
                            id="select-client"
                            label="Client"
                            :value="$selected_client_id"
                            wire:model.live="selected_client_id"
                            :options="$clientOptions"
                            placeholder="Sélectionnez un client…"
                            search-placeholder="Filtrer par société ou SIRET…"
                            empty-text="Aucun client trouvé."
                            searchable
                            required
                            :error="$errors->first('selected_client_id')"
                        />
                        <p class="mt-2 text-xs text-amber-700/80 italic">
                            Le badge sera créé au nom du client sélectionné.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Section Collaborateur --}}
            <div class="overflow-hidden rounded-lg border border-border bg-white">
                <div class="border-b border-border px-5 py-3">
                    <h2 class="text-sm font-semibold text-foreground">Collaborateur</h2>
                    <p class="mt-0.5 text-xs text-foreground-muted">Détenteur du badge.</p>
                </div>
                <div class="p-5">
                    @if (! $selected_client_id)
                        <x-ui.alert variant="info">
                            Sélectionnez d'abord un client.
                        </x-ui.alert>
                    @elseif (count($coworkerOptions) === 0)
                        <x-ui.alert variant="warning">
                            Aucun collaborateur trouvé pour ce client.
                        </x-ui.alert>
                    @else
                        <x-ui.select
                            id="select-coworker"
                            label="Collaborateur"
                            :value="$selected_coworker_id"
                            wire:model.live="selected_coworker_id"
                            :options="$coworkerOptions"
                            placeholder="Sélectionnez un collaborateur…"
                            search-placeholder="Filtrer par nom ou email…"
                            empty-text="Aucun collaborateur trouvé."
                            searchable
                            required
                            :error="$errors->first('selected_coworker_id')"
                        />
                    @endif
                </div>
            </div>

            {{-- Section Badge --}}
            <div class="overflow-hidden rounded-lg border border-border bg-white">
                <div class="border-b border-border px-5 py-3">
                    <h2 class="text-sm font-semibold text-foreground">Badge</h2>
                </div>
                <div class="space-y-5 p-5">
                    <x-ui.radio-cards
                        label="Aéroport"
                        name="airport"
                        wire:model.change="airport"
                        :value="$airport"
                        :error="$errors->first('airport')"
                        :options="[
                            ['value' => 'CDG', 'label' => 'CDG', 'description' => 'Roissy Charles de Gaulle'],
                            ['value' => 'ORY', 'label' => 'ORY', 'description' => 'Paris Orly'],
                            ['value' => 'LBG', 'label' => 'LBG', 'description' => 'Le Bourget'],
                        ]"
                        columns="3"
                        required
                    />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-ui.input
                            label="Numéro de badge"
                            wire:model.blur="badge_number"
                            placeholder="Ex : 123456"
                            :error="$errors->first('badge_number')"
                        />
                        <x-ui.input
                            label="Date d'expiration"
                            type="date"
                            wire:model.blur="expiry_date"
                            required
                            :error="$errors->first('expiry_date')"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Barre d'actions sticky --}}
    <div class="sticky bottom-0 z-10 border-t border-border bg-white shadow-[0_-4px_12px_-4px_rgba(15,23,42,0.08)]">
        <div class="mx-auto flex w-full max-w-3xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
            <x-ui.button
                type="button"
                variant="ghost"
                wire:click="abandon"
                wire:confirm="Abandonner la création de ce badge ?"
            >
                Annuler
            </x-ui.button>

            <x-ui.button
                type="submit"
                variant="primary"
                wire:loading.attr="disabled"
                wire:target="createBadge"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span wire:loading.remove wire:target="createBadge">Créer le badge</span>
                <span wire:loading wire:target="createBadge">Création...</span>
            </x-ui.button>
        </div>
    </div>
</form>
