<?php

namespace App\Actions\BadgeRequest;

use App\Models\BadgeRequest;

/**
 * Classe de résultat unifiée pour toutes les opérations sur les demandes de badge
 */
class BadgeRequestResult
{
    public function __construct(
        public bool $success,
        public ?BadgeRequest $badgeRequest,
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
     * Retourne la demande de badge
     */
    public function getBadgeRequest(): ?BadgeRequest
    {
        return $this->badgeRequest;
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
            'badge_request_id' => $this->badgeRequest?->id,
            'status' => $this->badgeRequest?->status,
        ];
    }

    /**
     * Créer un résultat de succès
     */
    public static function success(
        BadgeRequest $badgeRequest,
        string $message,
        ?string $operation = null
    ): self {
        return new self(
            success: true,
            badgeRequest: $badgeRequest,
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
            badgeRequest: null,
            message: $message,
            operation: $operation
        );
    }
}

