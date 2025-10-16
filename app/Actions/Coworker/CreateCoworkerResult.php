<?php

namespace App\Actions\Coworker;

use App\Models\Coworker;
use App\Models\User;

/**
 * Classe de résultat pour la création d'un collaborateur
 */
class CreateCoworkerResult
{
    public function __construct(
        public bool $success,
        public ?Coworker $coworker,
        public ?User $user,
        public string $message,
        public string $operation = 'create'
    ) {}

    /**
     * Créer un résultat de succès
     */
    public static function success(Coworker $coworker, ?User $user, string $message): self
    {
        return new self(
            success: true,
            coworker: $coworker,
            user: $user,
            message: $message,
            operation: 'create'
        );
    }

    /**
     * Créer un résultat d'échec
     */
    public static function failure(string $message): self
    {
        return new self(
            success: false,
            coworker: null,
            user: null,
            message: $message,
            operation: 'create'
        );
    }

    /**
     * Vérifie si l'action a été réussie
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Retourne le message de l'action
     */
    public function getMessage(): string
    {
        return $this->message;
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
            'coworker_id' => $this->coworker?->id,
            'user_id' => $this->user?->id,
            'coworker_name' => $this->coworker ? 
                $this->coworker->firstname . ' ' . $this->coworker->lastname : null,
            'coworker_email' => $this->coworker?->email,
            'user_created' => !is_null($this->user),
        ];
    }

    /**
     * Retourne les données du collaborateur créé
     */
    public function getCoworkerData(): ?array
    {
        if (!$this->coworker) {
            return null;
        }

        return [
            'id' => $this->coworker->id,
            'firstname' => $this->coworker->firstname,
            'lastname' => $this->coworker->lastname,
            'email' => $this->coworker->email,
            'phone' => $this->coworker->phone,
            'client_id' => $this->coworker->client_id,
            'can_access_formation' => $this->coworker->can_access_formation,
            'has_leave' => $this->coworker->has_leave,
            'departure_date' => $this->coworker->departure_date,
            'user_id' => $this->coworker->user_id,
            'created_at' => $this->coworker->created_at,
        ];
    }

    /**
     * Retourne les données de l'utilisateur créé
     */
    public function getUserData(): ?array
    {
        if (!$this->user) {
            return null;
        }

        return [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'role' => $this->user->role,
            'is_new' => $this->user->is_new,
            'client_id' => $this->user->client_id,
            'coworker_id' => $this->user->coworker_id,
            'created_at' => $this->user->created_at,
        ];
    }
}
