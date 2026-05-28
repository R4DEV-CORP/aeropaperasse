<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Coworker;
use App\Models\User;
use Tests\TenantTestCase;

/**
 * The coworkers index must render under tenancy (it filters tenant coworkers by their
 * central `user` via id-lists, not a cross-DB whereHas) and the role badge reflects the
 * role on the ACTIVE tenant (contextualRole()). See docs/multi-tenant-migration.md (Q-ROLES,
 * cross-database references).
 */
class CoworkerRoleDisplayTest extends TenantTestCase
{
    private function makeCoworkerWithUser(string $globalRole, ?string $pivotRole): Coworker
    {
        $client = Client::factory()->create();
        $user = User::factory()->create(['role' => $globalRole]);

        if ($pivotRole !== null) {
            $user->tenants()->attach($this->tenant->getTenantKey(), ['role' => $pivotRole]);
        }

        return Coworker::factory()->create(['client_id' => $client->id, 'user_id' => $user->id]);
    }

    public function test_index_renders_and_badge_uses_the_active_tenant_role(): void
    {
        // Global column `client`, but pivot role on this tenant is `aclient`.
        $this->makeCoworkerWithUser('client', 'aclient');

        $admin = User::factory()->create(['role' => 'rem_super_admin']);

        $this->actingAs($admin)
            ->get('http://'.self::TENANT_DOMAIN.'/coworkers')
            ->assertOk()                 // statistics()/queries no longer 500 cross-DB
            ->assertSee('AClient');      // contextual (pivot) label, not the global "Client"
    }

    public function test_index_renders_for_a_rem_admin_viewer(): void
    {
        // Exercises the baseQuery() path that excludes super-admins' coworkers via an id-list.
        $this->makeCoworkerWithUser('client', null);

        $admin = User::factory()->create(['role' => 'rem_admin']);

        $this->actingAs($admin)
            ->get('http://'.self::TENANT_DOMAIN.'/coworkers')
            ->assertOk();
    }
}
