<?php

namespace App\Policies;

use App\Models\BadgeRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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
}