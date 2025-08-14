<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;
    public $appUrl;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
        $this->appUrl = env('FRONTEND_URL', 'http://localhost:3000');
    }

    public function build()
    {
        return $this->subject('Réinitialisation de votre mot de passe')
                    ->view('emails.password-reset')
                    ->with([
                        'resetUrl' => "{$this->appUrl}/reset-password?token={$this->token}&email={$this->email}"
                    ]);
    }
}
