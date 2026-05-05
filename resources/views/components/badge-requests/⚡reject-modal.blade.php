<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\BadgeRequest;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $badgeRequestId = null;

    public ?string $targetStatus = null;

    public string $rejectReason = '';

    #[On('reject-badge-request')]
    public function open(int $id, string $targetStatus): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        if (! in_array($targetStatus, ['rejected_rem', 'rejected_adp'], true)) {
            return;
        }

        $this->badgeRequestId = $id;
        $this->targetStatus = $targetStatus;
        $this->rejectReason = '';
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'reject-badge-request');
    }

    public function submit(): void
    {
        if (! auth()->user()->isAdmin()
            || $this->badgeRequestId === null
            || ! in_array($this->targetStatus, ['rejected_rem', 'rejected_adp'], true)) {
            return;
        }

        $this->validate([
            'rejectReason' => ['required', 'string', 'min:3'],
        ], [
            'rejectReason.required' => 'Le motif du rejet est obligatoire.',
            'rejectReason.min' => 'Le motif doit contenir au moins 3 caractères.',
        ]);

        $badgeRequest = BadgeRequest::findOrFail($this->badgeRequestId);
        $timestampField = $this->targetStatus.'_at';

        $badgeRequest->update([
            'status' => $this->targetStatus,
            $timestampField => now(),
            'reject_reason' => $this->rejectReason,
        ]);

        $message = $this->targetStatus === 'rejected_rem'
            ? 'Demande marquée comme dossier incomplet.'
            : 'Demande rejetée par ADP.';

        $this->toast($message, 'success', 'Demande rejetée');

        $this->dispatch('close-modal', name: 'reject-badge-request');
        $this->dispatch('badge-request-status-updated');

        $this->reset(['badgeRequestId', 'targetStatus', 'rejectReason']);
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'reject-badge-request');
        $this->reset(['badgeRequestId', 'targetStatus', 'rejectReason']);
        $this->resetErrorBag();
    }

    public function getTitleProperty(): string
    {
        return $this->targetStatus === 'rejected_rem'
            ? 'Marquer comme dossier incomplet'
            : 'Rejeter la demande (ADP)';
    }
}; ?>

<div>
    <x-ui.modal name="reject-badge-request" maxWidth="xl">
        <form wire:submit="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">{{ $this->title }}</h2>
                <p class="text-sm text-foreground-muted">Indiquez le motif du rejet — il sera communiqué au demandeur par email.</p>
            </div>

            <x-ui.textarea
                wire:model="rejectReason"
                label="Motif du rejet"
                placeholder="Précisez la raison du rejet..."
                :rows="5"
                :error="$errors->first('rejectReason')"
                required
            />

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="danger">
                    <span wire:loading.remove wire:target="submit">Confirmer le rejet</span>
                    <span wire:loading wire:target="submit">Envoi...</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
