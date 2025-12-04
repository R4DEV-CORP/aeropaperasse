<?php

namespace App\Mail;

use App\Models\Badge;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BadgeReturned extends Mailable
{
    use Queueable, SerializesModels;

    public $badge;

    public $return_badge_document;

    public function __construct(Badge $badge, $return_badge_document)
    {
        $this->badge = $badge;
        $this->return_badge_document = $return_badge_document;
    }

    public function build()
    {
        return $this->view('emails.badge.returned')
            ->subject('Badge restitué')
            ->attach(Storage::disk('public')->path($this->return_badge_document), [
                'as' => 'document_de_restitution.pdf',
                'mime' => 'application/pdf',
            ])
            ->with([
                'name' => $this->badge->badgeRequest->creator->name,
                'returned_at' => $this->badge->returned_at,
                'coworker_firstname' => $this->badge->badgeRequest->coworker->firstname,
                'coworker_lastname' => $this->badge->badgeRequest->coworker->lastname,
            ]);
    }
}
