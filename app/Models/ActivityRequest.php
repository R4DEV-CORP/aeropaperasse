<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityRequest extends Model
{
    use HasFactory;

    protected $fillable = [
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
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}