<?php

namespace App\Mail;

use App\Models\VehiclePass;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VehiclePassCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $vehiclePass;
    public $isAdminNotification;

    /**
     * Create a new message instance.
     *
     * @param VehiclePass $vehiclePass
     * @param bool $isAdminNotification Indique si c'est une notification pour l'admin
     * @return void
     */
    public function __construct(VehiclePass $vehiclePass, $isAdminNotification = false)
    {
        $this->vehiclePass = $vehiclePass;
        $this->isAdminNotification = $isAdminNotification;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->isAdminNotification) {
            $subject = "Nouvelle demande de laisser-passer véhicule reçue - " . $this->vehiclePass->immatriculation;
            return $this->subject($subject)
                        ->view('emails.vehicle-pass.created-admin')
                        ->with([
                            'immatriculation' => $this->vehiclePass->immatriculation,
                            'nom_entreprise' => $this->vehiclePass->nom_entreprise,
                            'aeroport' => $this->vehiclePass->aeroport,
                            'marque_vehicule' => $this->vehiclePass->marque_vehicule,
                            'user_name' => $this->vehiclePass->user->name,
                            'user_email' => $this->vehiclePass->user->email,
                        ]);
        }

        return $this->view('emails.vehicle-pass.created')
                    ->subject('Confirmation de votre demande de laisser-passer véhicule')
                    ->with([
                        'immatriculation' => $this->vehiclePass->immatriculation,
                        'nom_entreprise' => $this->vehiclePass->nom_entreprise,
                        'aeroport' => $this->vehiclePass->aeroport,
                        'marque_vehicule' => $this->vehiclePass->marque_vehicule,
                    ]);
    }
}
