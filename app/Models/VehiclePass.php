<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclePass extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nom_entreprise',
        'siret',
        'adresse',
        'code_postal',
        'ville',
        'tampon_entreprise',
        'aeroport',
        'immatriculation',
        'marque_vehicule',
        'carte_grise_path',
        'status',
        'previous_status',
        'draft_at',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'aeroport' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
