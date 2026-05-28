<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;
use Tests\TenantTestCase;

/**
 * The universal-login behaviour: a logged-in user who lacks access to the current tenant
 * is routed to a tenant chooser when they belong to other tenants, or to the no-access
 * page when they belong to none. See docs/multi-tenant-migration.md (Auth model).
 */
class TenantChooserTest extends TenantTestCase
{
    private function gatedUrl(): string
    {
        return 'http://'.self::TENANT_DOMAIN.'/companies';
    }

    public function test_user_without_access_to_current_tenant_but_member_of_another_is_sent_to_chooser(): void
    {
        // A lightweight tenant row (no DB) is enough — the gate only checks the pivot.
        $other = Tenant::withoutEvents(fn (): Tenant => Tenant::create(['id' => 'other']));

        $user = User::factory()->create(['role' => 'client']);
        $user->tenants()->attach($other->getTenantKey(), ['role' => 'owner']);

        $this->actingAs($user)
            ->get($this->gatedUrl())
            ->assertRedirect(route('tenant.choose'));
    }

    public function test_user_with_no_tenant_is_sent_to_no_access(): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $this->actingAs($user)
            ->get($this->gatedUrl())
            ->assertRedirect(route('tenant.no-access'));
    }

    public function test_rem_staff_reach_the_current_tenant_and_see_the_switch_space_link(): void
    {
        $user = User::factory()->create(['role' => 'rem_super_admin']);

        $this->actingAs($user)
            ->get($this->gatedUrl())
            ->assertOk()
            ->assertSee(route('tenant.choose')); // "Changer d'espace" link in the app sidebar
    }

    public function test_chooser_page_lists_the_users_tenants_by_name(): void
    {
        $this->tenant->update(['name' => 'Espace Test']);

        $user = User::factory()->create(['role' => 'client']);
        $user->tenants()->attach($this->tenant->getTenantKey(), ['role' => 'owner']);

        $this->actingAs($user)
            ->get('http://'.self::TENANT_DOMAIN.'/choose-tenant')
            ->assertOk()
            ->assertSee('Espace Test')          // friendly name
            ->assertSee(self::TENANT_DOMAIN);    // domain shown as subtitle
    }

    public function test_chooser_lists_all_tenants_for_rem_staff(): void
    {
        $this->tenant->update(['name' => 'Espace Test']);

        // A second tenant the REM user has no pivot row for — REM must still see it.
        $other = Tenant::withoutEvents(fn (): Tenant => Tenant::create(['id' => 'other', 'name' => 'Autre Espace']));
        $other->domains()->create(['domain' => 'other.test']);

        $rem = User::factory()->create(['role' => 'rem_super_admin']);

        Livewire::actingAs($rem)
            ->test('pages::auth.choose-tenant')
            ->assertSee('Espace Test')
            ->assertSee('Autre Espace');
    }

    public function test_chooser_search_filters_spaces_by_name(): void
    {
        $this->tenant->update(['name' => 'Espace Test']);

        $user = User::factory()->create(['role' => 'client']);
        $user->tenants()->attach($this->tenant->getTenantKey(), ['role' => 'owner']);

        Livewire::actingAs($user)
            ->test('pages::auth.choose-tenant')
            ->assertSee('Espace Test')
            ->set('search', 'introuvable')
            ->assertDontSee('Espace Test')
            ->set('search', 'espace')
            ->assertSee('Espace Test');
    }

    public function test_root_redirects_authenticated_user_to_their_landing(): void
    {
        $user = User::factory()->create(['role' => 'client', 'is_new' => false]);

        $this->actingAs($user)
            ->get('http://'.self::TENANT_DOMAIN.'/')
            ->assertRedirect('http://'.self::TENANT_DOMAIN.'/badge-requests');
    }

    public function test_root_redirects_guest_to_login(): void
    {
        $this->get('http://'.self::TENANT_DOMAIN.'/')
            ->assertRedirect(route('auth.login'));
    }
}
