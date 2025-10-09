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
        $data = [
            'badge_number' => $this->badge->badge_number,
            'nom' => $this->badge->badgeRequest->nom,
            'prenom' => $this->badge->badgeRequest->prenom,
            'previous_status' => $this->previousStatus,
            'current_status' => $this->badge->status,
            'expiry_date' => $this->badge->expiry_date,
        ];

        if ($this->badge->status === 'ready_for_delivery') {
            return $this->view('emails.badge-request.ready-for-pickup')
                ->subject('Votre badge est prêt à être récupéré')
                ->with($data);
        }

        return $this->view('emails.badge.status-updated')
            ->subject('Statut du badge modifié')
            ->with($data);
    }
}
