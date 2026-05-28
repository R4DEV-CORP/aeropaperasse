<?php

namespace App\Enums;

/**
 * Canonical role values for the multi-tenant model.
 *
 * REM-level roles (rem_super_admin / rem_admin) are cross-tenant and live on
 * `users.role`. Tenant-level roles (owner, tenant_admin, aclient, sclient,
 * client) are scoped to a tenant and carried on the `tenant_user` pivot.
 *
 * See docs/multi-tenant-migration.md (Roles).
 */
enum Role: string
{
    case RemSuperAdmin = 'rem_super_admin';
    case RemAdmin = 'rem_admin';
    case Owner = 'owner';
    case TenantAdmin = 'tenant_admin';
    case AClient = 'aclient';
    case SClient = 'sclient';
    case Client = 'client';

    /**
     * REM staff — cross-tenant access to every tenant, bypassing the membership pivot.
     */
    public function isRemLevel(): bool
    {
        return $this === self::RemSuperAdmin || $this === self::RemAdmin;
    }

    public function label(): string
    {
        return match ($this) {
            self::RemSuperAdmin => 'Super administrateur REM',
            self::RemAdmin => 'Administrateur REM',
            self::Owner => 'Owner',
            self::TenantAdmin => 'Administrateur',
            self::AClient => 'AClient',
            self::SClient => 'Sclient',
            self::Client => 'Client',
        };
    }
}
