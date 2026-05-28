<?php

namespace Tests\Feature;

use App\Models\ActivityRequest;
use App\Models\BadgeRequest;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TenantTestCase;

class RenameBadgeRequestDocumentsCommandTest extends TenantTestCase
{
    private function makeBadgeRequest(): BadgeRequest
    {
        $admin = User::factory()->create(['role' => 'rem_admin']);
        $client = Client::factory()->create(['company_name' => 'Acme Corp']);
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);
        $activityRequest = ActivityRequest::factory()->create([
            'client_id' => $client->id,
            'created_by' => $admin->id,
        ]);

        return BadgeRequest::factory()->create([
            'created_by' => $admin->id,
            'activity_request_id' => $activityRequest->id,
            'coworker_id' => $coworker->id,
            'selfie_photo' => null,
            'identification_card' => null,
            'activity_authorization' => null,
            'for_document' => null,
            'formation_certificate_document' => null,
            'invoice_document' => null,
        ]);
    }

    private function placeLegacyFile(BadgeRequest $badgeRequest, string $field, string $legacyName): string
    {
        $path = "clients/acme-corp/documents/badge-requests/{$badgeRequest->id}/{$legacyName}";
        Storage::disk('public')->put($path, 'fake-content');
        $badgeRequest->update([$field => $path]);

        return $path;
    }

    public function test_dry_run_does_not_modify_files_or_database(): void
    {
        Storage::fake('public');

        $badgeRequest = $this->makeBadgeRequest();
        $legacyPath = $this->placeLegacyFile($badgeRequest, 'selfie_photo', 'selfie_photo-acme-corp-1700000000.jpeg');

        $this->artisan('badge-requests:rename-documents', ['--dry-run' => true])
            ->assertSuccessful();

        Storage::disk('public')->assertExists($legacyPath);
        $this->assertEquals($legacyPath, $badgeRequest->fresh()->selfie_photo);
    }

    public function test_command_renames_files_and_updates_database(): void
    {
        Storage::fake('public');

        $badgeRequest = $this->makeBadgeRequest();
        $legacyPaths = [
            'selfie_photo' => $this->placeLegacyFile($badgeRequest, 'selfie_photo', 'selfie_photo-acme-corp-1700000000.jpeg'),
            'identification_card' => $this->placeLegacyFile($badgeRequest, 'identification_card', 'identification_card-acme-corp-1700000000.pdf'),
            'activity_authorization' => $this->placeLegacyFile($badgeRequest, 'activity_authorization', 'activity_authorization-acme-corp-1700000000.pdf'),
            'for_document' => $this->placeLegacyFile($badgeRequest, 'for_document', 'for_document-acme-corp-1700000000.xlsx'),
            'formation_certificate_document' => $this->placeLegacyFile($badgeRequest, 'formation_certificate_document', 'formation_certificate_document-acme-corp-1700000000.pdf'),
            'invoice_document' => $this->placeLegacyFile($badgeRequest, 'invoice_document', 'invoice_document-acme-corp-1700000000.pdf'),
        ];

        $this->artisan('badge-requests:rename-documents')
            ->assertSuccessful();

        $expected = [
            'selfie_photo' => 'phi.jpeg',
            'identification_card' => 'pid.pdf',
            'activity_authorization' => 'aao.pdf',
            'for_document' => 'for.xlsx',
            'formation_certificate_document' => 'fre.pdf',
            'invoice_document' => 'fac.pdf',
        ];

        $fresh = $badgeRequest->fresh();
        foreach ($expected as $field => $expectedFilename) {
            $this->assertStringEndsWith("/{$expectedFilename}", $fresh->{$field});
            Storage::disk('public')->assertExists($fresh->{$field});
            Storage::disk('public')->assertMissing($legacyPaths[$field]);
        }
    }

    public function test_command_is_idempotent(): void
    {
        Storage::fake('public');

        $badgeRequest = $this->makeBadgeRequest();
        $this->placeLegacyFile($badgeRequest, 'selfie_photo', 'selfie_photo-acme-corp-1700000000.jpeg');

        $this->artisan('badge-requests:rename-documents')->assertSuccessful();
        $afterFirstRun = $badgeRequest->fresh()->selfie_photo;

        $this->artisan('badge-requests:rename-documents')->assertSuccessful();
        $afterSecondRun = $badgeRequest->fresh()->selfie_photo;

        $this->assertEquals($afterFirstRun, $afterSecondRun);
        Storage::disk('public')->assertExists($afterSecondRun);
    }

    public function test_command_handles_missing_files_gracefully(): void
    {
        Storage::fake('public');

        $badgeRequest = $this->makeBadgeRequest();
        $orphanPath = "clients/acme-corp/documents/badge-requests/{$badgeRequest->id}/selfie_photo-acme-corp-1700000000.jpeg";
        $badgeRequest->update(['selfie_photo' => $orphanPath]);

        $this->artisan('badge-requests:rename-documents')
            ->assertSuccessful();

        $this->assertEquals($orphanPath, $badgeRequest->fresh()->selfie_photo);
    }
}
