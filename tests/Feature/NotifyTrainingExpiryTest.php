<?php

namespace Tests\Feature;

use App\Mail\TrainingExpiryNotification;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\CoworkerTraining;
use App\Models\Tenant;
use App\Models\Training;
use Illuminate\Support\Facades\Mail;
use Tests\TenantTestCase;

/**
 * The training-expiry scheduled command must run inside each tenant's context, since
 * training assignments live in the per-tenant `coworker_trainings` table (the shared
 * `trainings` catalog stays central). See docs/multi-tenant-migration.md.
 */
class NotifyTrainingExpiryTest extends TenantTestCase
{
    public function test_it_notifies_coworkers_in_every_tenant(): void
    {
        Mail::fake();

        // A training expiring in 30 days for a coworker in the base `test` tenant.
        $this->seedExpiringTraining('coworker-test@example.com');

        // A second tenant with its own expiring training, to prove the command iterates
        // tenants and stays isolated to each tenant's data.
        $second = Tenant::create(['id' => 'second']);

        try {
            $second->run(function (): void {
                $this->seedExpiringTraining('coworker-second@example.com');
            });

            $this->artisan('trainings:check-expiry')->assertSuccessful();

            Mail::assertSent(
                TrainingExpiryNotification::class,
                fn (TrainingExpiryNotification $mail): bool => $mail->hasTo('coworker-test@example.com'),
            );
            Mail::assertSent(
                TrainingExpiryNotification::class,
                fn (TrainingExpiryNotification $mail): bool => $mail->hasTo('coworker-second@example.com'),
            );
            Mail::assertSent(TrainingExpiryNotification::class, 2);
        } finally {
            $second->delete();
        }
    }

    public function test_it_ignores_trainings_outside_the_notification_window(): void
    {
        Mail::fake();

        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id, 'email' => 'coworker@example.com']);
        $training = Training::firstOrCreate(['title' => 'Sûreté aéroportuaire'], ['requires_airport' => true]);

        CoworkerTraining::create([
            'coworker_id' => $coworker->id,
            'training_id' => $training->id,
            'started_at' => now()->subYears(3),
            'expires_at' => now()->addDays(45),
        ]);

        $this->artisan('trainings:check-expiry')->assertSuccessful();

        Mail::assertNothingSent();
    }

    private function seedExpiringTraining(string $coworkerEmail): void
    {
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id, 'email' => $coworkerEmail]);
        $training = Training::firstOrCreate(['title' => 'Sûreté aéroportuaire'], ['requires_airport' => true]);

        CoworkerTraining::create([
            'coworker_id' => $coworker->id,
            'training_id' => $training->id,
            'started_at' => now()->subYears(3),
            'expires_at' => now()->addDays(30),
        ]);
    }
}
