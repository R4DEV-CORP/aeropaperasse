<?php

namespace App\Actions\VehiclePass;

use App\Models\VehiclePass;

/**
 * Classe de résultat unifiée pour toutes les opérations sur les laissez-passer véhicule
 */
class VehiclePassResult
{
    public function __construct(
        public bool $success,
        public ?VehiclePass $vehiclePass,
        public string $message,
        public ?string $operation = null // 'create', 'update', 'draft'
    ) {}

    /**
     * Vérifie si l'opération a réussi
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Retourne le message du résultat
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Retourne le laissez-passer véhicule
     */
    public function getVehiclePass(): ?VehiclePass
    {
        return $this->vehiclePass;
    }

    /**
     * Retourne des informations supplémentaires pour la réponse
     */
    public function getData(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'operation' => $this->operation,
            'vehicle_pass_id' => $this->vehiclePass?->id,
            'status' => $this->vehiclePass?->status,
        ];
    }

    /**
     * Créer un résultat de succès
     */
    public static function success(
        VehiclePass $vehiclePass,
        string $message,
        ?string $operation = null
    ): self {
        return new self(
            success: true,
            vehiclePass: $vehiclePass,
            message: $message,
            operation: $operation
        );
    }

    /**
     * Créer un résultat d'échec
     */
    public static function failure(
        string $message,
        ?string $operation = null
    ): self {
        return new self(
            success: false,
            vehiclePass: null,
            message: $message,
            operation: $operation
        );
    }
}
