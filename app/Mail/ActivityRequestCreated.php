<?php

namespace App\Mail;

use App\Models\ActivityRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivityRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $activityRequest;

    public function __construct(ActivityRequest $activityRequest)
    {
        $this->activityRequest = $activityRequest;
    }

    public function build()
    {
        return $this->view('emails.activity-request.created')
                    ->subject('Nouvelle demande d\'activité reçue')
                    ->with([
                        'raison_sociale' => $this->activityRequest->raison_sociale,
                        'nom_commercial' => $this->activityRequest->nom_commercial,
                        'responsable_nom' => $this->activityRequest->responsable_nom,
                        'responsable_prenom' => $this->activityRequest->responsable_prenom,
                    ]);
    }
}