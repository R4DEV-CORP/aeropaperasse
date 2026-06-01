<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Mail\VehiclePassStatusUpdated;
use App\Models\VehiclePass;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $vehiclePassId = null;

    public string $reason = '';

    #[On('open-reject-vehicle-pass')]
    public function open(int $id): void
    {
        if (! auth()->user()->isTenantManager()) {
            return;
        }

        $vp = VehiclePass::find($id);
        if (! $vp || $vp->status !== 'pending') {
            return;
        }

        $this->vehiclePassId = $id;
        $this->reason = '';
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'vehicle-pass-reject');
    }

    #[Computed]
    public function vehiclePass(): ?VehiclePass
    {
        if ($this->vehiclePassId === null) {
            return null;
        }

        return VehiclePass::with(['client', 'createdBy'])->find($this->vehiclePassId);
    }

    public function submit(): void
    {
        if (! auth()->user()->isTenantManager() || $this->vehiclePassId === null) {
            return;
        }

        if (mb_strlen(trim($this->reason)) < 5) {
            $this->addError('reason', 'Merci de saisir une raison (5 caractères minimum).');

            return;
        }

        $vp = VehiclePass::find($this->vehiclePassId);
        if (! $vp || $vp->status !== 'pending') {
            $this->toast('Demande introuvable ou déjà traitée.', 'danger');
            $this->dispatch('close-modal', name: 'vehicle-pass-reject');

            return;
        }

        try {
            $previousStatus = $vp->status;

            $vp->update([
                'previous_status' => $previousStatus,
                'status' => 'rejected',
                'rejected_at' => now(),
                'reject_reason' => trim($this->reason),
            ]);

            $this->sendStatusUpdateEmail($vp->fresh(), $previousStatus);

            $this->toast('Demande rejetée.', 'success', 'Rejet enregistré');
            $this->dispatch('close-modal', name: 'vehicle-pass-reject');
            $this->dispatch('vehicle-pass-rejected');

            $this->reset(['vehiclePassId', 'reason']);
        } catch (\Exception $e) {
            Log::error("Erreur lors du rejet du laissez-passer", [
                'error' => $e->getMessage(),
                'vehicle_pass_id' => $vp->id,
                'user_id' => auth()->user()->id,
            ]);
            $this->toast('Erreur lors du rejet.', 'danger');
        }
    }

    private function sendStatusUpdateEmail(VehiclePass $vp, string $previousStatus): void
    {
        try {
            $recipient = $vp->createdBy?->email;
            if ($recipient) {
                Mail::to($recipient)->send(new VehiclePassStatusUpdated($vp, $previousStatus));
            }
        } catch (\Exception $e) {
            Log::error("Erreur d'envoi de l'email de changement de statut", [
                'vehicle_pass_id' => $vp->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'vehicle-pass-reject');
        $this->reset(['vehiclePassId', 'reason']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-ui.modal name="vehicle-pass-reject" maxWidth="lg">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Rejeter la demande</h2>
                <p class="text-sm text-foreground-muted">La raison du rejet sera communiquée au demandeur par e-mail.</p>
            </div>

            @if ($this->vehiclePass)
                @php $vp = $this->vehiclePass; @endphp
                <div class="grid grid-cols-1 gap-3 rounded-md border border-border bg-slate-50 p-4 text-sm sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Immatriculation</div>
                        <div class="mt-0.5 font-mono font-medium text-foreground">{{ $vp->plate_number ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Société</div>
                        <div class="mt-0.5 text-foreground">{{ $vp->client?->company_name ?? '—' }}</div>
                    </div>
                </div>

                <x-ui.textarea
                    label="Raison du rejet"
                    wire:model.blur="reason"
                    rows="4"
                    placeholder="Expliquez pourquoi la demande est rejetée…"
                    required
                    :error="$errors->first('reason')"
                />
            @endif

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="danger">
                    <span wire:loading.remove wire:target="submit">Rejeter</span>
                    <span wire:loading wire:target="submit">Rejet…</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
