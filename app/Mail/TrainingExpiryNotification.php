<?php

namespace App\Mail;

use App\Models\CoworkerTraining;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrainingExpiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CoworkerTraining $coworkerTraining, public int $daysRemaining) {}

    public function build(): self
    {
        return $this->subject('Notification d\'expiration de votre formation')
            ->view('emails.training-expiry')
            ->with([
                'trainingId' => $this->coworkerTraining->id,
                'nom' => $this->coworkerTraining->coworker->lastname ?? '',
                'prenom' => $this->coworkerTraining->coworker->firstname ?? '',
                'trainingTitle' => $this->coworkerTraining->training->title ?? 'Formation',
                'expiryDate' => $this->coworkerTraining->expires_at,
                'daysRemaining' => $this->daysRemaining,
            ]);
    }
}
