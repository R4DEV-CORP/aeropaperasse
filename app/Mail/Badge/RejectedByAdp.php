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

    public $rejectReason;

    public function __construct(BadgeRequest $badgeRequest, string $rejectReason)
    {
        $this->badgeRequest = $badgeRequest;
        $this->rejectReason = $rejectReason;
    }

    public function build()
    {
        return $this->view('emails.badge.rejected-by-adp')
            ->subject('Votre demande de badge a été rejetée par ADP')
            ->with([
                'name' => $this->badgeRequest->creator->name,
                'reject_reason' => $this->rejectReason,
            ]);
    }
}
