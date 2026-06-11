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

            foreach ($this->demoCompanies() as $company) {
                $client = Client::create($company['client']);

                foreach ($company['coworkers'] as $coworker) {
                    Coworker::create([...$coworker, 'client_id' => $client->id]);
                }
            }
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

    /**
     * Static demo business data — no Faker (factories are dev-only and absent under
     * `composer install --no-dev` on preprod).
     *
     * @return list<array{client: array<string, mixed>, coworkers: list<array<string, mixed>>}>
     */
    private function demoCompanies(): array
    {
        return [
            [
                'client' => [
                    'company_name' => 'Démo Société A',
                    'trade_name' => 'Démo Société A',
                    'siret_number' => '12345678',
                    'address' => '1 rue de la Démo',
                    'zip_code' => '95700',
                    'city' => 'Roissy-en-France',
                    'subcontractor_of' => 'REM Distribution',
                    'kbis_document' => 'demo/kbis-societe-a.pdf',
                    'safety_document' => 'demo/safety-societe-a.pdf',
                    'security_document' => 'demo/security-societe-a.pdf',
                    'notification_email' => 'contact@demo-societe-a.fr',
                    'slug' => 'demo-societe-a',
                    'is_airline_company' => false,
                ],
                'coworkers' => [
                    ['firstname' => 'Alice', 'lastname' => 'Martin', 'email' => 'alice.martin@demo-societe-a.fr', 'phone' => '0600000001', 'has_leave' => false, 'departure_date' => null],
                    ['firstname' => 'Bruno', 'lastname' => 'Petit', 'email' => 'bruno.petit@demo-societe-a.fr', 'phone' => '0600000002', 'has_leave' => false, 'departure_date' => null],
                ],
            ],
            [
                'client' => [
                    'company_name' => 'Démo Société B',
                    'trade_name' => 'Démo Société B',
                    'siret_number' => '87654321',
                    'address' => '2 avenue de la Démo',
                    'zip_code' => '95700',
                    'city' => 'Roissy-en-France',
                    'subcontractor_of' => 'REM Distribution',
                    'kbis_document' => 'demo/kbis-societe-b.pdf',
                    'safety_document' => 'demo/safety-societe-b.pdf',
                    'security_document' => 'demo/security-societe-b.pdf',
                    'notification_email' => 'contact@demo-societe-b.fr',
                    'slug' => 'demo-societe-b',
                    'is_airline_company' => true,
                ],
                'coworkers' => [
                    ['firstname' => 'Chloé', 'lastname' => 'Durand', 'email' => 'chloe.durand@demo-societe-b.fr', 'phone' => '0600000003', 'has_leave' => false, 'departure_date' => null],
                    ['firstname' => 'David', 'lastname' => 'Moreau', 'email' => 'david.moreau@demo-societe-b.fr', 'phone' => '0600000004', 'has_leave' => false, 'departure_date' => null],
                ],
            ],
        ];
    }
}
