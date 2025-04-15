<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\UserTraining;

class TrainingExpiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $userTraining;
    public $daysRemaining;

    /**
     * Create a new message instance.
     *
     * @param UserTraining $userTraining
     * @param int $daysRemaining
     * @return void
     */
    public function __construct(UserTraining $userTraining, int $daysRemaining)
    {
        $this->userTraining = $userTraining;
        $this->daysRemaining = $daysRemaining;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Notification d\'expiration de votre formation')
            ->view('emails.training-expiry')
            ->with([
                'trainingId' => $this->userTraining->id,
                'nom' => $this->userTraining->user->nom ?? '',
                'prenom' => $this->userTraining->user->prenom ?? '',
                'trainingTitle' => $this->userTraining->training->title ?? 'Formation',
                'expiryDate' => $this->userTraining->expires_at,
                'daysRemaining' => $this->daysRemaining
            ]);
    }
}