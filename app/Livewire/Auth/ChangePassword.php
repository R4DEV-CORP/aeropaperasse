<?php

namespace App\Livewire\Auth;

use App\Services\Auth\AuthService;
use App\Services\Auth\UserRedirectService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChangePassword extends Component
{
    public string $password = '';

    public string $password_confirmation = '';

    public string $current_password = '';

    public bool $isFirstLogin = false;

    public ?string $userEmail = null;

    public function mount()
    {
        $authService = app(AuthService::class);

        // Vérifier les droits d'accès
        $accessCheck = $authService->canAccessChangePassword();

        if (! $accessCheck['can_access']) {
            $this->redirect('/login');

            return;
        }

        $this->isFirstLogin = $accessCheck['is_first_login'];

        if (Auth::check()) {
            $this->userEmail = Auth::user()->email;
        }
    }

    public function changePassword()
    {
        $authService = app(AuthService::class);

        $this->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ], [
            'current_password.required' => 'Le mot de passe actuel est requis.',
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password_confirmation.required' => 'Veuillez confirmer le mot de passe.',
        ]);

        $user = Auth::user();

        $result = $authService->changePasswordAuthenticated($user, $this->current_password, $this->password);

        if (! $result['success']) {
            $this->addError('current_password', $result['message']);

            return;
        }

        // Succès : redirection vers le dashboard
        $redirectService = app(UserRedirectService::class);
        $redirectPath = $redirectService->getRedirectPath($user);

        session()->flash('success', $result['message']);
        $this->redirect($redirectPath);
    }

    public function render()
    {
        return view('livewire.auth.change-password');
    }
}
