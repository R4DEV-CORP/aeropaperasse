<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests for the tenant-contextual authorization (central directory + `tenant_user` pivot).
 * These exercise the membership/role resolution that EnsureTenantMembership relies on, against
 * the central database only (no tenant DBs needed). See docs/multi-tenant-migration.md (Auth model).
 */
class TenantMembershipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a tenant registry row without firing the create-database/migrate pipeline —
     * these tests only need the central `tenants` rows, not real tenant databases.
     */
    private function makeTenant(string $id): Tenant
    {
        return Tenant::withoutEvents(fn (): Tenant => Tenant::create(['id' => $id]));
    }

    public function test_rem_staff_reach_any_tenant_without_a_pivot_row(): void
    {
        $this->makeTenant('rem');
        $this->makeTenant('c1');

        $rem = User::factory()->create(['role' => 'sadmin']);

        $this->assertTrue($rem->isRemStaff());
        $this->assertTrue($rem->belongsToTenant('rem'));
        $this->assertTrue($rem->belongsToTenant('c1'));
        $this->assertSame('sadmin', $rem->effectiveRoleFor('rem'));
        $this->assertSame('sadmin', $rem->effectiveRoleFor('c1'));
        $this->assertCount(0, $rem->tenants);
    }

    public function test_a_user_is_limited_to_the_tenant_carried_on_its_pivot(): void
    {
        $this->makeTenant('rem');
        $c1 = $this->makeTenant('c1');

        $owner = User::factory()->create(['role' => 'client']);
        $owner->tenants()->attach($c1->getTenantKey(), ['role' => 'owner']);

        $this->assertFalse($owner->isRemStaff());
        $this->assertTrue($owner->belongsToTenant('c1'));
        $this->assertFalse($owner->belongsToTenant('rem'));
        $this->assertSame('owner', $owner->effectiveRoleFor('c1'));
        $this->assertNull($owner->effectiveRoleFor('rem'));
    }

    public function test_effective_role_is_resolved_per_tenant(): void
    {
        $rem = $this->makeTenant('rem');
        $c1 = $this->makeTenant('c1');

        $user = User::factory()->create(['role' => 'client']);
        $user->tenants()->attach($rem->getTenantKey(), ['role' => 'sclient']);
        $user->tenants()->attach($c1->getTenantKey(), ['role' => 'owner']);

        $this->assertSame('sclient', $user->effectiveRoleFor('rem'));
        $this->assertSame('owner', $user->effectiveRoleFor('c1'));
    }
}
