<?php

use App\Livewire\Concerns\InteractsWithToasts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $coworkerTrainingId = null;

    public ?string $airport = null;

    #[On('open-edit-airport')]
    public function open(int $coworkerTrainingId, ?string $currentAirport = null): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $this->coworkerTrainingId = $coworkerTrainingId;
        $this->airport = $currentAirport ?: null;
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'edit-airport');
    }

    #[Computed]
    public function context(): ?object
    {
        if (! $this->coworkerTrainingId) {
            return null;
        }

        return DB::table('coworker_trainings')
            ->join('coworkers', 'coworker_trainings.coworker_id', '=', 'coworkers.id')
            ->join('trainings', 'coworker_trainings.training_id', '=', 'trainings.id')
            ->where('coworker_trainings.id', $this->coworkerTrainingId)
            ->select(
                'coworker_trainings.coworker_id',
                'coworker_trainings.training_id',
                'coworkers.firstname',
                'coworkers.lastname',
                'trainings.title as training_title',
            )
            ->first();
    }

    public function submit(): void
    {
        if (! auth()->user()->isAdmin() || $this->coworkerTrainingId === null) {
            return;
        }

        $this->validate([
            'airport' => 'nullable|in:ORY,CDG,LBG',
        ], [
            'airport.in' => "L'aéroport sélectionné est invalide.",
        ]);

        $airport = $this->airport ?: null;

        try {
            $row = DB::table('coworker_trainings')->where('id', $this->coworkerTrainingId)->first();
            if (! $row) {
                $this->toast('Attribution introuvable.', 'danger');

                return;
            }

            $duplicate = DB::table('coworker_trainings')
                ->where('coworker_id', $row->coworker_id)
                ->where('training_id', $row->training_id)
                ->where('id', '!=', $this->coworkerTrainingId)
                ->when($airport !== null, fn ($q) => $q->where('airport', $airport))
                ->when($airport === null, fn ($q) => $q->whereNull('airport'))
                ->exists();

            if ($duplicate) {
                $this->toast(
                    $airport
                        ? 'Ce collaborateur a déjà cette formation pour cet aéroport.'
                        : 'Ce collaborateur a déjà cette formation sans aéroport.',
                    'danger'
                );

                return;
            }

            DB::table('coworker_trainings')
                ->where('id', $this->coworkerTrainingId)
                ->update([
                    'airport' => $airport,
                    'updated_at' => now(),
                ]);

            $this->toast('Aéroport mis à jour.', 'success', 'Mise à jour');
            $this->dispatch('close-modal', name: 'edit-airport');
            $this->dispatch('training-airport-updated');

            $this->reset(['coworkerTrainingId', 'airport']);
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour aéroport', [
                'error' => $e->getMessage(),
                'coworker_training_id' => $this->coworkerTrainingId,
            ]);
            $this->toast("Erreur lors de la mise à jour.", 'danger');
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'edit-airport');
        $this->reset(['coworkerTrainingId', 'airport']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-ui.modal name="edit-airport" maxWidth="lg">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Modifier l'aéroport</h2>
                <p class="text-sm text-foreground-muted">Laissez vide pour retirer l'aéroport.</p>
            </div>

            @if ($this->context)
                @php $ctx = $this->context; @endphp
                <div class="grid grid-cols-1 gap-3 rounded-md border border-border bg-slate-50 p-4 text-sm sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Collaborateur</div>
                        <div class="mt-0.5 font-medium text-foreground">{{ $ctx->firstname }} {{ $ctx->lastname }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Formation</div>
                        <div class="mt-0.5 text-foreground">{{ $ctx->training_title }}</div>
                    </div>
                </div>

                <x-ui.select
                    label="Aéroport"
                    :value="$airport"
                    wire:model.live="airport"
                    :options="[
                        ['value' => null, 'label' => 'Aucun'],
                        ['value' => 'CDG', 'label' => 'CDG — Paris-Charles-de-Gaulle'],
                        ['value' => 'ORY', 'label' => 'ORY — Paris-Orly'],
                        ['value' => 'LBG', 'label' => 'LBG — Paris-Le Bourget'],
                    ]"
                    placeholder="Sélectionnez un aéroport…"
                    :error="$errors->first('airport')"
                />
            @endif

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="submit">Enregistrer</span>
                    <span wire:loading wire:target="submit">Enregistrement…</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
