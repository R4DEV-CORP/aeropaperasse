<?php

namespace App\Mail;

use App\Models\BadgeComment;
use App\Models\BadgeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BadgeCommentCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $badgeRequest;

    public $comment;

    public function __construct(BadgeRequest $badgeRequest, BadgeComment $comment)
    {
        $this->badgeRequest = $badgeRequest;
        $this->comment = $comment;
    }

    public function build()
    {
        return $this->view('emails.badge-comment.created')
            ->subject('Nouveau commentaire sur votre demande de badge')
            ->with([
                'badge_request' => $this->badgeRequest,
                'comment' => $this->comment,
                'comment_author' => $this->comment->user,
            ]);
    }
}
