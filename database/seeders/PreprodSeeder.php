<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\Tenant;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Preprod baseline seed (fresh install). Idempotent. Run AFTER provisioning the
 * tenants:
 *   php artisan tenant:create rem dev.aeropaperasse.fr --name="REM Distribution"
 *   php artisan tenant:create client1 client1.dev.aeropaperasse.fr --name="Client 1 (démo)"
 *   php artisan db:seed --class=PreprodSeeder --force
 *
 * Seeds:
 *  - the real shared training catalog (central, from database/data/trainings.php);
 *  - one REM super-admin (cross-tenant) — login then change the password (is_new);
 *  - if the `client1` demo tenant exists: a little business data + an Owner account,
 *    so cross-tenant isolation can be exercised end-to-end on preprod.
 *
 * Unlike PocMultitenantSeeder this carries no `.test` demo matrix — REM is the real
 * tenant (its business data is loaded later), client1 is a throwaway demo.
 */
class PreprodSeeder extends Seeder
{
    private const REM_ADMIN_EMAIL = 'corentin.sarda@gmail.com';

    private const DEMO_TENANT_ID = 'client1';

    public function run(): void
    {
        $this->seedSharedTrainings();
        $this->seedRemSuperAdmin();
        $this->seedDemoTenant();
    }

    private function seedSharedTrainings(): void
    {
        $catalog = require database_path('data/trainings.php');

        foreach ($catalog as $entry) {
            Training::firstOrCreate(
                ['title' => $entry['title']],
                ['requires_airport' => $entry['requires_airport']],
            );
        }
    }

    /**
     * REM super-admin: REM-level role grants cross-tenant access without a pivot row.
     * 2FA disabled so the first login isn't blocked if mail (Resend) isn't wired yet —
     * enable it from the account once preprod mail is confirmed. is_new forces a
     * password change on first login.
     */
    private function seedRemSuperAdmin(): void
    {
        User::updateOrCreate(
            ['email' => self::REM_ADMIN_EMAIL],
            [
                'name' => 'Corentin Sarda',
                'password' => 'password',
                'role' => Role::RemSuperAdmin->value,
                'can_access_formation' => true,
                'two_factor_enabled' => false,
                'is_new' => true,
            ],
        );
    }

    private function seedDemoTenant(): void
    {
        $tenant = Tenant::find(self::DEMO_TENANT_ID);

        if ($tenant === null) {
            return;
        }

        $tenant->run(function (): void {
            if (Client::query()->exists()) {
                return;
            }

            Client::factory()
                ->count(2)
                ->sequence(
                    ['company_name' => 'Démo Société A', 'trade_name' => 'Démo Société A'],
                    ['company_name' => 'Démo Société B', 'trade_name' => 'Démo Société B'],
                )
                ->create()
                ->each(function (Client $client): void {
                    Coworker::factory()->count(2)->create(['client_id' => $client->id]);
                });
        });

        $firstClientId = $tenant->run(fn (): ?int => Client::query()->orderBy('id')->value('id'));

        $owner = User::updateOrCreate(
            ['email' => 'owner@client1.dev.aeropaperasse.fr'],
            [
                'name' => 'Owner Démo',
                'password' => 'password',
                'role' => Role::Client->value,
                'can_access_formation' => true,
                'two_factor_enabled' => false,
                'is_new' => true,
            ],
        );

        $owner->tenants()->syncWithoutDetaching([
            $tenant->getTenantKey() => [
                'role' => Role::Owner->value,
                'client_id' => $firstClientId,
                'can_access_formation' => true,
            ],
        ]);
    }
}
