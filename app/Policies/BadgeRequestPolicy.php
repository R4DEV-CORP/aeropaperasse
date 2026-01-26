<?php

namespace App\Policies;

use App\Models\BadgeRequest;
use App\Models\User;

class BadgeRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Tout utilisateur connecté peut voir la liste (filtrée par scope)
    }

    public function view(User $user, BadgeRequest $badgeRequest): bool
    {
        return $user->id === $badgeRequest->user_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true; // Tout utilisateur peut créer une demande
    }

    public function update(User $user, BadgeRequest $badgeRequest): bool
    {
        return $user->isAdmin(); // Seul l'admin peut mettre à jour le statut
    }

    /**
     * Détermine si l'utilisateur peut marquer un badge comme remis
     */
    public function deliver(User $user, BadgeRequest $badgeRequest): bool
    {
        // Seuls les admins peuvent marquer un badge comme remis
        if (! $user->isAdmin()) {
            return false;
        }

        // Le badge doit être en statut "ready_for_delivery"
        if (! $badgeRequest->canBeDelivered()) {
            return false;
        }

        // Le badge ne doit pas déjà être remis
        if ($badgeRequest->isDelivered()) {
            return false;
        }

        return true;
    }
}
