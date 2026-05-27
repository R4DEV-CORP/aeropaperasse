<?php

namespace Tests\Feature;

use App\Models\ActivityRequest;
use App\Models\BadgeRequest;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TenantTestCase;

class AClientRoleTest extends TenantTestCase
{
    private function makeAClient(Client $client): User
    {
        return User::factory()->create([
            'role' => 'aclient',
            'client_id' => $client->id,
        ]);
    }

    public function test_aclient_helper_methods(): void
    {
        $aclient = User::factory()->create(['role' => 'aclient']);

        $this->assertTrue($aclient->isAClient());
        $this->assertTrue($aclient->canChangeRequestStatus());
        $this->assertFalse($aclient->isAdmin());
        $this->assertFalse($aclient->isClient());
        $this->assertFalse($aclient->isSClient());
        $this->assertFalse($aclient->isSAdmin());
    }

    public function test_can_change_request_status_matrix(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sadmin = User::factory()->create(['role' => 'sadmin']);
        $sclient = User::factory()->create(['role' => 'sclient']);
        $client = User::factory()->create(['role' => 'client']);

        $this->assertTrue($admin->canChangeRequestStatus());
        $this->assertTrue($sadmin->canChangeRequestStatus());
        $this->assertFalse($sclient->canChangeRequestStatus());
        $this->assertFalse($client->canChangeRequestStatus());
    }

    public function test_aclient_can_approve_activity_request_from_index(): void
    {
        Mail::fake();

        $client = Client::factory()->create();
        $aclient = $this->makeAClient($client);

        $activityRequest = ActivityRequest::factory()
            ->for($client)
            ->for($aclient, 'creator')
            ->create(['status' => 'pending']);

        Livewire::actingAs($aclient)
            ->test('pages::activity-requests.index')
            ->call('approve', $activityRequest->id);

        $this->assertDatabaseHas('activity_requests', [
            'id' => $activityRequest->id,
            'status' => 'approved',
        ]);
    }

    public function test_aclient_can_reject_activity_request_via_modal(): void
    {
        Mail::fake();

        $client = Client::factory()->create();
        $aclient = $this->makeAClient($client);

        $activityRequest = ActivityRequest::factory()
            ->for($client)
            ->for($aclient, 'creator')
            ->create(['status' => 'pending']);

        Livewire::actingAs($aclient)
            ->test('activity-requests.reject-modal')
            ->call('open', $activityRequest->id)
            ->set('rejectReason', 'Documents incomplets')
            ->call('submit');

        $this->assertDatabaseHas('activity_requests', [
            'id' => $activityRequest->id,
            'status' => 'rejected',
            'reject_reason' => 'Documents incomplets',
        ]);
    }

    public function test_sclient_role_cannot_approve_activity_request(): void
    {
        Mail::fake();

        $client = Client::factory()->create();
        $sclient = User::factory()->create([
            'role' => 'sclient',
            'client_id' => $client->id,
        ]);

        $activityRequest = ActivityRequest::factory()
            ->for($client)
            ->for($sclient, 'creator')
            ->create(['status' => 'pending']);

        Livewire::actingAs($sclient)
            ->test('pages::activity-requests.index')
            ->call('approve', $activityRequest->id);

        $this->assertDatabaseHas('activity_requests', [
            'id' => $activityRequest->id,
            'status' => 'pending',
        ]);
    }

    public function test_aclient_can_validate_rem_on_badge_request(): void
    {
        $client = Client::factory()->create();
        $aclient = $this->makeAClient($client);
        $coworker = Coworker::factory()->for($client)->create();

        $activityRequest = ActivityRequest::factory()
            ->for($client)
            ->for($aclient, 'creator')
            ->create();

        $badgeRequest = BadgeRequest::factory()
            ->for($activityRequest)
            ->for($coworker)
            ->for($aclient, 'creator')
            ->create(['status' => 'pending_rem']);

        Livewire::actingAs($aclient)
            ->test('pages::badge-requests.index')
            ->call('validateRem', $badgeRequest->id);

        $this->assertDatabaseHas('badge_requests', [
            'id' => $badgeRequest->id,
            'status' => 'pending_adp',
        ]);
    }

    public function test_aclient_can_approve_adp_on_badge_request(): void
    {
        $client = Client::factory()->create();
        $aclient = $this->makeAClient($client);
        $coworker = Coworker::factory()->for($client)->create();

        $activityRequest = ActivityRequest::factory()
            ->for($client)
            ->for($aclient, 'creator')
            ->create();

        $badgeRequest = BadgeRequest::factory()
            ->for($activityRequest)
            ->for($coworker)
            ->for($aclient, 'creator')
            ->create(['status' => 'pending_adp']);

        Livewire::actingAs($aclient)
            ->test('pages::badge-requests.index')
            ->call('approveAdp', $badgeRequest->id);

        $this->assertDatabaseHas('badge_requests', [
            'id' => $badgeRequest->id,
            'status' => 'approved_adp',
        ]);
    }

    public function test_aclient_can_reject_badge_request_via_modal(): void
    {
        $client = Client::factory()->create();
        $aclient = $this->makeAClient($client);
        $coworker = Coworker::factory()->for($client)->create();

        $activityRequest = ActivityRequest::factory()
            ->for($client)
            ->for($aclient, 'creator')
            ->create();

        $badgeRequest = BadgeRequest::factory()
            ->for($activityRequest)
            ->for($coworker)
            ->for($aclient, 'creator')
            ->create(['status' => 'pending_rem']);

        Livewire::actingAs($aclient)
            ->test('badge-requests.reject-modal')
            ->call('open', $badgeRequest->id, 'rejected_rem')
            ->set('rejectReason', 'Document manquant')
            ->call('submit');

        $this->assertDatabaseHas('badge_requests', [
            'id' => $badgeRequest->id,
            'status' => 'rejected_rem',
            'reject_reason' => 'Document manquant',
        ]);
    }

    public function test_aclient_only_sees_their_own_client_activity_requests(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();

        $aclient = $this->makeAClient($clientA);

        $ownRequest = ActivityRequest::factory()
            ->for($clientA)
            ->for($aclient, 'creator')
            ->create(['status' => 'pending']);

        $otherUser = User::factory()->create(['role' => 'client', 'client_id' => $clientB->id]);
        $otherRequest = ActivityRequest::factory()
            ->for($clientB)
            ->for($otherUser, 'creator')
            ->create(['status' => 'pending']);

        $component = Livewire::actingAs($aclient)
            ->test('pages::activity-requests.index');

        $activityRequests = $component->instance()->activityRequests;
        $ids = collect($activityRequests->items())->pluck('id')->all();

        $this->assertContains($ownRequest->id, $ids);
        $this->assertNotContains($otherRequest->id, $ids);
    }
}
