<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Services\Auth\AuthService;
use App\Services\Auth\UserRedirectService;

class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'password.required' => 'Le mot de passe est obligatoire.'
        ]);

        // Utilisation simplifiée des services
        $authService = app(AuthService::class);
        $redirectService = app(UserRedirectService::class);
        
        $result = $authService->login($this->email, $this->password);

        // Gestion des erreurs
        if (!$result['success']) {
            $this->addError('email', $result['message']);
            return;
        }

        // Gestion de la 2FA
        if ($result['requires2FA']) {
            $this->redirect('/verify-2fa');
            return;
        }

        // Créer la session web
        $authService->createWebSession($result['user']);

        // Redirection simple
        $redirectPath = $redirectService->getRedirectPath($result['user']);
        $this->redirect($redirectPath);
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
