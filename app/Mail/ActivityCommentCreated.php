<?php

namespace App\Mail;

use App\Models\ActivityComment;
use App\Models\ActivityRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivityCommentCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $activityRequest;

    public $comment;

    public function __construct(ActivityRequest $activityRequest, ActivityComment $comment)
    {
        $this->activityRequest = $activityRequest;
        $this->comment = $comment;
    }

    public function build()
    {
        return $this->view('emails.activity-comment.created')
            ->subject('Nouveau commentaire sur votre demande d\'activité')
            ->with([
                'activity_request' => $this->activityRequest,
                'comment' => $this->comment,
                'comment_author' => $this->comment->user,
            ]);
    }
}
