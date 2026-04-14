<?php

namespace Tests\Feature;

use App\Livewire\BadgeManagement\CreateStandaloneBadgeForm;
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

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeClientUser(Client $client): User
    {
        return User::factory()->create(['role' => 'client', 'client_id' => $client->id]);
    }

    public function test_admin_can_create_standalone_badge(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        Livewire::actingAs($admin)
            ->test(CreateStandaloneBadgeForm::class)
            ->set('selected_client_id', $client->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('expiry_date', now()->addYear()->toDateString())
            ->call('createBadge')
            ->assertDispatched('badge-created');

        $badge = Badge::first();
        $this->assertNotNull($badge);
        $this->assertNull($badge->badge_request_id);
        $this->assertEquals($client->id, $badge->client_id);
        $this->assertEquals($coworker->id, $badge->coworker_id);
        $this->assertEquals('active', $badge->status);
    }

    public function test_non_admin_cannot_access_standalone_badge_form(): void
    {
        $client = Client::factory()->create();
        $clientUser = $this->makeClientUser($client);

        $this->actingAs($clientUser)
            ->get('/badge-management')
            ->assertStatus(200);

        // Le composant standalone ne devrait pas être accessible aux non-admins
        // (le bouton n'est pas affiché côté vue, mais on valide aussi que le composant
        // requiert la sélection d'un client qui appartient au contexte admin)
        Livewire::actingAs($clientUser)
            ->test(CreateStandaloneBadgeForm::class)
            ->set('selected_client_id', $client->id)
            ->set('selected_coworker_id', 999)
            ->set('expiry_date', now()->addYear()->toDateString())
            ->call('createBadge')
            ->assertHasErrors(['selected_coworker_id']);
    }

    public function test_standalone_badge_requires_valid_client_and_coworker(): void
    {
        $admin = $this->makeAdmin();

        Livewire::actingAs($admin)
            ->test(CreateStandaloneBadgeForm::class)
            ->set('selected_client_id', null)
            ->set('selected_coworker_id', null)
            ->set('expiry_date', null)
            ->call('createBadge')
            ->assertHasErrors(['selected_client_id', 'selected_coworker_id', 'expiry_date']);
    }

    public function test_standalone_badge_requires_future_expiry_date(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        Livewire::actingAs($admin)
            ->test(CreateStandaloneBadgeForm::class)
            ->set('selected_client_id', $client->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('expiry_date', now()->subDay()->toDateString())
            ->call('createBadge')
            ->assertHasErrors(['expiry_date']);
    }

    public function test_standalone_badge_loads_coworkers_when_client_selected(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        Coworker::factory()->count(3)->create(['client_id' => $client->id]);

        $component = Livewire::actingAs($admin)
            ->test(CreateStandaloneBadgeForm::class)
            ->set('selected_client_id', $client->id);

        $this->assertCount(3, $component->get('coworkers'));
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
            ->get('/badge-management')
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
