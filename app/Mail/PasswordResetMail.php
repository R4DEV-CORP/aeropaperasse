<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        return $this->subject('Réinitialisation de votre mot de passe')
            ->view('emails.password-reset')
            ->with([
                'resetUrl' => env('APP_URL') . "/reset-password?token={$this->token}&email={$this->email}",
            ]);
    }
}
