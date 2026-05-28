<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Mail\UserCreated;
use App\Models\Coworker;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $coworkerId = null;

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $can_access_formation = false;

    public string $role = 'client';

    #[On('open-make-user')]
    public function open(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $coworker = Coworker::find($id);
        if (! $coworker) {
            return;
        }

        if ($coworker->user_id) {
            $this->toast('Ce collaborateur a déjà un compte utilisateur.', 'warning');

            return;
        }

        $this->coworkerId = $id;
        $this->email = $coworker->email ?? '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->can_access_formation = false;
        $this->role = 'client';
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'coworker-make-user');
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
        $authUser = auth()->user();
        if (! $authUser->isAdmin() || $this->coworkerId === null) {
            return;
        }

        $allowedRoles = ['client', 'sclient', 'aclient', 'rem_admin'];
        if ($authUser->isSAdmin()) {
            $allowedRoles[] = 'rem_super_admin';
        }

        $this->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'password_confirmation' => 'required',
            'can_access_formation' => 'boolean',
            'role' => 'required|in:'.implode(',', $allowedRoles),
        ], [
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée par un autre compte.',
            'password.required' => 'Le mot de passe est requis.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password_confirmation.required' => 'La confirmation du mot de passe est requise.',
            'role.required' => 'Le rôle est requis.',
            'role.in' => 'Le rôle sélectionné n\'est pas valide.',
        ]);

        $coworker = Coworker::find($this->coworkerId);
        if (! $coworker || $coworker->user_id) {
            $this->toast('Action non disponible.', 'danger');

            return;
        }

        try {
            $newUser = User::create([
                'name' => $coworker->firstname.' '.$coworker->lastname,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role' => $this->role,
                'client_id' => $coworker->client_id,
                'coworker_id' => $coworker->id,
                'can_access_formation' => $this->can_access_formation,
                'is_new' => true,
            ]);

            $coworker->update(['user_id' => $newUser->id]);

            Mail::to($this->email)->send(new UserCreated($newUser, $this->password));

            $this->toast(
                'Compte utilisateur créé pour '.$coworker->firstname.' '.$coworker->lastname.'.',
                'success',
                'Compte créé'
            );

            $this->dispatch('close-modal', name: 'coworker-make-user');
            $this->dispatch('coworker-updated');

            $this->reset(['coworkerId', 'email', 'password', 'password_confirmation', 'can_access_formation']);
            $this->role = 'client';
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du compte utilisateur', [
                'error' => $e->getMessage(),
                'coworker_id' => $coworker->id,
                'user_id' => auth()->user()->id,
            ]);

            $this->toast('Une erreur est survenue lors de la création du compte.', 'danger');
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'coworker-make-user');
        $this->reset(['coworkerId', 'email', 'password', 'password_confirmation', 'can_access_formation']);
        $this->role = 'client';
        $this->resetErrorBag();
    }
}; ?>

@php
    $authUser = auth()->user();
    $roleOptions = [
        ['value' => 'client', 'label' => 'Client'],
        ['value' => 'sclient', 'label' => 'SClient'],
        ['value' => 'aclient', 'label' => 'AClient'],
        ['value' => 'rem_admin', 'label' => 'Administrateur REM'],
    ];
    if ($authUser?->isSAdmin()) {
        $roleOptions[] = ['value' => 'rem_super_admin', 'label' => 'Super admin REM'];
    }
@endphp

<div>
    <x-ui.modal name="coworker-make-user" maxWidth="2xl">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Créer un compte utilisateur</h2>
                <p class="text-sm text-foreground-muted">Le collaborateur recevra un email avec ses identifiants.</p>
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

            <div class="space-y-4">
                <x-ui.input
                    label="Email du compte"
                    type="email"
                    wire:model.blur="email"
                    required
                    :error="$errors->first('email')"
                />

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-ui.input
                        label="Mot de passe"
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

                <x-ui.select
                    label="Rôle"
                    :value="$role"
                    wire:model.live="role"
                    :options="$roleOptions"
                    required
                    :error="$errors->first('role')"
                />

                <x-ui.checkbox
                    wire:model.live="can_access_formation"
                    :checked="$can_access_formation"
                    label="Accès aux formations"
                    description="L'utilisateur pourra accéder à l'onglet formations."
                />
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="submit">Créer le compte</span>
                    <span wire:loading wire:target="submit">Création…</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
