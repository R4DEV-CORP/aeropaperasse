<?php

namespace App\Livewire\Auth;

use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Url;
use Livewire\Component;

class ResetPasswordForm extends Component
{
    #[Url]
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $successMessage = '';

    public $resetToken;

    public function mount()
    {
        $this->resetToken = PasswordResetToken::where('token', $this->token)->first();
        if (! $this->resetToken) {
            $this->addError('token', 'Ce lien de réinitialisation est invalide ou a expiré.');

            return;
        }

        if ($this->resetToken->created_at->addHours(1) < now()) {
            $this->addError('token', 'Ce lien de réinitialisation est invalide ou a expiré.');

            return;
        }

        $this->email = $this->resetToken->email;
    }

    public function resetPassword()
    {
        $this->validate([
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ]);

        $user = User::where('email', $this->email)->first();
        if (! $user) {
            $this->addError('email', 'Aucun utilisateur trouvé avec cet email.');

            return;
        }

        $user->password = Hash::make($this->password);
        $user->save();

        $this->resetToken->delete();

        $this->successMessage = 'Votre mot de passe a été réinitialisé avec succès.';
    }

    public function render()
    {
        return view('livewire.auth.reset-password-form');
    }
}
