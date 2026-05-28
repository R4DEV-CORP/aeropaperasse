<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\User;
use Livewire\Livewire;
use Tests\TenantTestCase;

class EditBadgeFormTest extends TenantTestCase
{
    private const COMPONENT = 'badge-management.edit-number-modal';

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'rem_admin']);
    }

    private function makeSAdmin(): User
    {
        return User::factory()->create(['role' => 'rem_super_admin']);
    }

    private function makeClientUser(Client $client): User
    {
        return User::factory()->create(['role' => 'client', 'client_id' => $client->id]);
    }

    private function makeBadge(string $airport = 'CDG', ?string $number = null): Badge
    {
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        return Badge::create([
            'client_id' => $client->id,
            'coworker_id' => $coworker->id,
            'badge_request_id' => null,
            'airport' => $airport,
            'badge_number' => $number,
            'status' => 'active',
            'expiry_date' => now()->addYear(),
        ]);
    }

    public function test_admin_can_edit_badge_number_and_airport(): void
    {
        $admin = $this->makeAdmin();
        $badge = $this->makeBadge('CDG', '111');

        Livewire::actingAs($admin)
            ->test(self::COMPONENT)
            ->call('open', $badge->id)
            ->assertSet('airport', 'CDG')
            ->assertSet('badge_number', '111')
            ->set('badge_number', '999')
            ->set('airport', 'ORY')
            ->call('submit')
            ->assertDispatched('badge-updated');

        $badge->refresh();
        $this->assertEquals('999', $badge->badge_number);
        $this->assertEquals('ORY', $badge->airport);
    }

    public function test_sadmin_can_edit_badge(): void
    {
        $sadmin = $this->makeSAdmin();
        $badge = $this->makeBadge('CDG');

        Livewire::actingAs($sadmin)
            ->test(self::COMPONENT)
            ->call('open', $badge->id)
            ->set('airport', 'LBG')
            ->call('submit')
            ->assertDispatched('badge-updated');

        $this->assertEquals('LBG', $badge->refresh()->airport);
    }

    public function test_non_admin_cannot_open_modal(): void
    {
        $badge = $this->makeBadge('CDG');
        $clientUser = $this->makeClientUser($badge->client);

        Livewire::actingAs($clientUser)
            ->test(self::COMPONENT)
            ->call('open', $badge->id)
            ->assertSet('badgeId', null);

        $this->assertEquals('CDG', $badge->refresh()->airport);
    }

    public function test_non_admin_submit_is_a_noop(): void
    {
        $badge = $this->makeBadge('CDG');
        $clientUser = $this->makeClientUser($badge->client);

        // On force les valeurs comme si la modal avait été ouverte côté client.
        Livewire::actingAs($clientUser)
            ->test(self::COMPONENT)
            ->set('badgeId', $badge->id)
            ->set('badge_number', $badge->badge_number)
            ->set('airport', 'ORY')
            ->call('submit')
            ->assertNotDispatched('badge-updated');

        $this->assertEquals('CDG', $badge->refresh()->airport);
    }

    public function test_airport_is_required(): void
    {
        $admin = $this->makeAdmin();
        $badge = $this->makeBadge('CDG');

        Livewire::actingAs($admin)
            ->test(self::COMPONENT)
            ->call('open', $badge->id)
            ->set('airport', null)
            ->call('submit')
            ->assertHasErrors(['airport']);

        $this->assertEquals('CDG', $badge->refresh()->airport);
    }

    public function test_invalid_airport_is_rejected(): void
    {
        $admin = $this->makeAdmin();
        $badge = $this->makeBadge('CDG');

        Livewire::actingAs($admin)
            ->test(self::COMPONENT)
            ->call('open', $badge->id)
            ->set('airport', 'XYZ')
            ->call('submit')
            ->assertHasErrors(['airport']);
    }
}
