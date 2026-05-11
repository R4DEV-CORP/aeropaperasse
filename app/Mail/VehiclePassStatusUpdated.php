<?php

namespace App\Mail;

use App\Models\VehiclePass;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VehiclePassStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $vehiclePass;

    public $previousStatus;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(VehiclePass $vehiclePass, ?string $previousStatus = null)
    {
        $this->vehiclePass = $vehiclePass;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Mise à jour de votre demande de laisser-passer véhicule - '.$this->getStatusLabel();

        return $this->view('emails.vehicle-pass.status-updated')
            ->subject($subject)
            ->with([
                'immatriculation' => $this->vehiclePass->plate_number,
                'nom_entreprise' => $this->vehiclePass->client?->company_name,
                'aeroport' => $this->vehiclePass->airport,
                'marque_vehicule' => $this->vehiclePass->car_brand,
                'previous_status' => $this->previousStatus,
                'current_status' => $this->vehiclePass->status,
                'status_label' => $this->getStatusLabel(),
                'approved_at' => $this->vehiclePass->approved_at,
                'rejected_at' => $this->vehiclePass->rejected_at,
            ]);
    }

    /**
     * Obtient le libellé du statut
     *
     * @return string
     */
    private function getStatusLabel()
    {
        switch ($this->vehiclePass->status) {
            case 'approved':
                return 'Approuvée';
            case 'rejected':
                return 'Rejetée';
            case 'pending':
                return 'En attente';
            default:
                return ucfirst($this->vehiclePass->status);
        }
    }
}
