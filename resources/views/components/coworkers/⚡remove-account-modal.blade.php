<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Coworker;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $coworkerId = null;

    public string $confirmation = '';

    #[On('open-remove-account')]
    public function open(int $id): void
    {
        if (! auth()->user()->isTenantManager()) {
            return;
        }

        $coworker = Coworker::with('user')->find($id);
        if (! $coworker || ! $coworker->user_id) {
            $this->toast('Ce collaborateur n\'a pas de compte utilisateur.', 'warning');

            return;
        }

        $this->coworkerId = $id;
        $this->confirmation = '';
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'coworker-remove-account');
    }

    #[Computed]
    public function coworker(): ?Coworker
    {
        if ($this->coworkerId === null) {
            return null;
        }

        return Coworker::with('user')->find($this->coworkerId);
    }

    public function submit(): void
    {
        if (! auth()->user()->isTenantManager() || $this->coworkerId === null) {
            return;
        }

        $coworker = Coworker::with('user')->find($this->coworkerId);
        if (! $coworker || ! $coworker->user) {
            $this->toast('Compte utilisateur introuvable.', 'danger');

            return;
        }

        if (auth()->id() === $coworker->user_id) {
            $this->toast('Vous ne pouvez pas retirer votre propre compte.', 'danger');

            return;
        }

        $expected = mb_strtolower(trim($coworker->user->email));
        if (mb_strtolower(trim($this->confirmation)) !== $expected) {
            $this->addError('confirmation', 'L\'email saisi ne correspond pas.');

            return;
        }

        try {
            DB::transaction(function () use ($coworker) {
                $user = $coworker->user;

                $coworker->update(['user_id' => null]);

                $user->update([
                    'coworker_id' => null,
                    'client_id' => null,
                    'email' => '_removed_'.now()->timestamp.'_'.$user->email,
                    'password' => Hash::make(Str::random(64)),
                    'two_factor_enabled' => false,
                    'is_new' => false,
                ]);
            });

            $this->toast(
                'Compte utilisateur retiré pour '.$coworker->firstname.' '.$coworker->lastname.'.',
                'success',
                'Compte retiré'
            );

            $this->dispatch('close-modal', name: 'coworker-remove-account');
            $this->dispatch('coworker-updated');

            $this->reset(['coworkerId', 'confirmation']);
        } catch (\Exception $e) {
            Log::error('Erreur lors du retrait du compte utilisateur', [
                'error' => $e->getMessage(),
                'coworker_id' => $coworker->id,
                'user_id' => auth()->user()->id,
            ]);

            $this->toast('Une erreur est survenue lors du retrait du compte.', 'danger');
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'coworker-remove-account');
        $this->reset(['coworkerId', 'confirmation']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-ui.modal name="coworker-remove-account" maxWidth="lg">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Retirer le compte utilisateur</h2>
                <p class="text-sm text-foreground-muted">Le collaborateur perdra son accès. La fiche collaborateur est conservée.</p>
            </div>

            @if ($this->coworker)
                @php $cw = $this->coworker; @endphp
                <div class="grid grid-cols-1 gap-3 rounded-md border border-border bg-slate-50 p-4 text-sm sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Collaborateur</div>
                        <div class="mt-0.5 font-medium text-foreground">{{ $cw->firstname }} {{ $cw->lastname }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Email du compte</div>
                        <div class="mt-0.5 font-mono text-foreground">{{ $cw->user?->email ?? '—' }}</div>
                    </div>
                </div>

                <x-ui.alert variant="danger">
                    L'utilisateur ne pourra plus se connecter et l'email sera libéré pour un nouveau compte. L'historique des actions qu'il a réalisées (demandes, badges) reste préservé.
                </x-ui.alert>

                <x-ui.input
                    label="Saisissez l'email pour confirmer"
                    wire:model.live="confirmation"
                    placeholder="{{ $cw->user?->email }}"
                    :error="$errors->first('confirmation')"
                />
            @endif

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="danger">
                    <span wire:loading.remove wire:target="submit">Retirer le compte</span>
                    <span wire:loading wire:target="submit">Suppression…</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
