<?php

namespace App\Mail\Badge;

use App\Models\BadgeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprovedByRem extends Mailable
{
    use Queueable, SerializesModels;

    public $badgeRequest;

    public function __construct(BadgeRequest $badgeRequest)
    {
        $this->badgeRequest = $badgeRequest;
    }

    public function build()
    {
        return $this->view('emails.badge.approved-by-rem')
            ->subject('Mise à jour de votre demande de badge')
            ->with([
                'name' => $this->badgeRequest->creator->name,
            ]);
    }
}
