<?php

namespace Tests\Feature;

use App\Mail\BadgeExpiryNotification;
use App\Models\Badge;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\Tenant;
use Illuminate\Support\Facades\Mail;
use Tests\TenantTestCase;

/**
 * The badge-expiry scheduled command must run inside each tenant's context, since
 * `badges` live in the per-tenant databases. See docs/multi-tenant-migration.md
 * (tenant-aware infrastructure).
 */
class NotifyBadgeExpiryTest extends TenantTestCase
{
    public function test_it_notifies_badge_holders_in_every_tenant(): void
    {
        Mail::fake();

        // A badge expiring in 30 days in the base `test` tenant.
        $this->seedExpiringBadge('holder-test@example.com');

        // A second tenant with its own expiring badge, to prove the command iterates
        // tenants and stays isolated to each tenant's data.
        $second = Tenant::create(['id' => 'second']);

        try {
            $second->run(function (): void {
                $this->seedExpiringBadge('holder-second@example.com');
            });

            $this->artisan('badges:check-expiry')->assertSuccessful();

            Mail::assertSent(
                BadgeExpiryNotification::class,
                fn (BadgeExpiryNotification $mail): bool => $mail->hasTo('holder-test@example.com'),
            );
            Mail::assertSent(
                BadgeExpiryNotification::class,
                fn (BadgeExpiryNotification $mail): bool => $mail->hasTo('holder-second@example.com'),
            );
            Mail::assertSent(BadgeExpiryNotification::class, 2);
        } finally {
            $second->delete();
        }
    }

    public function test_it_ignores_badges_outside_the_notification_window(): void
    {
        Mail::fake();

        $client = Client::factory()->create(['notification_email' => 'holder@example.com']);
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        Badge::create([
            'client_id' => $client->id,
            'coworker_id' => $coworker->id,
            'badge_request_id' => null,
            'airport' => 'CDG',
            'status' => 'active',
            'expiry_date' => now()->addDays(45),
        ]);

        $this->artisan('badges:check-expiry')->assertSuccessful();

        Mail::assertNothingSent();
    }

    private function seedExpiringBadge(string $notificationEmail): void
    {
        $client = Client::factory()->create(['notification_email' => $notificationEmail]);
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);

        Badge::create([
            'client_id' => $client->id,
            'coworker_id' => $coworker->id,
            'badge_request_id' => null,
            'airport' => 'CDG',
            'status' => 'active',
            'expiry_date' => now()->addDays(30),
        ]);
    }
}
