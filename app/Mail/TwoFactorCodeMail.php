<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class TwoFactorCodeMail extends Mailable
{
    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->view('emails.two-factor-code')
            ->subject('Code de vérification');
    }
}
