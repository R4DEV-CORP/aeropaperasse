<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
        return $this->role === Role::RemAdmin->value || $this->role === Role::RemSuperAdmin->value;
    }

    public function isSAdmin(): bool
    {
        return $this->role === Role::RemSuperAdmin->value;
    }

    public function isClient(): bool
    {
        return $this->role === Role::Client->value;
    }

    public function isSClient(): bool
    {
        return $this->role === Role::SClient->value;
    }

    public function isAClient(): bool
    {
        return $this->role === Role::AClient->value;
    }

    /**
     * REM-level staff (`admin` / `sadmin`) — cross-tenant access to every tenant.
     * Under the multi-tenant model these short-circuit any per-tenant membership check.
     * See docs/multi-tenant-migration.md (Roles, Auth model).
     */
    public function isRemStaff(): bool
    {
        return $this->isAdmin();
    }

    /**
     * The membership (tenant + pivot role/client_id) this user holds on the given tenant, if any.
     */
    public function membershipFor(string $tenantId): ?Tenant
    {
        return $this->tenants()->where('tenants.id', $tenantId)->first();
    }

    /**
     * Whether this user may access the given tenant: REM staff reach every tenant,
     * otherwise they need a `tenant_user` row for it.
     */
    public function belongsToTenant(string $tenantId): bool
    {
        return $this->isRemStaff() || $this->membershipFor($tenantId) !== null;
    }

    /**
     * Effective role (typed) for a tenant: the REM role short-circuits (REM staff),
     * otherwise the role carried on the `tenant_user` pivot for that tenant.
     * Defaults to the currently initialized tenant when no id is given.
     */
    public function effectiveRole(?string $tenantId = null): ?Role
    {
        $tenantId ??= tenant()?->getTenantKey();

        if ($this->isRemStaff()) {
            return Role::tryFrom((string) $this->role);
        }

        if ($tenantId === null) {
            return null;
        }

        $pivotRole = $this->membershipFor($tenantId)?->pivot->role;

        return $pivotRole === null ? null : Role::tryFrom($pivotRole);
    }

    /**
     * String form of {@see effectiveRole()} for a specific tenant.
     */
    public function effectiveRoleFor(string $tenantId): ?string
    {
        return $this->effectiveRole($tenantId)?->value;
    }

    public function canChangeRequestStatus(): bool
    {
        return $this->isAdmin() || $this->isAClient();
    }

    /**
     * The user lives in the central database, but most of its relations target
     * tenant-scoped models (clients, coworkers, requests…) that must resolve on the
     * active tenant connection — not be forced onto central like Eloquent does by
     * default. Models pinned to central (Tenant, Training) keep their own connection.
     * This is what makes `client()` / `coworker()` / `badgeRequests()` etc. tenant-contextual.
     * See docs/multi-tenant-migration.md (Q-CLIENT, Cross-database references).
     */
    protected function newRelatedInstance($class): Model
    {
        return new $class;
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
