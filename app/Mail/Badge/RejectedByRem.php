<?php

namespace App\Mail\Badge;

use App\Models\BadgeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RejectedByRem extends Mailable
{
    use Queueable, SerializesModels;

    public $badgeRequest;
    public $motifRejet;

    public function __construct(BadgeRequest $badgeRequest, string $motifRejet)
    {
        $this->badgeRequest = $badgeRequest;
        $this->motifRejet = $motifRejet;
    }

    public function build()
    {
        return $this->view('emails.badge.rejected-by-rem')
                    ->subject('Mise à jour de votre demande de badge')
                    ->with([
                        'nom' => $this->badgeRequest->nom,
                        'prenom' => $this->badgeRequest->prenom,
                        'badge_request_id' => $this->badgeRequest->id,
                        'motif_rejet' => $this->motifRejet,
                    ]);
    }
}