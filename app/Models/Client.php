<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Client extends Model
{
    use HasFactory;
    use Searchable;

    protected $casts = [
        'is_airline_company' => 'boolean',
    ];

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
        'notification_email',
        'slug',
        'is_airline_company',
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

    public function contacts()
    {
        return $this->hasMany(ContactClient::class);
    }

    public function badges()
    {
        return $this->hasMany(Badge::class);
    }

    public function getActiveBadgeCount(): int
    {
        return Badge::where('status', '!=', 'returned')
            ->whereHas('badgeRequest.activityRequest', function ($query) {
                $query->where('client_id', $this->id);
            })
            ->count();
    }
}
