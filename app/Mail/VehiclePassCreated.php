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
     * @param  bool  $isAdminNotification  Indique si c'est une notification pour l'admin
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
        $immatriculation = $this->vehiclePass->plate_number;
        $nomEntreprise = $this->vehiclePass->client?->company_name;
        $aeroport = $this->vehiclePass->airport;
        $marqueVehicule = $this->vehiclePass->car_brand;
        $creator = $this->vehiclePass->createdBy;

        if ($this->isAdminNotification) {
            $subject = 'Nouvelle demande de laisser-passer véhicule reçue - '.$immatriculation;

            return $this->subject($subject)
                ->view('emails.vehicle-pass.created-admin')
                ->with([
                    'immatriculation' => $immatriculation,
                    'nom_entreprise' => $nomEntreprise,
                    'aeroport' => $aeroport,
                    'marque_vehicule' => $marqueVehicule,
                    'user_name' => $creator?->name,
                    'user_email' => $creator?->email,
                ]);
        }

        return $this->view('emails.vehicle-pass.created')
            ->subject('Confirmation de votre demande de laisser-passer véhicule')
            ->with([
                'immatriculation' => $immatriculation,
                'nom_entreprise' => $nomEntreprise,
                'aeroport' => $aeroport,
                'marque_vehicule' => $marqueVehicule,
            ]);
    }
}
