<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $selected_client_id = null;

    public ?int $selected_coworker_id = null;

    public ?int $selected_training_id = null;

    public ?string $start_date = null;

    public ?string $validity_years = null;

    public ?string $selected_airport = null;

    #[On('open-assign-training')]
    public function open(?int $clientId = null): void
    {
        $authUser = auth()->user();
        if ($authUser->isClient() && ! $authUser->canAccessFormation()) {
            return;
        }

        $this->resetForm();

        if ($authUser->isTenantManager()) {
            $this->selected_client_id = $clientId;
        } else {
            $this->selected_client_id = $authUser->contextualClientId();
        }

        $this->dispatch('open-modal', name: 'assign-training');
    }

    #[Computed]
    public function clients()
    {
        return Client::orderBy('company_name')->get();
    }

    #[Computed]
    public function trainings()
    {
        return Training::orderBy('title')->get();
    }

    #[Computed]
    public function coworkers()
    {
        if (! $this->selected_client_id) {
            return collect();
        }

        return Coworker::where('client_id', $this->selected_client_id)
            ->where('has_leave', false)
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();
    }

    #[Computed]
    public function selectedTraining(): ?Training
    {
        if (! $this->selected_training_id) {
            return null;
        }

        return $this->trainings->firstWhere('id', (int) $this->selected_training_id);
    }

    #[Computed]
    public function requiresAirport(): bool
    {
        return (bool) ($this->selectedTraining?->requires_airport);
    }

    public function updatedSelectedClientId(): void
    {
        $this->selected_coworker_id = null;
        unset($this->coworkers);
    }

    public function updatedSelectedTrainingId(): void
    {
        unset($this->selectedTraining, $this->requiresAirport);
        if (! $this->requiresAirport) {
            $this->selected_airport = null;
        }
    }

    public function submit(): void
    {
        $authUser = auth()->user();

        if (! $authUser->isTenantManager()) {
            $this->selected_client_id = $authUser->contextualClientId();
        }

        $this->validate([
            'selected_client_id' => 'required|exists:clients,id',
            'selected_coworker_id' => 'required|exists:coworkers,id',
            'selected_training_id' => 'required|exists:central.trainings,id',
            'start_date' => 'required|date',
            'validity_years' => 'required|in:2,3,5,lifetime',
            'selected_airport' => 'nullable|in:ORY,CDG,LBG',
        ], [
            'selected_client_id.required' => 'Veuillez sélectionner une société.',
            'selected_coworker_id.required' => 'Veuillez sélectionner un collaborateur.',
            'selected_training_id.required' => 'Veuillez sélectionner une formation.',
            'start_date.required' => 'Veuillez saisir une date de début.',
            'validity_years.required' => 'Veuillez sélectionner une durée de validité.',
            'validity_years.in' => 'La durée de validité doit être 2, 3, 5 ans ou à vie.',
            'selected_airport.in' => "L'aéroport sélectionné est invalide.",
        ]);

        $airport = $this->requiresAirport ? $this->selected_airport : null;

        try {
            DB::beginTransaction();

            $duplicate = DB::table('coworker_trainings')
                ->where('coworker_id', $this->selected_coworker_id)
                ->where('training_id', $this->selected_training_id)
                ->when($airport !== null, fn ($q) => $q->where('airport', $airport))
                ->when($airport === null, fn ($q) => $q->whereNull('airport'))
                ->exists();

            if ($duplicate) {
                DB::rollBack();
                $this->toast(
                    $airport
                        ? 'Ce collaborateur a déjà cette formation attribuée pour cet aéroport.'
                        : 'Ce collaborateur a déjà cette formation attribuée.',
                    'danger'
                );

                return;
            }

            $startDate = Carbon::parse($this->start_date);
            $expiresAt = $this->validity_years === 'lifetime'
                ? null
                : $startDate->copy()->addYears((int) $this->validity_years);

            DB::table('coworker_trainings')->insert([
                'coworker_id' => $this->selected_coworker_id,
                'training_id' => $this->selected_training_id,
                'airport' => $airport,
                'started_at' => $startDate,
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $this->toast('Formation attribuée avec succès.', 'success', 'Attribution réussie');
            $this->dispatch('close-modal', name: 'assign-training');
            $this->dispatch('training-assigned');
            $this->resetForm();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de l'attribution de formation", [
                'error' => $e->getMessage(),
                'user_id' => $authUser->id,
            ]);
            $this->toast("Une erreur est survenue lors de l'attribution.", 'danger');
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'assign-training');
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'selected_coworker_id',
            'selected_training_id',
            'start_date',
            'validity_years',
            'selected_airport',
        ]);
        $this->resetErrorBag();
    }
}; ?>

