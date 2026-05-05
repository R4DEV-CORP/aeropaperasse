<?php

use App\Mail\PasswordResetMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::auth')]
#[Title('Mot de passe oublié')]
class extends Component
{
    public string $email = '';

    public string $successMessage = '';

    public function forgotPassword(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
        ]);

        $user = User::where('email', $this->email)->first();

        if (! $user) {
            $this->addError('email', 'Cet email n\'est pas associé à un compte.');

            return;
        }

        PasswordResetToken::where('email', $this->email)->delete();

        $token = Str::random(64);

        PasswordResetToken::create([
            'email' => $this->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        Mail::to($this->email)->send(new PasswordResetMail($token, $this->email));

        $this->reset('email');
        $this->successMessage = 'Un lien de réinitialisation a été envoyé à votre adresse email.';
    }
}; ?>

<div class="space-y-6">
    <div class="space-y-2 text-center">
        <h1 class="text-2xl font-semibold text-foreground">Mot de passe oublié</h1>
        <p class="text-sm text-foreground-muted">
            Saisissez votre email pour recevoir un lien de réinitialisation.
        </p>
    </div>

    @if ($successMessage)
        <x-ui.alert variant="success">{{ $successMessage }}</x-ui.alert>
    @endif

    <form wire:submit="forgotPassword" class="space-y-4">
        <x-ui.input
            wire:model="email"
            type="email"
            label="Email"
            placeholder="vous@exemple.com"
            autocomplete="email"
            required
            autofocus
        />

        @error('email')
            <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
        @enderror

        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled" wire:target="forgotPassword">
            <span wire:loading.remove wire:target="forgotPassword">Recevoir le lien de réinitialisation</span>
            <span wire:loading wire:target="forgotPassword">Envoi en cours…</span>
        </x-ui.button>
    </form>

    <div class="text-center">
        <x-ui.button variant="link" href="/login" wire:navigate>
            Retour à la connexion
        </x-ui.button>
    </div>
</div>
