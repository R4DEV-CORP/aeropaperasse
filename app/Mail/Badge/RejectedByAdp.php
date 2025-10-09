<?php

namespace App\Mail\Badge;

use App\Models\BadgeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RejectedByAdp extends Mailable
{
    use Queueable, SerializesModels;

    public $badgeRequest;

    public function __construct(BadgeRequest $badgeRequest)
    {
        $this->badgeRequest = $badgeRequest;
    }

    public function build()
    {
        return $this->view('emails.badge.rejected-by-adp')
            ->subject('Votre demande de badge a été rejetée par ADP')
            ->with([
                'nom' => $this->badgeRequest->nom,
                'prenom' => $this->badgeRequest->prenom,
                'badge_request_id' => $this->badgeRequest->id,
            ]);
    }
}
