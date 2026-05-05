<?php

use App\Services\Auth\AuthService;
use App\Services\Auth\UserRedirectService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::auth')]
#[Title('Connexion')]
class extends Component
{
    public string $email = '';

    public string $password = '';

    public function login(AuthService $authService, UserRedirectService $redirectService): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $result = $authService->login($this->email, $this->password);

        if (! $result['success']) {
            $this->addError('email', $result['message']);

            return;
        }

        if ($result['requires2FA']) {
            $this->redirect('/verify-2fa');

            return;
        }

        $authService->createWebSession($result['user']);

        $this->redirect($redirectService->getRedirectPath($result['user']));
    }
}; ?>

<div class="space-y-6">
    <div class="space-y-1 text-center">
        <h1 class="text-2xl font-semibold text-foreground">Connexion</h1>
        <p class="text-sm text-foreground-muted">
            Connectez-vous avec votre email pour accéder à vos outils et ressources.
        </p>
    </div>

    <form wire:submit="login" class="space-y-4">
        <x-ui.input
            wire:model="email"
            type="email"
            label="Email"
            placeholder="vous@exemple.com"
            autocomplete="email"
            required
            autofocus
        />

        <x-ui.input
            wire:model="password"
            type="password"
            label="Mot de passe"
            placeholder="••••••••"
            autocomplete="current-password"
            required
        />

        @error('email')
            <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
        @enderror

        @error('password')
            <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
        @enderror

        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled" wire:target="login">
            <span wire:loading.remove wire:target="login">Se connecter</span>
            <span wire:loading wire:target="login">Connexion en cours…</span>
        </x-ui.button>
    </form>

    <div class="text-center">
        <x-ui.button variant="link" href="/forgot-password" wire:navigate>
            Mot de passe oublié ?
        </x-ui.button>
    </div>
</div>