@php
    $authUser = auth()->user();
    $isAdmin = $authUser->isTenantManager();

    $clientOptions = [];
    foreach ($this->clients as $c) {
        $clientOptions[] = [
            'value' => $c->id,
            'label' => $c->company_name,
            'hint' => $c->siret_number,
        ];
    }

    $coworkerOptions = [];
    foreach ($this->coworkers as $c) {
        $coworkerOptions[] = [
            'value' => $c->id,
            'label' => $c->firstname.' '.$c->lastname,
            'hint' => $c->email,
        ];
    }

    $trainingOptions = [];
    foreach ($this->trainings as $t) {
        $trainingOptions[] = [
            'value' => $t->id,
            'label' => $t->title,
            'hint' => $t->requires_airport ? 'Requiert un aéroport' : null,
        ];
    }

    $validityOptions = [
        ['value' => '2', 'label' => '2 ans'],
        ['value' => '3', 'label' => '3 ans'],
        ['value' => '5', 'label' => '5 ans'],
        ['value' => 'lifetime', 'label' => 'À vie'],
    ];

    $airportOptions = [
        ['value' => 'CDG', 'label' => 'CDG — Paris-Charles-de-Gaulle'],
        ['value' => 'ORY', 'label' => 'ORY — Paris-Orly'],
        ['value' => 'LBG', 'label' => 'LBG — Paris-Le Bourget'],
    ];
@endphp

<div>
    <x-ui.modal name="assign-training" maxWidth="lg">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Attribuer une formation</h2>
                <p class="text-sm text-foreground-muted">Sélectionnez le collaborateur, la formation et les dates de validité.</p>
            </div>

            @if ($isAdmin)
                <x-ui.select
                    label="Société"
                    :value="$selected_client_id"
                    wire:model.live="selected_client_id"
                    :options="$clientOptions"
                    placeholder="Sélectionnez une société…"
                    search-placeholder="Filtrer par société ou SIRET…"
                    empty-text="Aucune société trouvée."
                    searchable
                    required
                    :error="$errors->first('selected_client_id')"
                />
            @endif

            @if ($selected_client_id)
                @if (count($coworkerOptions) === 0)
                    <x-ui.alert variant="warning">
                        Aucun collaborateur actif pour cette société.
                    </x-ui.alert>
                @else
                    <x-ui.select
                        label="Collaborateur"
                        :value="$selected_coworker_id"
                        wire:model.live="selected_coworker_id"
                        :options="$coworkerOptions"
                        placeholder="Sélectionnez un collaborateur…"
                        search-placeholder="Filtrer par nom ou email…"
                        empty-text="Aucun collaborateur."
                        searchable
                        required
                        :error="$errors->first('selected_coworker_id')"
                    />
                @endif
            @endif

            @if ($selected_coworker_id)
                <x-ui.select
                    label="Formation"
                    :value="$selected_training_id"
                    wire:model.live="selected_training_id"
                    :options="$trainingOptions"
                    placeholder="Sélectionnez une formation…"
                    search-placeholder="Filtrer par titre…"
                    empty-text="Aucune formation."
                    searchable
                    required
                    :error="$errors->first('selected_training_id')"
                />

                @if ($this->requiresAirport)
                    <x-ui.select
                        label="Aéroport"
                        :value="$selected_airport"
                        wire:model.live="selected_airport"
                        :options="$airportOptions"
                        placeholder="Sélectionnez un aéroport…"
                        required
                        :error="$errors->first('selected_airport')"
                    />
                @endif

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-ui.input
                        label="Date de début"
                        type="date"
                        wire:model.blur="start_date"
                        required
                        :error="$errors->first('start_date')"
                    />
                    <x-ui.select
                        label="Durée de validité"
                        :value="$validity_years"
                        wire:model.live="validity_years"
                        :options="$validityOptions"
                        placeholder="Sélectionner…"
                        required
                        :error="$errors->first('validity_years')"
                    />
                </div>
            @endif

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="submit">Attribuer</span>
                    <span wire:loading wire:target="submit">Attribution…</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
