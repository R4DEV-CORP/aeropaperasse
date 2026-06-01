<?php

namespace App\Enums;

use App\Models\User;

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

    /**
     * Roles the given actor may attribute when creating/updating an account on the
     * **active tenant**. Hierarchy (from least to most privileged tenant-side):
     * client < sclient < aclient < tenant_admin < owner. REM-level roles can only be
     * granted by REM staff. Used to gate both the UI (dropdowns) and the server-side
     * validation (in: rule), so the two stay in sync.
     *
     * @return list<self>
     */
    public static function assignableBy(User $actor): array
    {
        if ($actor->isSAdmin()) {
            return [self::RemSuperAdmin, self::RemAdmin, self::Owner, self::TenantAdmin, self::AClient, self::SClient, self::Client];
        }

        if ($actor->isRemStaff()) {
            return [self::RemAdmin, self::Owner, self::TenantAdmin, self::AClient, self::SClient, self::Client];
        }

        $contextual = $actor->effectiveRole();

        if ($contextual === self::Owner) {
            return [self::TenantAdmin, self::AClient, self::SClient, self::Client];
        }

        if ($contextual === self::TenantAdmin) {
            return [self::AClient, self::SClient, self::Client];
        }

        return [];
    }

    /**
     * Convenience: string values of {@see assignableBy()}, useful for `in:` rules.
     *
     * @return list<string>
     */
    public static function assignableValuesBy(User $actor): array
    {
        return array_map(fn (self $r): string => $r->value, self::assignableBy($actor));
    }
}
