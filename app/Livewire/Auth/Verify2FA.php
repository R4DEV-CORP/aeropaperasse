<?php

namespace App\Livewire\Auth;

use App\Services\Auth\AuthService;
use App\Services\Auth\UserRedirectService;
use Livewire\Component;

class Verify2FA extends Component
{
    public string $code = '';

    public function mount()
    {
        // Vérifier si une session 2FA est active
        $authService = app(AuthService::class);
        $user = $authService->getTwoFAUser();

        if (! $user) {
            // Pas de session 2FA active, rediriger vers login
            $this->redirect('/login');
        }
    }

    public function verifyCode()
    {
        // Validation du champ code
        $this->validate([
            'code' => 'required|string|size:6|regex:/^[0-9]+$/',
        ], [
            'code.required' => 'Le code de vérification est requis.',
            'code.size' => 'Le code doit contenir exactement 6 chiffres.',
            'code.regex' => 'Le code ne doit contenir que des chiffres.',
        ]);

        $authService = app(AuthService::class);

        // Vérifier le code 2FA
        $result = $authService->verifyTwoFACode($this->code);

        if (! $result['success']) {
            $this->addError('code', $result['message']);

            return;
        }

        // Vérifier si c'est la première connexion
        if ($result['user']->is_new) {
            // Redirect vers la page de changement de mot de passe obligatoire
            $this->redirect('/change-password');

            return;
        }

        // Redirection vers le dashboard selon le rôle
        $redirectService = app(UserRedirectService::class);
        $redirectPath = $redirectService->getRedirectPath($result['user']);

        $this->redirect($redirectPath);
    }

    public function cancel()
    {
        $authService = app(AuthService::class);
        $authService->cancelTwoFA();

        $this->redirect('/login');
    }

    public function render()
    {
        // Récupérer l'utilisateur pour afficher son email dans la vue
        $authService = app(AuthService::class);
        $user = $authService->getTwoFAUser();

        return view('livewire.auth.verify2-f-a', [
            'userEmail' => $user ? $user->email : null,
        ]);
    }
}
