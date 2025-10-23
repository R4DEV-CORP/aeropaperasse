<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use App\Models\Badge;

class Client extends Model
{
    use HasFactory;
    use Searchable;

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
        'slug',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'company_name' => $this->company_name,
            'trade_name' => $this->trade_name,
            'siret_number' => $this->siret_number,
        ];
    }

    protected $appends = ['active_badges_count', 'active_vehicle_passes_count'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function coworkers()
    {
        return $this->hasMany(Coworker::class);
    }

    public function badgeRequests()
    {
        return $this->hasManyThrough(BadgeRequest::class, ActivityRequest::class);
    }

    public function activityRequests()
    {
        return $this->hasMany(ActivityRequest::class);
    }

    public function trainings()
    {
        return $this->belongsToMany(Training::class, Coworker::class)->withPivot(['started_at', 'expires_at', 'certificate_path', 'validity_years']);
    }

    public function vehiclePasses()
    {
        return $this->hasMany(VehiclePass::class);
    }

    public function getActiveBadgesCountAttribute(): int
    {
        return $this->badgeRequests()
            ->where('badge_requests.status', 'ready_for_delivery')
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

    public function badges()
    {
        return $this->hasMany(Badge::class);
    }

    public function getActiveBadgeCount() : int
    {
        return Badge::where('status', '!=', 'returned')
            ->whereHas('badgeRequest.activityRequest', function ($query) {
                $query->where('client_id', $this->id);
            })
            ->count();
    }
}
