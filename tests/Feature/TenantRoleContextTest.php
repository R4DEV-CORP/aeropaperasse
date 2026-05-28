<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TenantTestCase;

/**
 * Tenant-level role helpers (isClient/isSClient/isAClient) resolve against the role the
 * user holds on the ACTIVE tenant (the `tenant_user` pivot), falling back to the global
 * `users.role` column when there is no pivot. REM-level helpers (isAdmin/isSAdmin) stay
 * global. TenantTestCase boots and initializes the `test` tenant, so `tenant()` is set.
 * See docs/multi-tenant-migration.md (Q-ROLES).
 */
class TenantRoleContextTest extends TenantTestCase
{
    public function test_tenant_role_helpers_use_the_active_tenant_pivot_role(): void
    {
        // Global column says `client`, but on the active tenant the pivot role is `aclient`.
        $user = User::factory()->create(['role' => 'client']);
        $user->tenants()->attach($this->tenant->getTenantKey(), ['role' => 'aclient']);

        $this->assertTrue($user->isAClient());   // resolved from the pivot
        $this->assertFalse($user->isClient());    // not the global `client`
        $this->assertSame('aclient', $user->contextualRole());
    }

    public function test_tenant_role_helpers_fall_back_to_the_global_role_without_a_pivot(): void
    {
        // No pivot row for the active tenant → fall back to the global column.
        $user = User::factory()->create(['role' => 'sclient']);

        $this->assertTrue($user->isSClient());
        $this->assertFalse($user->isClient());
        $this->assertSame('sclient', $user->contextualRole());
    }

    public function test_rem_staff_helpers_are_global_and_not_a_tenant_client(): void
    {
        $user = User::factory()->create(['role' => 'rem_super_admin']);

        $this->assertTrue($user->isRemStaff());
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isSAdmin());
        $this->assertFalse($user->isClient());
        $this->assertSame('rem_super_admin', $user->contextualRole());
    }
}
