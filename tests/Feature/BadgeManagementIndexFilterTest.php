<?php

namespace Tests\Feature;

use App\Models\ActivityRequest;
use App\Models\Badge;
use App\Models\BadgeRequest;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\User;
use Livewire\Livewire;
use Tests\TenantTestCase;

class BadgeManagementIndexFilterTest extends TenantTestCase
{
    private const PAGE = 'pages::badge-management.index';

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function createStandaloneBadge(Client $client, Coworker $coworker, string $airport): Badge
    {
        return Badge::create([
            'client_id' => $client->id,
            'coworker_id' => $coworker->id,
            'badge_request_id' => null,
            'airport' => $airport,
            'status' => 'active',
            'expiry_date' => now()->addYear(),
        ]);
    }

    private function createLinkedBadge(User $admin, Client $client, Coworker $coworker, string $airport): Badge
    {
        $activityRequest = ActivityRequest::factory()->create([
            'client_id' => $client->id,
            'created_by' => $admin->id,
            'airport' => $airport,
        ]);

        $badgeRequest = BadgeRequest::factory()->create([
            'created_by' => $admin->id,
            'activity_request_id' => $activityRequest->id,
            'coworker_id' => $coworker->id,
        ]);

        return Badge::create([
            'badge_request_id' => $badgeRequest->id,
            'airport' => $activityRequest->airport,
            'status' => 'active',
            'expiry_date' => now()->addYear(),
        ]);
    }

    public function test_filter_by_airport_returns_only_matching_standalone_badges(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $cdgBadge = $this->createStandaloneBadge($client, $coworker, 'CDG');
        $this->createStandaloneBadge($client, $coworker, 'ORY');
        $this->createStandaloneBadge($client, $coworker, 'LBG');

        $component = Livewire::actingAs($admin)
            ->test(self::PAGE)
            ->set('selectedAirport', 'CDG');

        $badges = $component->instance()->badges;

        $this->assertEquals(1, $badges->total());
        $this->assertEquals($cdgBadge->id, $badges->items()[0]->id);
    }

    public function test_filter_by_airport_returns_only_matching_linked_badges(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $this->createLinkedBadge($admin, $client, $coworker, 'CDG');
        $oryBadge = $this->createLinkedBadge($admin, $client, $coworker, 'ORY');

        $component = Livewire::actingAs($admin)
            ->test(self::PAGE)
            ->set('selectedAirport', 'ORY');

        $badges = $component->instance()->badges;

        $this->assertEquals(1, $badges->total());
        $this->assertEquals($oryBadge->id, $badges->items()[0]->id);
    }

    public function test_filter_by_airport_returns_both_standalone_and_linked_badges(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $standaloneCdg = $this->createStandaloneBadge($client, $coworker, 'CDG');
        $linkedCdg = $this->createLinkedBadge($admin, $client, $coworker, 'CDG');
        $standaloneOry = $this->createStandaloneBadge($client, $coworker, 'ORY');

        $component = Livewire::actingAs($admin)
            ->test(self::PAGE)
            ->set('selectedAirport', 'CDG');

        $badges = $component->instance()->badges;
        $ids = collect($badges->items())->pluck('id')->all();

        $this->assertEquals(2, $badges->total());
        $this->assertContains($standaloneCdg->id, $ids);
        $this->assertContains($linkedCdg->id, $ids);
        $this->assertNotContains($standaloneOry->id, $ids);
    }

    public function test_no_filter_returns_all_badges(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $this->createStandaloneBadge($client, $coworker, 'CDG');
        $this->createStandaloneBadge($client, $coworker, 'ORY');
        $this->createStandaloneBadge($client, $coworker, 'LBG');

        $component = Livewire::actingAs($admin)->test(self::PAGE);

        $this->assertEquals(3, $component->instance()->badges->total());
    }

    public function test_reset_filters_clears_airport_and_status(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $this->createStandaloneBadge($client, $coworker, 'CDG');
        $this->createStandaloneBadge($client, $coworker, 'ORY');

        Livewire::actingAs($admin)
            ->test(self::PAGE)
            ->set('selectedAirport', 'CDG')
            ->set('selectedStatus', 'active')
            ->call('resetFilters')
            ->assertSet('selectedAirport', null)
            ->assertSet('selectedStatus', null)
            ->assertSet('search', '');
    }

    public function test_filter_by_status_keeps_only_matching_badges(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $active = $this->createStandaloneBadge($client, $coworker, 'CDG');
        $returned = Badge::create([
            'client_id' => $client->id,
            'coworker_id' => $coworker->id,
            'badge_request_id' => null,
            'airport' => 'CDG',
            'status' => 'returned',
            'returned_at' => now(),
            'expiry_date' => now()->addYear(),
        ]);

        $component = Livewire::actingAs($admin)
            ->test(self::PAGE)
            ->call('filterByStatus', 'active');

        $ids = collect($component->instance()->badges->items())->pluck('id')->all();
        $this->assertContains($active->id, $ids);
        $this->assertNotContains($returned->id, $ids);
    }

    public function test_filter_by_status_toggles_off_when_clicked_twice(): void
    {
        $admin = $this->makeAdmin();

        Livewire::actingAs($admin)
            ->test(self::PAGE)
            ->call('filterByStatus', 'active')
            ->assertSet('selectedStatus', 'active')
            ->call('filterByStatus', 'active')
            ->assertSet('selectedStatus', null);
    }

    public function test_statistics_count_per_status(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $this->createStandaloneBadge($client, $coworker, 'CDG');
        $this->createStandaloneBadge($client, $coworker, 'CDG');
        Badge::create([
            'client_id' => $client->id,
            'coworker_id' => $coworker->id,
            'badge_request_id' => null,
            'airport' => 'CDG',
            'status' => 'returned',
            'returned_at' => now(),
            'expiry_date' => now()->addYear(),
        ]);

        $component = Livewire::actingAs($admin)->test(self::PAGE);
        $stats = $component->instance()->statistics;

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['active']);
        $this->assertEquals(1, $stats['returned']);
    }

    public function test_linked_badge_creation_persists_airport(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $badge = $this->createLinkedBadge($admin, $client, $coworker, 'LBG');

        $this->assertEquals('LBG', $badge->fresh()->airport);
    }

    public function test_mount_marks_active_badges_with_past_expiry_as_expired(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $expired = Badge::create([
            'client_id' => $client->id,
            'coworker_id' => $coworker->id,
            'badge_request_id' => null,
            'airport' => 'CDG',
            'status' => 'active',
            'expiry_date' => now()->subDay(),
        ]);

        Livewire::actingAs($admin)->test(self::PAGE);

        $this->assertEquals('expired', $expired->fresh()->status);
    }
}
