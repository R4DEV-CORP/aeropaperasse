<?php

namespace App\Mail\Badge;

use App\Models\BadgeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Delivered extends Mailable
{
    use Queueable, SerializesModels;

    public $badgeRequest;

    public function __construct(BadgeRequest $badgeRequest)
    {
        $this->badgeRequest = $badgeRequest;
    }

    public function build()
    {
        return $this->view('emails.badge.delivered')
            ->subject('Votre badge a été remis')
            ->with([
                'name' => $this->badgeRequest->creator->name,
                'coworker' => $this->badgeRequest->coworker,
                'deliveredAt' => $this->badgeRequest->delivered_at,
            ]);
    }
}
