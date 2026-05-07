<?php

use App\Actions\BadgeRequest\DeliverBadgeRequestAction;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\BadgeRequest;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use InteractsWithToasts;
    use WithFileUploads;

    public ?int $badgeRequestId = null;

    public $deliveryPhoto = null;

    #[On('deliver-badge-request')]
    public function open(int $id): void
    {
        if (! auth()->user()->canChangeRequestStatus()) {
            return;
        }

        $this->badgeRequestId = $id;
        $this->deliveryPhoto = null;
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'deliver-badge-request');
    }

    public function submit(DeliverBadgeRequestAction $action): void
    {
        if (! auth()->user()->canChangeRequestStatus() || $this->badgeRequestId === null) {
            return;
        }

        $this->validate([
            'deliveryPhoto' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ], [
            'deliveryPhoto.required' => 'La photo du badge remis est obligatoire.',
            'deliveryPhoto.image' => 'Le fichier doit être une image.',
            'deliveryPhoto.mimes' => 'La photo doit être au format JPEG, PNG ou JPG.',
            'deliveryPhoto.max' => 'La photo ne doit pas dépasser 5 Mo.',
        ]);

        $badgeRequest = BadgeRequest::find($this->badgeRequestId);
        if ($badgeRequest === null || ! auth()->user()->can('deliver', $badgeRequest)) {
            $this->toast('Action non autorisée.', 'danger');

            return;
        }

        $client = $badgeRequest->activityRequest->client;

        $result = $action->execute($badgeRequest, $this->deliveryPhoto, $client);

        if (! $result->isSuccessful()) {
            $this->toast($result->getMessage(), 'danger', 'Remise impossible');

            return;
        }

        $this->toast('Badge marqué comme remis.', 'success', 'Badge remis');

        $this->dispatch('close-modal', name: 'deliver-badge-request');
        $this->dispatch('badge-request-status-updated');

        $this->reset(['badgeRequestId', 'deliveryPhoto']);
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'deliver-badge-request');
        $this->reset(['badgeRequestId', 'deliveryPhoto']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-ui.modal name="deliver-badge-request" maxWidth="xl">
        <form wire:submit="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Marquer le badge comme remis</h2>
                <p class="text-sm text-foreground-muted">Téléversez la photo du badge remis au demandeur — elle sert de preuve de remise.</p>
            </div>

            <x-ui.file-upload
                wire:model="deliveryPhoto"
                label="Photo du badge remis"
                accept="image/jpeg,image/png,image/jpg"
                hint="JPG ou PNG — 5 Mo max"
                :new-files="$deliveryPhoto?->getClientOriginalName()"
                :error="$errors->first('deliveryPhoto')"
                required
            />

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="submit,deliveryPhoto">Confirmer la remise</span>
                    <span wire:loading wire:target="submit,deliveryPhoto">Envoi...</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
