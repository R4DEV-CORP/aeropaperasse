<?php

namespace App\Mail;

use App\Models\Discussion;
use App\Models\MessageComment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $discussion;

    public $comment;

    public function __construct(Discussion $discussion, MessageComment $comment)
    {
        $this->discussion = $discussion;
        $this->comment = $comment;
    }

    public function build()
    {
        return $this->view('emails.messages.new-message')
            ->subject('Vous avez un nouveau message de REM DISTRIBUTION');
    }
}
