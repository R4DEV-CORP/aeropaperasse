<?php

namespace App\Observers;

use App\Models\Badge;
use App\Services\BadgeMailService;

class BadgeObserver
{
    protected $badgeMailService;

    public function __construct(BadgeMailService $badgeMailService)
    {
        $this->badgeMailService = $badgeMailService;
    }

    /**
     * Handle the Badge "created" event.
     */
    public function created(Badge $badge): void
    {
        // Envoyer l'email de création de badge
        $this->badgeMailService->sendBadgeCreatedMail($badge);
    }

    /**
     * Handle the Badge "updating" event.
     * 
     * Cette méthode est appelée avant la mise à jour.
     * Nous sauvegardons l'ancien statut pour le comparer après la mise à jour.
     */
    public function updating(Badge $badge): void
    {
        // Stocker temporairement l'ancien statut
        $badge->previous_status = $badge->getOriginal('status');
    }

    /**
     * Handle the Badge "updated" event.
     * 
     * Cette méthode est appelée après la mise à jour.
     */
    public function updated(Badge $badge): void
    {
        // Vérifier si le statut a changé
        if (isset($badge->previous_status) && $badge->previous_status !== $badge->status) {
            // Envoyer l'email de changement de statut
            $this->badgeMailService->sendBadgeStatusUpdatedMail($badge, $badge->previous_status);
        }
    }
}

