<?php

use App\Services\Auth\AuthService;
use App\Services\Auth\UserRedirectService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::auth')]
#[Title('Changement de mot de passe')]
class extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $isFirstLogin = false;

    public function mount(AuthService $authService): void
    {
        $accessCheck = $authService->canAccessChangePassword();

        if (! $accessCheck['can_access']) {
            $this->redirect('/login');

            return;
        }

        $this->isFirstLogin = $accessCheck['is_first_login'];
    }

    public function changePassword(AuthService $authService, UserRedirectService $redirectService): void
    {
        $this->validate([
            'current_password' => ['required'],
            'password' => ['required', 'min:8', 'confirmed', 'different:current_password'],
            'password_confirmation' => ['required'],
        ], [
            'current_password.required' => 'Le mot de passe actuel est requis.',
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.different' => 'Le nouveau mot de passe doit être différent de l\'ancien.',
            'password_confirmation.required' => 'Veuillez confirmer le mot de passe.',
        ]);

        $user = Auth::user();

        $result = $authService->changePasswordAuthenticated($user, $this->current_password, $this->password);

        if (! $result['success']) {
            $this->addError('current_password', $result['message']);

            return;
        }

        session()->flash('success', $result['message']);
        $this->redirect($redirectService->getRedirectPath($user));
    }
}; ?>

<div class="space-y-6">
    <div class="space-y-2 text-center">
        <h1 class="text-2xl font-semibold text-foreground">
            @if ($isFirstLogin)
                Définissez votre mot de passe
            @else
                Modifier mon mot de passe
            @endif
        </h1>
        <p class="text-sm text-foreground-muted">
            @if ($isFirstLogin)
                Bienvenue sur Aéropaperasse. Pour des raisons de sécurité, choisissez un nouveau mot de passe avant
                d'accéder à votre espace.
            @else
                Saisissez votre mot de passe actuel puis votre nouveau mot de passe.
            @endif
        </p>
    </div>

    <form wire:submit="changePassword" class="space-y-4">
        <x-ui.input
            wire:model="current_password"
            type="password"
            label="{{ $isFirstLogin ? 'Mot de passe temporaire' : 'Mot de passe actuel' }}"
            placeholder="••••••••"
            autocomplete="current-password"
            required
            autofocus
            toggle-password
        />

        <x-ui.input
            wire:model="password"
            type="password"
            label="Nouveau mot de passe"
            placeholder="••••••••"
            autocomplete="new-password"
            hint="8 caractères minimum."
            required
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

        @error('current_password')
            <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
        @enderror

        @error('password')
            <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
        @enderror

        @error('password_confirmation')
            <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
        @enderror

        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled" wire:target="changePassword">
            <span wire:loading.remove wire:target="changePassword">
                {{ $isFirstLogin ? 'Activer mon compte' : 'Mettre à jour le mot de passe' }}
            </span>
            <span wire:loading wire:target="changePassword">Mise à jour…</span>
        </x-ui.button>
    </form>

    @unless ($isFirstLogin)
        <div class="text-center">
            <x-ui.button variant="link" href="/login" wire:navigate>
                Retour
            </x-ui.button>
        </div>
    @endunless
</div>
