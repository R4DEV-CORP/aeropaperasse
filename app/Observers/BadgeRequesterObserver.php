<?php

namespace App\Observers;

use App\Models\BadgeRequest;
use App\Services\BadgeRequestMailService;

class BadgeRequesterObserver
{
    protected $badgeRequestMailService;

    private $originalStatus;

    public function __construct(BadgeRequestMailService $badgeRequestMailService)
    {
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
     */
    public function updating(BadgeRequest $badgeRequest): void
    {
        // Stocker l'ancien statut
        $this->originalStatus = $badgeRequest->getOriginal('status');
    }

    /**
     * Handle the BadgeRequest "updated" event.
     */
    public function updated(BadgeRequest $badgeRequest): void
    {
        // Vérifier si le statut a changé
        if (isset($this->originalStatus) && $this->originalStatus !== $badgeRequest->status) {
            // Envoyer l'email de changement de statut
            $this->badgeRequestMailService->sendStatusUpdateMail($badgeRequest, $this->originalStatus);
        }
    }
}
