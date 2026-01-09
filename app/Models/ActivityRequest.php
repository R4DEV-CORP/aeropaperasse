<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class ActivityRequest extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'client_id',
        'created_by',
        'airport',
        'renewal',
        'last_activity_request_id',
        'manager_firstname',
        'manager_lastname',
        'manager_email',
        'manager_phone',
        'manager_role',
        'description',
        'person_count',
        'vehicule_count',
        'customer_names', // dénomination des clients
        'aao_request_document', // attestation client
        'kbis_document', // KBIS
        'term_document', // Mandat
        'safety_referent_document', // Responsable de la sureté
        'security_referent_document', // Responsable de la sécurité
        'cta_document', // CTA
        'status',
        'previous_status',
        'draft_at',
        'pending_at',
        'approved_at',
        'rejected_at',
        'reject_reason',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'manager_firstname' => $this->manager_firstname,
            'manager_lastname' => $this->manager_lastname,
            'manager_email' => $this->manager_email,
            'clients.company_name' => $this->client->company_name ?? '',
            'clients.trade_name' => $this->client->trade_name ?? '',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ActivityRequestAttachment::class);
    }

    /**
     * Récupère le document AAO Request (unique)
     */
    public function getAaoRequestDocument(): ?ActivityRequestAttachment
    {
        return $this->attachments()
            ->ofType(ActivityRequestAttachment::TYPE_AAO_REQUEST)
            ->first();
    }

    /**
     * Récupère le document KBIS (unique)
     */
    public function getKbisDocument(): ?ActivityRequestAttachment
    {
        return $this->attachments()
            ->ofType(ActivityRequestAttachment::TYPE_KBIS)
            ->first();
    }

    /**
     * Récupère tous les documents Principals (peut être multiple)
     */
    public function getPrincipalsDocuments(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->attachments()
            ->ofType(ActivityRequestAttachment::TYPE_PRINCIPALS)
            ->get();
    }

    /**
     * Récupère le document Safety Referent (unique)
     */
    public function getSafetyReferentDocument(): ?ActivityRequestAttachment
    {
        return $this->attachments()
            ->ofType(ActivityRequestAttachment::TYPE_SAFETY_REFERENT)
            ->first();
    }

    /**
     * Récupère le document Security Referent (unique)
     */
    public function getSecurityReferentDocument(): ?ActivityRequestAttachment
    {
        return $this->attachments()
            ->ofType(ActivityRequestAttachment::TYPE_SECURITY_REFERENT)
            ->first();
    }

    /**
     * Récupère le document CTA (unique)
     */
    public function getCtaDocument(): ?ActivityRequestAttachment
    {
        return $this->attachments()
            ->ofType(ActivityRequestAttachment::TYPE_CTA)
            ->first();
    }

    /**
     * Vérifie si un document d'un type donné existe
     */
    public function hasDocument(string $type): bool
    {
        return $this->attachments()
            ->ofType($type)
            ->exists();
    }

    /**
     * Récupère un document par son type (pour les types uniques)
     */
    public function getDocumentByType(string $type): ?ActivityRequestAttachment
    {
        return $this->attachments()
            ->ofType($type)
            ->first();
    }
}
