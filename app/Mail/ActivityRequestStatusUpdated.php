<?php

namespace App\Mail;

use App\Models\ActivityRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivityRequestStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $activityRequest;
    public $isAdminNotification;

    /**
     * Create a new message instance.
     *
     * @param ActivityRequest $activityRequest
     * @param bool $isAdminNotification Indique si c'est une notification pour l'admin
     * @return void
     */
    public function __construct(ActivityRequest $activityRequest, $isAdminNotification = false)
    {
        $this->activityRequest = $activityRequest;
        $this->isAdminNotification = $isAdminNotification;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "Mise à jour de votre demande d'activité - " . $this->getStatusLabel();
        
        // if ($this->isAdminNotification) {
        //     $subject = "Statut mis à jour - Demande d'activité #" . $this->activityRequest->id;
        //     return $this->subject($subject)
        //                 ->view('emails.activity-request-status-admin');
        // }
        
        return $this->subject($subject)
                    ->view('emails.activity-request.activity-request-status-updated');
    }
    
    /**
     * Obtient le libellé du statut
     *
     * @return string
     */
    private function getStatusLabel()
    {
        switch ($this->activityRequest->status) {
            case 'approved':
                return 'Approuvée';
            case 'rejected':
                return 'Rejetée';
            case 'pending':
                return 'En attente';
            default:
                return ucfirst($this->activityRequest->status);
        }
    }
}