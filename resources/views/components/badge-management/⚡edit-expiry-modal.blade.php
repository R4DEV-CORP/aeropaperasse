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

    public ?string $expiry_date = null;

    protected array $rules = [
        'expiry_date' => 'required|date|after:today',
    ];

    protected array $messages = [
        'expiry_date.required' => "La date d'expiration est obligatoire.",
        'expiry_date.date' => "La date d'expiration doit être une date valide.",
        'expiry_date.after' => "La date d'expiration doit être postérieure à aujourd'hui.",
    ];

    #[On('edit-badge-expiry')]
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
        $this->expiry_date = $badge->expiry_date?->format('Y-m-d');
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'edit-badge-expiry');
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
            $update = ['expiry_date' => $this->expiry_date];

            // Si le badge était expiré et qu'on prolonge la date, on le réactive.
            if ($badge->status === 'expired') {
                $update['previous_status'] = $badge->status;
                $update['status'] = 'active';
            }

            $badge->update($update);

            $this->toast("Date d'expiration mise à jour.", 'success', 'Badge modifié');

            $this->dispatch('close-modal', name: 'edit-badge-expiry');
            $this->dispatch('badge-updated');

            $this->reset(['badgeId', 'expiry_date']);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la modification de la date d'expiration", [
                'error' => $e->getMessage(),
                'badge_id' => $badge->id,
            ]);

            $this->toast("Une erreur est survenue lors de la modification.", 'danger');
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'edit-badge-expiry');
        $this->reset(['badgeId', 'expiry_date']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-ui.modal name="edit-badge-expiry" maxWidth="lg">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Modifier la date d'expiration</h2>
                <p class="text-sm text-foreground-muted">Si le badge est expiré, prolonger la date le réactivera automatiquement.</p>
            </div>

            <x-ui.input
                label="Date d'expiration"
                type="date"
                wire:model="expiry_date"
                required
                :error="$errors->first('expiry_date')"
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
