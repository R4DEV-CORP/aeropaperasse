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
                'activity_request' => $this->activityRequest,
                'company_name' => $this->activityRequest->client->company_name,
                'trade_name' => $this->activityRequest->client->trade_name,
                'siret_number' => $this->activityRequest->client->siret_number,
            ]);
    }
}
