<?php

namespace App\Mail;

use App\Models\Badge;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BadgeReturned extends Mailable
{
    use Queueable, SerializesModels;

    public $badge;

    public function __construct(Badge $badge)
    {
        $this->badge = $badge;
    }

    public function build()
    {
        return $this->view('emails.badge.returned')
                    ->subject('Badge restitué')
                    ->with([
                        'badge_number' => $this->badge->badge_number,
                        'nom' => $this->badge->badgeRequest->nom,
                        'prenom' => $this->badge->badgeRequest->prenom,
                        'returned_at' => $this->badge->returned_at,
                        'return_document' => $this->badge->return_document,
                    ]);
    }
}