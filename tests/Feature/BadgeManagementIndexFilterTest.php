<?php

namespace Tests\Feature;

use App\Livewire\BadgeManagement\Index;
use App\Models\ActivityRequest;
use App\Models\Badge;
use App\Models\BadgeRequest;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BadgeManagementIndexFilterTest extends TestCase
{
    use RefreshDatabase;

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
        $oryBadge = $this->createStandaloneBadge($client, $coworker, 'ORY');
        $lbgBadge = $this->createStandaloneBadge($client, $coworker, 'LBG');

        $component = Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('selectedAirport', 'CDG');

        $badges = $component->viewData('badges');

        $this->assertEquals(1, $badges->total());
        $this->assertEquals($cdgBadge->id, $badges->items()[0]->id);
    }

    public function test_filter_by_airport_returns_only_matching_linked_badges(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $cdgBadge = $this->createLinkedBadge($admin, $client, $coworker, 'CDG');
        $oryBadge = $this->createLinkedBadge($admin, $client, $coworker, 'ORY');

        $component = Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('selectedAirport', 'ORY');

        $badges = $component->viewData('badges');

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
            ->test(Index::class)
            ->set('selectedAirport', 'CDG');

        $badges = $component->viewData('badges');
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

        $component = Livewire::actingAs($admin)
            ->test(Index::class);

        $this->assertEquals(3, $component->viewData('badges')->total());
    }

    public function test_reset_filters_clears_airport(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $this->createStandaloneBadge($client, $coworker, 'CDG');
        $this->createStandaloneBadge($client, $coworker, 'ORY');

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('selectedAirport', 'CDG')
            ->call('resetFilters')
            ->assertSet('selectedAirport', null)
            ->assertSet('search', '');
    }

    public function test_stats_respect_airport_filter(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $this->createStandaloneBadge($client, $coworker, 'CDG');
        $this->createStandaloneBadge($client, $coworker, 'CDG');
        $this->createStandaloneBadge($client, $coworker, 'ORY');

        $component = Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('selectedAirport', 'CDG');

        $stats = $component->viewData('stats');

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(2, $stats['active']);
    }

    public function test_linked_badge_creation_persists_airport(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $badge = $this->createLinkedBadge($admin, $client, $coworker, 'LBG');

        $this->assertEquals('LBG', $badge->fresh()->airport);
    }
}
