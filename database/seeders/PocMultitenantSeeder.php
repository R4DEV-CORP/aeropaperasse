<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Coworker;
use App\Models\Tenant;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * POC multi-tenant demo data. Idempotent. Run with:
 *   php artisan db:seed --class=PocMultitenantSeeder
 *
 * Seeds:
 *  - shared trainings (central),
 *  - a REM super-admin (central role `sadmin`) — reaches every tenant via the REM short-circuit, no pivot row,
 *  - a C1 owner (no central REM role) attached to tenant `c1` only via the `tenant_user` pivot,
 *  - a couple of distinct clients/coworkers inside each tenant DB so isolation is visible.
 *
 * See docs/multi-tenant-migration.md (POC scope).
 */
class PocMultitenantSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSharedTrainings();

        $remAdmin = User::updateOrCreate(
            ['email' => 'rem-admin@aeropaperasse.test'],
            ['name' => 'REM Super Admin', 'password' => 'password', 'role' => 'sadmin', 'can_access_formation' => true],
        );

        $c1Owner = User::updateOrCreate(
            ['email' => 'owner@client1.test'],
            ['name' => 'Owner Client 1', 'password' => 'password', 'role' => 'client', 'can_access_formation' => true],
        );

        $rem = Tenant::findOrFail('rem');
        $c1 = Tenant::findOrFail('c1');

        // C1 owner belongs to c1 only, with the tenant-scoped role `owner`. REM admin keeps no pivot row
        // (their cross-tenant access comes from the REM short-circuit), so app.* rejects the C1 owner.
        $c1Owner->tenants()->syncWithoutDetaching([
            $c1->getTenantKey() => ['role' => 'owner'],
        ]);

        $this->seedTenantBusinessData($rem, 'REM');
        $this->seedTenantBusinessData($c1, 'Client1');
    }

    private function seedSharedTrainings(): void
    {
        foreach (['Sûreté aéroportuaire', 'Accès zone réservée'] as $title) {
            Training::firstOrCreate(['title' => $title], ['requires_airport' => true]);
        }
    }

    private function seedTenantBusinessData(Tenant $tenant, string $label): void
    {
        $tenant->run(function () use ($label): void {
            if (Client::query()->exists()) {
                return;
            }

            Client::factory()
                ->count(2)
                ->sequence(
                    ['company_name' => "{$label} Société A", 'trade_name' => "{$label} Société A"],
                    ['company_name' => "{$label} Société B", 'trade_name' => "{$label} Société B"],
                )
                ->create()
                ->each(function (Client $client): void {
                    Coworker::factory()->count(2)->create(['client_id' => $client->id]);
                });
        });
    }
}
