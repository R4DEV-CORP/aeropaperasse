<?php

namespace App\Mail;

use App\Models\Badge;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BadgeCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $badge;

    public function __construct(Badge $badge)
    {
        $this->badge = $badge;
    }

    public function build()
    {
        return $this->view('emails.badge.created')
                    ->subject('Nouveau badge créé')
                    ->with([
                        'badge_number' => $this->badge->badge_number,
                        'nom' => $this->badge->badgeRequest->nom,
                        'prenom' => $this->badge->badgeRequest->prenom,
                        'email' => $this->badge->badgeRequest->email,
                        'expiry_date' => $this->badge->expiry_date,
                    ]);
    }
}