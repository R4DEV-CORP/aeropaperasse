<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Stancl\Tenancy\Database\Models\Tenant;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Users live in the central database — a single directory shared across all
     * tenants. Pinned explicitly so the model keeps resolving to the central
     * connection even when tenancy has switched the default connection to a tenant.
     */
    protected $connection = 'central';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_new',
        'role',
        'client_id',
        'coworker_id',
        'function',
        'two_factor_enabled',
        'can_access_formation',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'password' => 'hashed',
        'is_new' => 'boolean',
        'can_access_formation' => 'boolean',
    ];

    public function isAdmin(): bool
    {
        // return $this->role === 'admin'; // ou votre logique d'admin
        return $this->role === 'admin' || $this->role === 'sadmin';
    }

    public function isSAdmin(): bool
    {
        return $this->role === 'sadmin';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isSClient(): bool
    {
        return $this->role === 'sclient';
    }

    public function isAClient(): bool
    {
        return $this->role === 'aclient';
    }

    public function canChangeRequestStatus(): bool
    {
        return $this->isAdmin() || $this->isAClient();
    }

    public function badgeRequests()
    {
        return $this->hasMany(BadgeRequest::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function coworker()
    {
        return $this->hasOne(Coworker::class);
    }

    /**
     * Tenants this user belongs to, with the tenant-scoped role and (optionally)
     * the company (`client_id`) they map to within that tenant — carried on the
     * central `tenant_user` pivot. See docs/multi-tenant-migration.md.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot(['role', 'client_id'])
            ->withTimestamps();
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'created_by');
    }

    public function trainings()
    {
        return $this->belongsToMany(Training::class, 'user_trainings')
            ->withPivot(['id', 'started_at', 'expires_at', 'certificate_path'])
            ->withTimestamps();
    }

    public function vehiclePasses()
    {
        return $this->hasMany(VehiclePass::class, 'created_by');
    }
}
