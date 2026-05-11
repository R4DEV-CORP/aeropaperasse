<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Coworker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $coworkerId = null;

    public string $password = '';

    public string $password_confirmation = '';

    #[On('open-reset-password')]
    public function open(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $coworker = Coworker::with('user')->find($id);
        if (! $coworker || ! $coworker->user_id) {
            $this->toast('Ce collaborateur n\'a pas de compte utilisateur.', 'warning');

            return;
        }

        $this->coworkerId = $id;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'coworker-reset-password');
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
        if (! auth()->user()->isAdmin() || $this->coworkerId === null) {
            return;
        }

        $this->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
            'password_confirmation' => 'required',
        ], [
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password_confirmation.required' => 'La confirmation du mot de passe est requise.',
        ]);

        $coworker = Coworker::with('user')->find($this->coworkerId);
        if (! $coworker || ! $coworker->user) {
            $this->toast('Compte utilisateur introuvable.', 'danger');

            return;
        }

        try {
            $coworker->user->update([
                'password' => Hash::make($this->password),
                'is_new' => true,
            ]);

            $this->toast(
                'Mot de passe réinitialisé pour '.$coworker->firstname.' '.$coworker->lastname.'.',
                'success',
                'Mot de passe modifié'
            );

            $this->dispatch('close-modal', name: 'coworker-reset-password');
            $this->dispatch('coworker-updated');

            $this->reset(['coworkerId', 'password', 'password_confirmation']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation du mot de passe', [
                'error' => $e->getMessage(),
                'coworker_id' => $coworker->id,
                'user_id' => auth()->user()->id,
            ]);

            $this->toast('Une erreur est survenue lors de la réinitialisation.', 'danger');
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'coworker-reset-password');
        $this->reset(['coworkerId', 'password', 'password_confirmation']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-ui.modal name="coworker-reset-password" maxWidth="lg">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Réinitialiser le mot de passe</h2>
                <p class="text-sm text-foreground-muted">L'utilisateur devra changer son mot de passe à la prochaine connexion.</p>
            </div>

            @if ($this->coworker)
                @php $cw = $this->coworker; @endphp
                <div class="grid grid-cols-1 gap-3 rounded-md border border-border bg-slate-50 p-4 text-sm sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Utilisateur</div>
                        <div class="mt-0.5 font-medium text-foreground">{{ $cw->firstname }} {{ $cw->lastname }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Email</div>
                        <div class="mt-0.5 text-foreground">{{ $cw->user?->email ?? '—' }}</div>
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <x-ui.input
                    label="Nouveau mot de passe"
                    type="password"
                    wire:model.blur="password"
                    required
                    togglePassword
                    :error="$errors->first('password')"
                />

                <x-ui.input
                    label="Confirmer le mot de passe"
                    type="password"
                    wire:model.blur="password_confirmation"
                    required
                    togglePassword
                    :error="$errors->first('password_confirmation')"
                />
            </div>

            <x-ui.alert variant="info">
                L'utilisateur devra définir un nouveau mot de passe lors de sa prochaine connexion.
            </x-ui.alert>

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="submit">Réinitialiser</span>
                    <span wire:loading wire:target="submit">Enregistrement…</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
