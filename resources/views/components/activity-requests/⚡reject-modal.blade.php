<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Mail\ActivityRequestStatusUpdated;
use App\Models\ActivityRequest;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $activityRequestId = null;

    public string $rejectReason = '';

    #[On('reject-activity-request')]
    public function open(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $this->activityRequestId = $id;
        $this->rejectReason = '';
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'reject-activity-request');
    }

    public function submit(): void
    {
        if (! auth()->user()->isAdmin() || $this->activityRequestId === null) {
            return;
        }

        $this->validate([
            'rejectReason' => ['required', 'string', 'min:3'],
        ], [
            'rejectReason.required' => 'Le motif du rejet est obligatoire.',
            'rejectReason.min' => 'Le motif doit contenir au moins 3 caractères.',
        ]);

        $activityRequest = ActivityRequest::findOrFail($this->activityRequestId);
        $activityRequest->update([
            'previous_status' => $activityRequest->status,
            'status' => 'rejected',
            'rejected_at' => now(),
            'reject_reason' => $this->rejectReason,
        ]);

        $email = $activityRequest->creator->email;
        if ($activityRequest->client->notification_email) {
            $email = $activityRequest->client->notification_email;
        }

        Mail::to($email)->send(new ActivityRequestStatusUpdated($activityRequest, $activityRequest->client));

        $this->toast('Demande rejetée avec succès.', 'success', 'Demande rejetée');

        $this->dispatch('close-modal', name: 'reject-activity-request');
        $this->dispatch('activity-request-rejected');

        $this->reset(['activityRequestId', 'rejectReason']);
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'reject-activity-request');
        $this->reset(['activityRequestId', 'rejectReason']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-ui.modal name="reject-activity-request" maxWidth="xl">
        <form wire:submit="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Rejeter la demande</h2>
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
                    <span wire:loading.remove wire:target="submit">Rejeter la demande</span>
                    <span wire:loading wire:target="submit">Envoi...</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
