<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        \Log::info('LoginForm: Tentative de connexion avec Auth Laravel', [
            'email' => $this->email,
        ]);

        try {
            if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
                $user = Auth::user();
                
                // Créer un token Sanctum pour les appels API futurs
                $token = $user->createToken('auth_token')->plainTextToken;
                
                \Log::info('LoginForm: Connexion réussie', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role
                ]);

                $this->redirect('/');
            } else {
                $this->redirect('/login');
            }
        } catch (\Exception $e) {
            \Log::error('LoginForm: Erreur de connexion', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $this->addError('email', 'Une erreur est survenue lors de la connexion.');
        }
    }
    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
