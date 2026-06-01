<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Coworker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $coworkerId = null;

    public ?string $departure_date = null;

    #[On('open-has-leave')]
    public function open(int $id): void
    {
        $coworker = Coworker::find($id);
        if (! $coworker) {
            return;
        }

        if (! $this->canMarkLeave($coworker)) {
            return;
        }

        if ($coworker->has_leave) {
            $this->toast('Ce collaborateur a déjà quitté l\'entreprise.', 'warning');

            return;
        }

        $this->coworkerId = $id;
        $this->departure_date = Carbon::today()->format('Y-m-d');
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'coworker-has-leave');
    }

    #[Computed]
    public function coworker(): ?Coworker
    {
        if ($this->coworkerId === null) {
            return null;
        }

        return Coworker::with('client')->find($this->coworkerId);
    }

    public function submit(): void
    {
        if ($this->coworkerId === null) {
            return;
        }

        $coworker = Coworker::find($this->coworkerId);
        if (! $coworker || ! $this->canMarkLeave($coworker)) {
            $this->toast('Action non autorisée.', 'danger');

            return;
        }

        $this->validate([
            'departure_date' => 'required|date|before_or_equal:today',
        ], [
            'departure_date.required' => 'La date de départ est requise.',
            'departure_date.date' => 'La date de départ doit être une date valide.',
            'departure_date.before_or_equal' => 'La date de départ ne peut pas être dans le futur.',
        ]);

        try {
            $coworker->update([
                'has_leave' => true,
                'departure_date' => $this->departure_date,
            ]);

            $this->toast(
                $coworker->firstname.' '.$coworker->lastname.' marqué(e) comme parti(e) le '.Carbon::parse($this->departure_date)->format('d/m/Y').'.',
                'success',
                'Départ enregistré'
            );

            $this->dispatch('close-modal', name: 'coworker-has-leave');
            $this->dispatch('coworker-updated');

            $this->reset(['coworkerId', 'departure_date']);
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage du départ', [
                'error' => $e->getMessage(),
                'coworker_id' => $coworker->id,
                'user_id' => auth()->user()->id,
            ]);

            $this->toast('Une erreur est survenue lors de l\'enregistrement du départ.', 'danger');
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'coworker-has-leave');
        $this->reset(['coworkerId', 'departure_date']);
        $this->resetErrorBag();
    }

    private function canMarkLeave(Coworker $coworker): bool
    {
        $user = auth()->user();

        if ($user->isTenantManager()) {
            return true;
        }

        return $coworker->client_id === $user->contextualClientId();
    }
}; ?>

<div>
    <x-ui.modal name="coworker-has-leave" maxWidth="lg">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Marquer comme parti</h2>
                <p class="text-sm text-foreground-muted">Indiquez la date de départ du collaborateur. Cette action peut être nécessaire pour clôturer son cycle de vie.</p>
            </div>

            @if ($this->coworker)
                @php $cw = $this->coworker; @endphp
                <div class="grid grid-cols-1 gap-3 rounded-md border border-border bg-slate-50 p-4 text-sm sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Collaborateur</div>
                        <div class="mt-0.5 font-medium text-foreground">{{ $cw->firstname }} {{ $cw->lastname }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Société</div>
                        <div class="mt-0.5 text-foreground">{{ $cw->client?->company_name ?? '—' }}</div>
                    </div>
                </div>
            @endif

            <x-ui.input
                label="Date de départ"
                type="date"
                wire:model.blur="departure_date"
                required
                hint="La date ne peut pas être dans le futur."
                :error="$errors->first('departure_date')"
            />

            <x-ui.alert variant="warning">
                Cette action marque le collaborateur comme ayant quitté l'entreprise.
            </x-ui.alert>

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="submit">Confirmer le départ</span>
                    <span wire:loading wire:target="submit">Enregistrement…</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
