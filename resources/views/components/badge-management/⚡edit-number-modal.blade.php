<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Badge;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $badgeId = null;

    public ?string $badge_number = null;

    public ?string $airport = null;

    protected array $rules = [
        'badge_number' => 'nullable|string|max:255',
        'airport' => 'required|in:ORY,CDG,LBG',
    ];

    protected array $messages = [
        'badge_number.max' => 'Le numéro de badge ne peut pas dépasser 255 caractères.',
        'airport.required' => 'Veuillez sélectionner un aéroport.',
        'airport.in' => 'L\'aéroport sélectionné n\'est pas valide.',
    ];

    #[On('edit-badge-number')]
    public function open(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $badge = Badge::find($id);
        if ($badge === null) {
            return;
        }

        $this->badgeId = $badge->id;
        $this->badge_number = $badge->badge_number;
        $this->airport = $badge->airport;
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'edit-badge-number');
    }

    public function submit(): void
    {
        if (! auth()->user()->isAdmin() || $this->badgeId === null) {
            return;
        }

        $this->validate();

        $badge = Badge::find($this->badgeId);
        if ($badge === null) {
            $this->toast('Badge introuvable.', 'danger');

            return;
        }

        try {
            $badge->update([
                'badge_number' => $this->badge_number ?: null,
                'airport' => $this->airport,
            ]);

            $this->toast('Numéro et aéroport mis à jour.', 'success', 'Badge modifié');

            $this->dispatch('close-modal', name: 'edit-badge-number');
            $this->dispatch('badge-updated');

            $this->reset(['badgeId', 'badge_number', 'airport']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la modification du badge', [
                'error' => $e->getMessage(),
                'badge_id' => $badge->id,
            ]);

            $this->toast('Une erreur est survenue lors de la modification.', 'danger');
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'edit-badge-number');
        $this->reset(['badgeId', 'badge_number', 'airport']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-ui.modal name="edit-badge-number" maxWidth="lg">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Modifier le badge</h2>
                <p class="text-sm text-foreground-muted">Numéro de badge et aéroport rattaché.</p>
            </div>

            <x-ui.input
                label="Numéro de badge"
                wire:model="badge_number"
                placeholder="Ex : 123456"
                :error="$errors->first('badge_number')"
            />

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

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="submit">Enregistrer</span>
                    <span wire:loading wire:target="submit">Enregistrement...</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
