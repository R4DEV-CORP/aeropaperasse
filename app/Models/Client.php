<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name', // raison sociale
        'trade_name', // nom commercial
        'siret_number',
        'address',
        'zip_code',
        'city',
        'subcontractor_of', // Sous traitant de quelles entreprises
        'kbis_document',
        'safety_document',
        'security_document',
        'badge_limit',
        'vehicle_pass_limit',
        'notification_email',
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

    public function activityRequests()
    {
        return $this->hasMany(ActivityRequest::class);
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

    public function contacts()
    {
        return $this->hasMany(ContactClient::class);
    }
}
