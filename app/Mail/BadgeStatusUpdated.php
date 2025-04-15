<?php

namespace App\Mail;

use App\Models\Badge;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BadgeStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $badge;
    public $previousStatus;

    public function __construct(Badge $badge, string $previousStatus)
    {
        $this->badge = $badge;
        $this->previousStatus = $previousStatus;
    }

    public function build()
    {
        return $this->view('emails.badge.status-updated')
                    ->subject('Statut du badge modifié')
                    ->with([
                        'badge_number' => $this->badge->badge_number,
                        'nom' => $this->badge->badgeRequest->nom,
                        'prenom' => $this->badge->badgeRequest->prenom,
                        'previous_status' => $this->previousStatus,
                        'current_status' => $this->badge->status,
                        'expiry_date' => $this->badge->expiry_date,
                    ]);
    }
}