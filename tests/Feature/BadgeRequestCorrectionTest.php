<?php

namespace Tests\Feature;

use App\Models\ActivityRequest;
use App\Models\BadgeRequest;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TenantTestCase;

class BadgeRequestCorrectionTest extends TenantTestCase
{
    private const COMPONENT = 'badge-requests.create-form';

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /**
     * Setup commun : client + activity request approved + coworker + badge request rejetée
     * avec tous les documents existants pour qu'aucun upload ne soit obligatoire à la resoumission.
     *
     * @return array{client: Client, activityRequest: ActivityRequest, coworker: Coworker, badgeRequest: BadgeRequest}
     */
    private function makeRejectedRequest(string $status = 'rejected_rem', ?int $createdBy = null): array
    {
        $admin = User::factory()->create(['role' => 'rem_admin']);
        $client = Client::factory()->create(['company_name' => 'Acme Corp']);
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        $activityRequest = ActivityRequest::factory()->create([
            'client_id' => $client->id,
            'created_by' => $createdBy ?? $admin->id,
            'status' => 'approved',
            'person_count' => 3,
        ]);

        $rejectedAtField = $status === 'rejected_adp' ? 'rejected_adp_at' : 'rejected_rem_at';

        $badgeRequest = BadgeRequest::factory()->create([
            'created_by' => $createdBy ?? $admin->id,
            'activity_request_id' => $activityRequest->id,
            'coworker_id' => $coworker->id,
            'status' => $status,
            'reject_reason' => 'Photo illisible, merci de la refaire.',
            $rejectedAtField => Carbon::parse('2026-05-01 10:00:00'),
            'selfie_photo' => 'documents/'.$client->id.'/selfie.jpg',
            'identification_card' => 'documents/'.$client->id.'/id.pdf',
            'activity_authorization' => 'documents/'.$client->id.'/auth.pdf',
            'for_document' => 'documents/'.$client->id.'/for.xlsx',
            'formation_certificate_document' => 'documents/'.$client->id.'/certif.pdf',
            'invoice_document' => null,
            'application_authorization' => false,
            'validate_training' => false,
        ]);

        return compact('client', 'activityRequest', 'coworker', 'badgeRequest');
    }

    public function test_sclient_can_correct_rejected_rem_and_resubmit(): void
    {
        $data = $this->makeRejectedRequest('rejected_rem');
        $sclient = User::factory()->create(['role' => 'sclient', 'client_id' => $data['client']->id]);

        Livewire::actingAs($sclient)
            ->test(self::COMPONENT, ['badgeRequestId' => $data['badgeRequest']->id])
            ->assertSet('mode', 'correction')
            ->assertSet('sourceStatus', 'rejected_rem')
            ->assertSet('sourceRejectReason', 'Photo illisible, merci de la refaire.')
            ->call('submit')
            ->assertHasNoErrors();

        $data['badgeRequest']->refresh();

        $this->assertSame('pending_rem', $data['badgeRequest']->status);
        $this->assertNull($data['badgeRequest']->reject_reason);
        $this->assertNotNull($data['badgeRequest']->pending_rem_at);
        // La date de rejet doit être conservée pour la timeline.
        $this->assertNotNull($data['badgeRequest']->rejected_rem_at);
        $this->assertSame('2026-05-01 10:00:00', $data['badgeRequest']->rejected_rem_at->format('Y-m-d H:i:s'));
    }

    public function test_rejected_adp_resubmits_to_pending_rem(): void
    {
        $data = $this->makeRejectedRequest('rejected_adp');
        $sclient = User::factory()->create(['role' => 'sclient', 'client_id' => $data['client']->id]);

        Livewire::actingAs($sclient)
            ->test(self::COMPONENT, ['badgeRequestId' => $data['badgeRequest']->id])
            ->assertSet('mode', 'correction')
            ->assertSet('sourceStatus', 'rejected_adp')
            ->call('submit')
            ->assertHasNoErrors();

        $data['badgeRequest']->refresh();

        $this->assertSame('pending_rem', $data['badgeRequest']->status);
        $this->assertNull($data['badgeRequest']->reject_reason);
        // La trace du rejet ADP doit rester pour la timeline.
        $this->assertNotNull($data['badgeRequest']->rejected_adp_at);
    }

    public function test_admin_can_also_correct_rejected_request(): void
    {
        $data = $this->makeRejectedRequest('rejected_rem');
        $admin = User::factory()->create(['role' => 'rem_admin']);

        Livewire::actingAs($admin)
            ->test(self::COMPONENT, ['badgeRequestId' => $data['badgeRequest']->id])
            ->assertSet('mode', 'correction')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame('pending_rem', $data['badgeRequest']->fresh()->status);
    }

    public function test_save_draft_is_a_noop_in_correction_mode(): void
    {
        $data = $this->makeRejectedRequest('rejected_rem');
        $sclient = User::factory()->create(['role' => 'sclient', 'client_id' => $data['client']->id]);

        Livewire::actingAs($sclient)
            ->test(self::COMPONENT, ['badgeRequestId' => $data['badgeRequest']->id])
            ->call('saveDraft');

        // Le statut ne régresse pas vers brouillon.
        $this->assertSame('rejected_rem', $data['badgeRequest']->fresh()->status);
    }

    public function test_client_role_is_redirected_when_opening_form_for_rejected_request(): void
    {
        $data = $this->makeRejectedRequest('rejected_rem');
        $client = User::factory()->create(['role' => 'client', 'client_id' => $data['client']->id]);
        $client->tenants()->attach($this->tenant->getTenantKey(), ['role' => 'client', 'client_id' => $data['client']->id]);

        $this->actingAs($client)
            ->get(route('badge-requests.form', ['badgeRequestId' => $data['badgeRequest']->id]))
            ->assertRedirect(route('companies.show', ['companyId' => $data['client']->id]));
    }

    public function test_sclient_from_another_company_cannot_load_rejected_request(): void
    {
        $data = $this->makeRejectedRequest('rejected_rem');
        $otherClient = Client::factory()->create();
        $foreignSclient = User::factory()->create(['role' => 'sclient', 'client_id' => $otherClient->id]);

        // loadExisting redirige vers l'index quand l'utilisateur n'a pas accès à la demande.
        Livewire::actingAs($foreignSclient)
            ->test(self::COMPONENT, ['badgeRequestId' => $data['badgeRequest']->id])
            ->assertRedirect(route('badge-requests.index'));

        $this->assertSame('rejected_rem', $data['badgeRequest']->fresh()->status);
    }
}
