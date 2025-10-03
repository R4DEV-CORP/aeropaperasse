<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Services\Auth\AuthService;
use App\Services\Auth\UserRedirectService;

class TwoFactorForm extends Component
{
    public string $code = '';
    
    public function mount()
    {
        // Vérifier si une session 2FA est active
        $authService = app(AuthService::class);
        $user = $authService->getTwoFAUser();
        
        if (!$user) {
            // Pas de session 2FA active, rediriger vers login
            $this->redirect('/login');
        }
    }

    public function verifyCode()
    {
        $this->validate([
            'code' => 'required|string|size:6',
        ]);

        $authService = app(AuthService::class);
        $redirectService = app(UserRedirectService::class);
        
        $result = $authService->verifyTwoFACode($this->code);

        if (!$result['success']) {
            $this->addError('code', $result['message']);
            return;
        }

        // Redirection après connexion réussie
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
        
        return view('livewire.auth.two-factor-form', [
            'userEmail' => $user ? $user->email : null
        ]);
    }
}
