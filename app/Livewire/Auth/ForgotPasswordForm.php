<?php

namespace App\Livewire\Auth;

use App\Mail\PasswordResetMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class ForgotPasswordForm extends Component
{
    public string $email = '';

    public string $successMessage = '';

    public function forgotPassword()
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $this->email)->first();

        if (! $user) {
            $this->addError('email', 'Cet email n\'est pas associé à un compte.');

            return;
        }

        // Supprimer les anciens tokens
        PasswordResetToken::where('email', $this->email)->delete();

        // Créer un nouveau token
        $token = Str::random(64);

        PasswordResetToken::create([
            'email' => $this->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        // Envoyer l'email avec le lien de réinitialisation
        Mail::to($this->email)->send(new PasswordResetMail($token, $this->email));

        $this->successMessage = 'Un lien de réinitialisation a été envoyé à votre email.';

    }

    public function render()
    {
        return view('livewire.auth.forgot-password-form');
    }
}
