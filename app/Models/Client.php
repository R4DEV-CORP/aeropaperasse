<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'referent_name',
        'referent_email',
        'safety_referent_name_1', 'safety_referent_email_1', 'safety_referent_phone_1',
        'safety_referent_name_2', 'safety_referent_email_2', 'safety_referent_phone_2',
        'safety_referent_name_3', 'safety_referent_email_3', 'safety_referent_phone_3',
        'safety_document',
        'security_correspondent_name', 'security_correspondent_email', 'security_correspondent_phone',
        'security_document',
        'kbis_document',
        'hr_contact_name', 'hr_contact_email', 'hr_contact_phone',
        'notification_email',
        'badge_limit',
        'vehicle_pass_limit',
        'raison_sociale',
        'nom_commercial',
        'siret',
        'adresse',
        'code_postal',
        'ville',
        'responsable_nom',
        'responsable_prenom',
        'responsable_email',
        'responsable_telephone',
        'responsable_fonction',
        'activite_description',
        'sous_traitant_de',
        'numero_identification',
        'safety_referent_prenom_1',
        'safety_referent_prenom_2',
        'safety_referent_prenom_3',
        'security_correspondent_prenom',
        'hr_contact_prenom',
        'aeroports_concernes',
        'zones_concernees',
        'nombre_demandes_activite',
        'numeros_demandes_activite',
        'date_debut_validite',
        'date_fin_validite',
        'nombre_badges_actifs',
        'nombre_vehicules_actifs'
    ];

    protected $casts = [
        'badge_limit' => 'integer',
        'vehicle_pass_limit' => 'integer',
        'aeroports_concernes' => 'array',
        'zones_concernees' => 'array',
        'date_debut_validite' => 'date',
        'date_fin_validite' => 'date',
    ];

    protected $appends = ['active_badges_count', 'active_vehicle_passes_count'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function badgeRequests()
    {
        return $this->hasMany(BadgeRequest::class);
    }

    public function vehiclePasses()
    {
        return $this->hasManyThrough(VehiclePass::class, User::class);
    }

    public function getActiveBadgesCountAttribute(): int
    {
        return $this->badgeRequests()
            ->where('status', 'ready_for_delivery')
            ->count();
    }

    public function getActiveVehiclePassesCountAttribute(): int
    {
        return $this->vehiclePasses()
            ->where('status', 'approved')
            ->count();
    }

    public function canCreateBadge(): bool
    {
        return $this->active_badges_count < $this->badge_limit;
    }

    public function canCreateVehiclePass(): bool
    {
        if ($this->vehicle_pass_limit == 0) {
            return false;
        }

        return $this->active_vehicle_passes_count < $this->vehicle_pass_limit;
    }
}
