<?php

use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new
#[Layout('layouts::auth')]
#[Title('Réinitialisation du mot de passe')]
class extends Component
{
    #[Url]
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $tokenInvalid = false;

    public ?PasswordResetToken $resetToken = null;

    public function mount(): void
    {
        $this->resetToken = PasswordResetToken::where('token', $this->token)->first();

        if (! $this->resetToken || $this->resetToken->created_at->addHour() < now()) {
            $this->tokenInvalid = true;

            return;
        }

        $this->email = $this->resetToken->email;
    }

    public function resetPassword(): void
    {
        if ($this->tokenInvalid || ! $this->resetToken) {
            return;
        }

        $this->validate([
            'password' => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password_confirmation.required' => 'Veuillez confirmer le mot de passe.',
        ]);

        $user = User::where('email', $this->email)->first();

        if (! $user) {
            $this->addError('email', 'Aucun utilisateur trouvé avec cet email.');

            return;
        }

        $user->password = Hash::make($this->password);
        $user->is_new = false;
        $user->save();

        $this->resetToken->delete();

        session()->flash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
        $this->redirect('/login');
    }
}; ?>

<div class="space-y-6">
    <div class="space-y-2 text-center">
        <h1 class="text-2xl font-semibold text-foreground">Réinitialiser le mot de passe</h1>
        @unless ($tokenInvalid)
            <p class="text-sm text-foreground-muted">
                Choisissez un nouveau mot de passe pour <span class="font-medium text-foreground">{{ $email }}</span>.
            </p>
        @endunless
    </div>

    @if ($tokenInvalid)
        <x-ui.alert variant="danger" title="Lien invalide ou expiré">
            Le lien de réinitialisation est invalide ou a expiré. Veuillez en demander un nouveau.
        </x-ui.alert>

        <div class="text-center">
            <x-ui.button variant="primary" href="/forgot-password" class="w-full" wire:navigate>
                Demander un nouveau lien
            </x-ui.button>
        </div>
    @else
        <form wire:submit="resetPassword" class="space-y-4">
            <x-ui.input
                wire:model="password"
                type="password"
                label="Nouveau mot de passe"
                placeholder="••••••••"
                autocomplete="new-password"
                hint="8 caractères minimum."
                required
                autofocus
                toggle-password
            />

            <x-ui.input
                wire:model="password_confirmation"
                type="password"
                label="Confirmation du nouveau mot de passe"
                placeholder="••••••••"
                autocomplete="new-password"
                required
                toggle-password
            />

            @error('password')
                <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
            @enderror

            @error('password_confirmation')
                <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
            @enderror

            @error('email')
                <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
            @enderror

            <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled" wire:target="resetPassword">
                <span wire:loading.remove wire:target="resetPassword">Réinitialiser le mot de passe</span>
                <span wire:loading wire:target="resetPassword">Mise à jour…</span>
            </x-ui.button>
        </form>

        <div class="text-center">
            <x-ui.button variant="link" href="/login" wire:navigate>
                Retour à la connexion
            </x-ui.button>
        </div>
    @endif
</div>
