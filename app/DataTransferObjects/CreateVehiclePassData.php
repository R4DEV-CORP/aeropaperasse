<?php

namespace App\DataTransferObjects;

use Illuminate\Http\UploadedFile;

class CreateVehiclePassData
{
    public function __construct(
        // Informations du véhicule
        public ?string $plate_number,
        public ?string $car_brand,
        public ?string $airport,

        // Documents
        public ?UploadedFile $certificate_of_registration,
        public ?UploadedFile $company_stamp,

        // Metadata
        public int $client_id,
        public int $created_by,
        public ?int $activity_request_id = null,
    ) {}

    /**
     * Créer le DTO à partir d'un array
     */
    public static function fromArray(array $data, int $clientId, int $userId): self
    {
        return new self(
            plate_number: $data['plate_number'] ?? null,
            car_brand: $data['car_brand'] ?? null,
            airport: $data['airport'] ?? null,
            certificate_of_registration: $data['certificate_of_registration'] ?? null,
            company_stamp: $data['company_stamp'] ?? null,
            client_id: $clientId,
            created_by: $userId,
            activity_request_id: $data['activity_request_id'] ?? null,
        );
    }

    /**
     * Retourne les données du laissez-passer véhicule pour la base de données
     */
    public function getVehiclePassData(): array
    {
        $data = [
            'client_id' => $this->client_id,
            'created_by' => $this->created_by,
            'activity_request_id' => $this->activity_request_id,
            'plate_number' => $this->plate_number,
            'car_brand' => $this->car_brand,
            'airport' => $this->airport,
            'status' => 'pending',
            'pending_at' => now(),
        ];

        // Filtrer uniquement les valeurs null (garder false et 0)
        return array_filter($data, function ($value) {
            return ! is_null($value);
        });
    }

    /**
     * Retourne les documents à enregistrer
     */
    public function getDocuments(): array
    {
        $documents = [];

        if ($this->certificate_of_registration) {
            $documents['certificate_of_registration'] = $this->certificate_of_registration;
        }
        if ($this->company_stamp) {
            $documents['company_stamp'] = $this->company_stamp;
        }

        return $documents;
    }

    /**
     * Vérifie si des documents sont présents
     */
    public function hasDocuments(): bool
    {
        return ! empty($this->getDocuments());
    }
}
