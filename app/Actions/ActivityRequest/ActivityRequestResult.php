<?php

namespace App\Actions\ActivityRequest;

use App\Models\ActivityRequest;

/**
 * Classe de résultat unifiée pour toutes les opérations sur les demandes d'activité
 * Remplace CreateActivityRequestResult et UpdateActivityRequestResult
 */
class ActivityRequestResult
{
    public function __construct(
        public bool $success,
        public ?ActivityRequest $activityRequest,
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
     * Retourne la demande d'activité
     */
    public function getActivityRequest(): ?ActivityRequest
    {
        return $this->activityRequest;
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
            'activity_request_id' => $this->activityRequest?->id,
            'status' => $this->activityRequest?->status,
        ];
    }
    
    /**
     * Créer un résultat de succès
     */
    public static function success(
        ActivityRequest $activityRequest,
        string $message,
        string $operation = null
    ): self {
        return new self(
            success: true,
            activityRequest: $activityRequest,
            message: $message,
            operation: $operation
        );
    }
    
    /**
     * Créer un résultat d'échec
     */
    public static function failure(
        string $message,
        string $operation = null
    ): self {
        return new self(
            success: false,
            activityRequest: null,
            message: $message,
            operation: $operation
        );
    }
}

