<?php

namespace App\Observers;

use App\Models\BadgeRequest;
use App\Services\BadgeMailService;
use App\Services\BadgeRequestMailService;

class BadgeRequestObserver
{
    protected $badgeMailService;
    protected $badgeRequestMailService;

    public function __construct(
        BadgeMailService $badgeMailService,
        BadgeRequestMailService $badgeRequestMailService
    ) {
        $this->badgeMailService = $badgeMailService;
        $this->badgeRequestMailService = $badgeRequestMailService;
    }

    /**
     * Handle the BadgeRequest "created" event.
     */
    public function created(BadgeRequest $badgeRequest): void
    {
        // Envoyer l'email de création de demande de badge
        $this->badgeRequestMailService->sendCreatedMail($badgeRequest);
    }
    /**
     * Handle the BadgeRequest "updating" event.
     * 
     * Cette méthode est appelée avant la mise à jour.
     * Nous sauvegardons l'ancien statut pour le comparer après la mise à jour.
     */
    public function updating(BadgeRequest $badgeRequest): void
    {
        // Stocker temporairement l'ancien statut
        $badgeRequest->previous_status = $badgeRequest->getOriginal('status');
    }

    /**
     * Handle the BadgeRequest "updated" event.
     * 
     * Cette méthode est appelée après la mise à jour.
     */
    public function updated(BadgeRequest $badgeRequest): void
    {
        // Vérifier si le statut a changé
        if (isset($badgeRequest->previous_status) && $badgeRequest->previous_status !== $badgeRequest->status) {
            // Envoyer l'email de changement de statut
            $this->badgeRequestMailService->sendStatusUpdateMail($badgeRequest, $badgeRequest->previous_status);
        }
    }
}