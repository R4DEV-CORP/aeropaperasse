<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'customer_certificate_document', // attestation client
        'prefectural_agreement_document', // agrément préfectoral
        'iata_contract_document', // contrat IATA
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
}
