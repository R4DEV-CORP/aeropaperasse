<?php

namespace App\Services\Auth;

use App\Models\User;

class UserRedirectService
{
    /**
     * Retourne le chemin de redirection selon l'utilisateur
     */
    public function getRedirectPath(User $user): string
    {
        // Supprimer les logs de debug pour simplifier
        
        // Si première connexion, changement de mot de passe obligatoire
        if ($user->is_new) {
            return '/change-password';
        }

        // Redirection selon le rôle (identique à AuthController API)
        return match ($user->role) {
            'admin', 'sadmin' => '/dashboard/badge-requests',
            'sclient' => '/dashboard/badge-requests',
            'client' => '/dashboard/badge-requests',
            default => '/dashboard/badge-requests'
        };
    }
}