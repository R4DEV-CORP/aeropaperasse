<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateStandaloneBadgeTest extends TestCase
{
    use RefreshDatabase;

    private const COMPONENT = 'badge-management.create-standalone-form';

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeClientUser(Client $client): User
    {
        return User::factory()->create(['role' => 'client', 'client_id' => $client->id]);
    }

    private function makeSClientUser(Client $client): User
    {
        return User::factory()->create(['role' => 'sclient', 'client_id' => $client->id]);
    }

    public function test_admin_can_create_standalone_badge(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        Livewire::actingAs($admin)
            ->test(self::COMPONENT)
            ->set('selected_client_id', $client->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('airport', 'CDG')
            ->set('expiry_date', now()->addYear()->toDateString())
            ->call('createBadge')
            ->assertRedirect(route('badge-management.index'));

        $badge = Badge::first();
        $this->assertNotNull($badge);
        $this->assertNull($badge->badge_request_id);
        $this->assertEquals($client->id, $badge->client_id);
        $this->assertEquals($coworker->id, $badge->coworker_id);
        $this->assertEquals('CDG', $badge->airport);
        $this->assertEquals('active', $badge->status);
    }

    public function test_standalone_badge_requires_airport(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        Livewire::actingAs($admin)
            ->test(self::COMPONENT)
            ->set('selected_client_id', $client->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('airport', null)
            ->set('expiry_date', now()->addYear()->toDateString())
            ->call('createBadge')
            ->assertHasErrors(['airport']);
    }

    public function test_client_role_cannot_access_form_component(): void
    {
        $client = Client::factory()->create();
        $clientUser = $this->makeClientUser($client);

        Livewire::actingAs($clientUser)
            ->test(self::COMPONENT)
            ->assertStatus(403);

        $this->assertEquals(0, Badge::count());
    }

    public function test_client_role_is_redirected_from_form_route(): void
    {
        $client = Client::factory()->create(['slug' => 'acme']);
        $clientUser = $this->makeClientUser($client);

        $this->actingAs($clientUser)
            ->get(route('badge-management.form', ['mode' => 'standalone']))
            ->assertRedirect(route('companies.show', ['companyId' => $client->id]));
    }

    public function test_sclient_can_create_standalone_badge_for_their_own_client(): void
    {
        $client = Client::factory()->create();
        $sclient = $this->makeSClientUser($client);
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        // Le sclient ne sélectionne pas son client (UI cachée), mais celui-ci doit
        // être auto-rempli via mount() et préservé même si on tente de le forcer.
        Livewire::actingAs($sclient)
            ->test(self::COMPONENT)
            ->assertSet('selected_client_id', $client->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('airport', 'CDG')
            ->set('expiry_date', now()->addYear()->toDateString())
            ->call('createBadge')
            ->assertRedirect(route('badge-management.index'));

        $badge = Badge::first();
        $this->assertNotNull($badge);
        $this->assertEquals($client->id, $badge->client_id);
    }

    public function test_sclient_cannot_override_client_id(): void
    {
        $ownClient = Client::factory()->create();
        $otherClient = Client::factory()->create();
        $sclient = $this->makeSClientUser($ownClient);
        $coworker = Coworker::factory()->create(['client_id' => $ownClient->id]);

        // Tentative de bypass : on force selected_client_id à un autre client.
        // Le composant doit le réécrire à partir du user au moment du submit.
        Livewire::actingAs($sclient)
            ->test(self::COMPONENT)
            ->set('selected_client_id', $otherClient->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('airport', 'CDG')
            ->set('expiry_date', now()->addYear()->toDateString())
            ->call('createBadge');

        $badge = Badge::first();
        $this->assertNotNull($badge);
        $this->assertEquals($ownClient->id, $badge->client_id);
    }

    public function test_standalone_badge_requires_valid_client_and_coworker(): void
    {
        $admin = $this->makeAdmin();

        Livewire::actingAs($admin)
            ->test(self::COMPONENT)
            ->set('selected_client_id', null)
            ->set('selected_coworker_id', null)
            ->set('airport', null)
            ->set('expiry_date', null)
            ->call('createBadge')
            ->assertHasErrors(['selected_client_id', 'selected_coworker_id', 'airport', 'expiry_date']);
    }

    public function test_standalone_badge_requires_future_expiry_date(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        Livewire::actingAs($admin)
            ->test(self::COMPONENT)
            ->set('selected_client_id', $client->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('airport', 'CDG')
            ->set('expiry_date', now()->subDay()->toDateString())
            ->call('createBadge')
            ->assertHasErrors(['expiry_date']);
    }

    public function test_changing_client_resets_selected_coworker(): void
    {
        $admin = $this->makeAdmin();
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client1->id]);

        Livewire::actingAs($admin)
            ->test(self::COMPONENT)
            ->set('selected_client_id', $client1->id)
            ->set('selected_coworker_id', $coworker->id)
            ->assertSet('selected_coworker_id', $coworker->id)
            ->set('selected_client_id', $client2->id)
            ->assertSet('selected_coworker_id', null);
    }

    public function test_standalone_badge_appears_in_badge_management_index(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        Badge::create([
            'client_id' => $client->id,
            'coworker_id' => $coworker->id,
            'badge_request_id' => null,
            'status' => 'active',
            'expiry_date' => now()->addYear(),
        ]);

        $this->actingAs($admin)
            ->get(route('badge-management.index'))
            ->assertStatus(200);
    }

    public function test_standalone_badge_effective_client_and_coworker(): void
    {
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $badge = Badge::create([
            'client_id' => $client->id,
            'coworker_id' => $coworker->id,
            'badge_request_id' => null,
            'status' => 'active',
            'expiry_date' => now()->addYear(),
        ]);

        $badge->load(['client', 'coworker']);

        $this->assertEquals($client->id, $badge->getEffectiveClient()->id);
        $this->assertEquals($coworker->id, $badge->getEffectiveCoworker()->id);
    }
}
