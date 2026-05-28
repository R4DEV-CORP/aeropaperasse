<?php

namespace Tests\Feature;

use App\Models\ActivityRequest;
use App\Models\BadgeRequest;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\User;
use App\Services\BadgeRequestDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TenantTestCase;

class BadgeRequestDocumentServiceTest extends TenantTestCase
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

    public function test_store_documents_uses_short_codes_for_mapped_types(): void
    {
        Storage::fake('public');

        $badgeRequest = $this->makeBadgeRequest();
        $client = $badgeRequest->activityRequest->client;
        $service = new BadgeRequestDocumentService;

        $stored = $service->storeDocuments([
            'selfie_photo' => UploadedFile::fake()->create('selfie.jpeg', 10, 'image/jpeg'),
            'identification_card' => UploadedFile::fake()->create('id.pdf', 10, 'application/pdf'),
            'activity_authorization' => UploadedFile::fake()->create('aao.pdf', 10, 'application/pdf'),
            'for_document' => UploadedFile::fake()->create('for.xlsx', 10),
            'invoice_document' => UploadedFile::fake()->create('fac.pdf', 10, 'application/pdf'),
            'formation_certificate_document' => UploadedFile::fake()->create('fre.pdf', 10, 'application/pdf'),
        ], $client, $badgeRequest);

        $this->assertStringEndsWith('/phi.jpeg', $stored['selfie_photo']);
        $this->assertStringEndsWith('/pid.pdf', $stored['identification_card']);
        $this->assertStringEndsWith('/aao.pdf', $stored['activity_authorization']);
        $this->assertStringEndsWith('/for.xlsx', $stored['for_document']);
        $this->assertStringEndsWith('/fac.pdf', $stored['invoice_document']);
        $this->assertStringEndsWith('/fre.pdf', $stored['formation_certificate_document']);

        foreach ($stored as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_store_documents_path_uses_client_folder_and_badge_request_id(): void
    {
        Storage::fake('public');

        $badgeRequest = $this->makeBadgeRequest();
        $client = $badgeRequest->activityRequest->client;
        $service = new BadgeRequestDocumentService;

        $stored = $service->storeDocuments([
            'selfie_photo' => UploadedFile::fake()->create('selfie.jpeg', 10, 'image/jpeg'),
        ], $client, $badgeRequest);

        $this->assertEquals(
            "clients/acme-corp/documents/badge-requests/{$badgeRequest->id}/phi.jpeg",
            $stored['selfie_photo']
        );
    }

    public function test_replacing_document_with_different_extension_deletes_previous_file(): void
    {
        Storage::fake('public');

        $badgeRequest = $this->makeBadgeRequest();
        $client = $badgeRequest->activityRequest->client;
        $service = new BadgeRequestDocumentService;

        $first = $service->storeDocuments([
            'selfie_photo' => UploadedFile::fake()->create('first.jpeg', 10, 'image/jpeg'),
        ], $client, $badgeRequest);

        $badgeRequest->update(['selfie_photo' => $first['selfie_photo']]);
        Storage::disk('public')->assertExists($first['selfie_photo']);

        $second = $service->storeDocuments([
            'selfie_photo' => UploadedFile::fake()->create('second.png', 10, 'image/png'),
        ], $client, $badgeRequest->fresh());

        Storage::disk('public')->assertMissing($first['selfie_photo']);
        Storage::disk('public')->assertExists($second['selfie_photo']);
        $this->assertStringEndsWith('/phi.png', $second['selfie_photo']);
    }

    public function test_zip_archive_uses_short_codes_as_entry_names(): void
    {
        if (! class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('Extension PHP zip non disponible.');
        }

        Storage::fake('public');

        $badgeRequest = $this->makeBadgeRequest();
        $client = $badgeRequest->activityRequest->client;
        $service = new BadgeRequestDocumentService;

        $stored = $service->storeDocuments([
            'selfie_photo' => UploadedFile::fake()->create('s.jpeg', 10, 'image/jpeg'),
            'activity_authorization' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'),
        ], $client, $badgeRequest);

        $badgeRequest->update($stored);

        $zipPath = $service->createDocumentsZip($badgeRequest->fresh());
        $this->assertNotNull($zipPath);

        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($zipPath) === true);

        $entryNames = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryNames[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($zipPath);

        $this->assertContains('phi.jpeg', $entryNames);
        $this->assertContains('aao.pdf', $entryNames);
    }

    public function test_unmapped_document_type_keeps_legacy_filename(): void
    {
        Storage::fake('public');

        $badgeRequest = $this->makeBadgeRequest();
        $client = $badgeRequest->activityRequest->client;
        $service = new BadgeRequestDocumentService;

        $stored = $service->storeDocuments([
            'return_badge_document' => UploadedFile::fake()->create('restitution.pdf', 10, 'application/pdf'),
        ], $client, $badgeRequest);

        $filename = basename($stored['return_badge_document']);
        $this->assertMatchesRegularExpression('/^return_badge_document-acme-corp-\d+\.pdf$/', $filename);
    }
}
