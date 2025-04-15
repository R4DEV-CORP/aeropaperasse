<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Badge;

class BadgeExpiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $badge;
    public $daysRemaining;

    /**
     * Create a new message instance.
     *
     * @param Badge $badge
     * @param int $daysRemaining
     * @return void
     */
    public function __construct(Badge $badge, int $daysRemaining)
    {
        $this->badge = $badge;
        $this->daysRemaining = $daysRemaining;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Notification d\'expiration de votre badge')
            ->view('emails.badge-expiry')
            ->with([
                'badgeNumber' => $this->badge->badge_number,
                'nom' => $this->badge->badgeRequest->nom,
                'prenom' => $this->badge->badgeRequest->prenom,
                'expiryDate' => $this->badge->expiry_date,
                'daysRemaining' => $this->daysRemaining
            ]);
    }
}