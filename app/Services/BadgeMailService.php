<?php

namespace App\Services;

use App\Mail\Badge\ApprovedByAdp;
use App\Mail\Badge\ApprovedByRem;
use App\Mail\Badge\Delivered;
use App\Mail\Badge\InProduction;
use App\Mail\Badge\ReadyForPickup;
use App\Mail\Badge\RejectedByAdp;
use App\Mail\Badge\RejectedByRem;
use App\Mail\BadgeCreated;
use App\Mail\BadgeExpired;
use App\Mail\BadgeReturned;
use App\Mail\BadgeStatusUpdated;
use App\Models\Badge;
use App\Models\BadgeRequest;
use Illuminate\Support\Facades\Mail;

class BadgeMailService
{
    /**
     * Envoie un email en fonction du changement de statut d'une demande de badge
     */
    public function sendBadgeRequestStatusMail(BadgeRequest $badgeRequest, ?string $previousStatus = null): void
    {
        $recipientEmail = $this->getRecipientEmailFromBadgeRequest($badgeRequest);
        if (! $recipientEmail) {
            return;
        }

        switch ($badgeRequest->status) {
            case 'approved_by_rem':
                Mail::to($recipientEmail)->send(new ApprovedByRem($badgeRequest));
                break;

            case 'rejected_by_rem':
                Mail::to($recipientEmail)->send(new RejectedByRem($badgeRequest, $badgeRequest->rejection_reason));
                break;

            case 'approved_by_adp':
                Mail::to($recipientEmail)->send(new ApprovedByAdp($badgeRequest));
                break;

            case 'rejected_by_adp':
                Mail::to($recipientEmail)->send(new RejectedByAdp($badgeRequest));
                break;

            case 'in_production':
                Mail::to($recipientEmail)->send(new InProduction($badgeRequest));
                break;

            case 'ready_for_delivery':
                Mail::to($recipientEmail)->send(new ReadyForPickup($badgeRequest));
                break;

            case 'delivered':
                Mail::to($recipientEmail)->send(new Delivered($badgeRequest));
                break;
        }
    }

    /**
     * Envoie un email lors de la création d'un badge
     */
    public function sendBadgeCreatedMail(Badge $badge): void
    {
        $recipientEmail = $badge->getRecipientEmail();

        if (! $recipientEmail) {
            return;
        }

        Mail::to($recipientEmail)->send(new BadgeCreated($badge));
    }

    /**
     * Envoie un email lors d'un changement de statut d'un badge
     */
    public function sendBadgeStatusUpdatedMail(Badge $badge, string $previousStatus): void
    {
        $recipientEmail = $badge->getRecipientEmail();

        if (! $recipientEmail) {
            return;
        }

        switch ($badge->status) {
            case 'active':
                // Si le badge vient d'être activé, pas besoin d'envoyer un autre email
                // car on a déjà envoyé le mail de création
                break;

            case 'expired':
                Mail::to($recipientEmail)->send(new BadgeExpired($badge));
                break;

            case 'returned':
                Mail::to($recipientEmail)->send(new BadgeReturned($badge));
                break;

            default:
                Mail::to($recipientEmail)->send(new BadgeStatusUpdated($badge, $previousStatus));
                break;
        }
    }

    /**
     * Détermine l'email destinataire depuis une BadgeRequest.
     * Priorité : notification_email client > email créateur.
     */
    private function getRecipientEmailFromBadgeRequest(BadgeRequest $badgeRequest): ?string
    {
        if ($badgeRequest->activityRequest->client && ! empty($badgeRequest->activityRequest->client->notification_email)) {
            return $badgeRequest->activityRequest->client->notification_email;
        }

        return $badgeRequest->creator->email;
    }
}
