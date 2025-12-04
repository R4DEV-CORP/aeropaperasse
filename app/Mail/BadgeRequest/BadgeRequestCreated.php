<?php

namespace App\Mail\BadgeRequest;

use App\Models\BadgeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BadgeRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $badgeRequest;

    public function __construct(BadgeRequest $badgeRequest)
    {
        $this->badgeRequest = $badgeRequest;
    }

    public function build()
    {
        return $this->view('emails.badge-request.created')
            ->subject('Nouvelle demande de badge reçue')
            ->with([
                'name' => $this->badgeRequest->creator->name,
            ]);
    }
}
