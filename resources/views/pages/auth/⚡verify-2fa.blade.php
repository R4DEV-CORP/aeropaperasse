<?php

use App\Services\Auth\AuthService;
use App\Services\Auth\UserRedirectService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::auth')]
#[Title('Vérification 2FA')]
class extends Component
{
    public string $code = '';

    public ?string $userEmail = null;

    public function mount(AuthService $authService): void
    {
        $user = $authService->getTwoFAUser();

        if (! $user) {
            $this->redirect('/login');

            return;
        }

        $this->userEmail = $user->email;
    }

    public function verifyCode(AuthService $authService, UserRedirectService $redirectService): void
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
        ], [
            'code.required' => 'Le code de vérification est requis.',
            'code.size' => 'Le code doit contenir exactement 6 chiffres.',
            'code.regex' => 'Le code ne doit contenir que des chiffres.',
        ]);

        $result = $authService->verifyTwoFACode($this->code);

        if (! $result['success']) {
            $this->addError('code', $result['message']);

            return;
        }

        $this->redirect($redirectService->getRedirectPath($result['user']));
    }
}; ?>

<div class="space-y-6">
    <div class="space-y-2 text-center">
        <h1 class="text-2xl font-semibold text-foreground">Vérification en deux étapes</h1>
        <p class="text-sm text-foreground-muted">
            Un code à 6 chiffres a été envoyé
            @if ($userEmail)
                à <span class="font-medium text-foreground">{{ $userEmail }}</span>.
            @else
                par email.
            @endif
            Saisissez-le pour finaliser votre connexion.
        </p>
    </div>

    <form wire:submit="verifyCode" class="space-y-4">
        <x-ui.otp-input wire:model="code" :length="6" />

        @error('code')
            <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
        @enderror

        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled" wire:target="verifyCode">
            <span wire:loading.remove wire:target="verifyCode">Vérifier le code</span>
            <span wire:loading wire:target="verifyCode">Vérification…</span>
        </x-ui.button>
    </form>

    <div class="text-center">
        <x-ui.button variant="link" href="/login" wire:navigate>
            Retour à la connexion
        </x-ui.button>
    </div>
</div>
