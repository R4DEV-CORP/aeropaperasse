<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'renouvellement',
        'autorisation_anterieur',
        'raison_sociale',
        'nom_commercial',
        'siret',
        'adresse',
        'responsable_nom',
        'responsable_prenom',
        'responsable_email',
        'responsable_telephone',
        'responsable_fonction',
        'activite_description',
        'nombre_personnes',
        'nombre_vehicules',
        'clients_denomination',
        'extrait_kbis_path',
        'attestations_clients_path',
        'formulaire_surete_path',
        'agrement_prefectoral_path',
        'contrat_iata_path',
        'cta_path',
        'status',
        'previous_status',
        'draft_at',
        'created_by',
        'pending_at',
        'approved_at',
        'rejected_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}