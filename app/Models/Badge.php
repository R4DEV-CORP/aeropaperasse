<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Badge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'badge_request_id',
        'client_id',
        'coworker_id',
        'status',
        'previous_status',
        'expiry_date',
        'returned_at',
        'return_document',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'returned_at' => 'datetime',
    ];

    public function badgeRequest(): BelongsTo
    {
        return $this->belongsTo(BadgeRequest::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function coworker(): BelongsTo
    {
        return $this->belongsTo(Coworker::class);
    }

    /**
     * Retourne le client effectif, qu'il soit direct (badge standalone) ou via la demande de badge.
     */
    public function getEffectiveClient(): ?Client
    {
        return $this->client ?? $this->badgeRequest?->activityRequest?->client;
    }

    /**
     * Retourne le coworker effectif, qu'il soit direct (badge standalone) ou via la demande de badge.
     */
    public function getEffectiveCoworker(): ?Coworker
    {
        return $this->coworker ?? $this->badgeRequest?->coworker;
    }

    /**
     * Résout l'email destinataire pour les notifications.
     * Priorité : notification_email client > email coworker > email créateur de la demande.
     */
    public function getRecipientEmail(): ?string
    {
        $client = $this->getEffectiveClient();
        if ($client && ! empty($client->notification_email)) {
            return $client->notification_email;
        }

        $coworker = $this->getEffectiveCoworker();

        return $coworker?->email ?? $this->badgeRequest?->creator?->email;
    }
}
