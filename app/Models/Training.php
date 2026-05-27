<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    /**
     * The training catalog is shared across all tenants — it lives in the
     * central database. Pinned so it keeps resolving to the central connection
     * even when tenancy has switched the default connection to a tenant.
     */
    protected $connection = 'central';

    protected $fillable = [
        'title',
        'requires_airport',
    ];

    protected $casts = [
        'requires_airport' => 'boolean',
    ];

    public function coworkers()
    {
        return $this->belongsToMany(Coworker::class, 'coworker_trainings')
            ->withPivot(['id', 'started_at', 'expires_at', 'certificate_path', 'airport'])
            ->withTimestamps();
    }
}
