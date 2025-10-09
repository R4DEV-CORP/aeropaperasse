<?php

namespace App\Mail\Badge;

use App\Models\BadgeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReadyForPickup extends Mailable
{
    use Queueable, SerializesModels;

    public $badgeRequest;

    public function __construct(BadgeRequest $badgeRequest)
    {
        $this->badgeRequest = $badgeRequest;
    }

    public function build()
    {
        return $this->view('emails.badge.ready-for-pickup')
            ->subject('Votre badge est prêt à être remis')
            ->with([
                'nom' => $this->badgeRequest->nom,
                'prenom' => $this->badgeRequest->prenom,
                'badge_request_id' => $this->badgeRequest->id,
            ]);
    }
}
