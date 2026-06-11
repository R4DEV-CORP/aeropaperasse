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
 *  - REM super-admin accounts (cross-tenant, no pivot row needed);
 *  - for each existing demo tenant (rem, client1): a little business data
 *    (2 companies + coworkers) and an Owner account scoped to that tenant, so
 *    cross-tenant isolation can be exercised end-to-end on preprod.
 *
 * No Faker: factories are dev-only and absent under `composer install --no-dev`.
 */
class PreprodSeeder extends Seeder
{
    /**
     * Tenants that should receive demo business data, keyed by tenant id → label.
     */
    private const DEMO_TENANTS = [
        'rem' => 'REM',
        'client1' => 'Client 1',
    ];

    public function run(): void
    {
        $this->seedSharedTrainings();
        $this->seedRemStaff();

        foreach (self::DEMO_TENANTS as $tenantId => $label) {
            $this->seedTenantDemoData($tenantId, $label);
        }
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
     * REM-level staff: the REM role grants cross-tenant access without a pivot row.
     * 2FA disabled so logins aren't blocked while mail (Resend) is being wired —
     * enable it from each account once preprod mail is confirmed.
     */
    private function seedRemStaff(): void
    {
        // corentin: is_new => forced password change on first login.
        $this->upsertUser('corentin.sarda@gmail.com', 'Corentin Sarda', Role::RemSuperAdmin, isNew: true);

        // clement: active account, no forced change, no 2FA.
        $this->upsertUser('clement.richard@r4web.fr', 'Clément Richard', Role::RemSuperAdmin, isNew: false);
    }

    private function seedTenantDemoData(string $tenantId, string $label): void
    {
        $tenant = Tenant::find($tenantId);

        if ($tenant === null) {
            return;
        }

        $tenant->run(function () use ($label): void {
            if (Client::query()->exists()) {
                return;
            }

            foreach ($this->demoCompanies($label) as $company) {
                $client = Client::create($company['client']);

                foreach ($company['coworkers'] as $coworker) {
                    Coworker::create([...$coworker, 'client_id' => $client->id]);
                }
            }
        });

        $firstClientId = $tenant->run(fn (): ?int => Client::query()->orderBy('id')->value('id'));

        $owner = $this->upsertUser("owner.{$tenantId}@aeropaperasse.fr", "Owner {$label}", Role::Client, isNew: false);

        $owner->tenants()->syncWithoutDetaching([
            $tenant->getTenantKey() => [
                'role' => Role::Owner->value,
                'client_id' => $firstClientId,
                'can_access_formation' => true,
            ],
        ]);
    }

    private function upsertUser(string $email, string $name, Role $role, bool $isNew): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'role' => $role->value,
                'can_access_formation' => true,
                'two_factor_enabled' => false,
                'is_new' => $isNew,
            ],
        );
    }

    /**
     * Static demo business data — slugs are unique per tenant DB, so the same
     * companies can be seeded into every tenant without collision.
     *
     * @return list<array{client: array<string, mixed>, coworkers: list<array<string, mixed>>}>
     */
    private function demoCompanies(string $label): array
    {
        $slug = strtolower(str_replace(' ', '-', $label));

        return [
            [
                'client' => [
                    'company_name' => "{$label} Société A",
                    'trade_name' => "{$label} Société A",
                    'siret_number' => '12345678',
                    'address' => '1 rue de la Démo',
                    'zip_code' => '95700',
                    'city' => 'Roissy-en-France',
                    'subcontractor_of' => 'REM Distribution',
                    'kbis_document' => 'demo/kbis-a.pdf',
                    'safety_document' => 'demo/safety-a.pdf',
                    'security_document' => 'demo/security-a.pdf',
                    'notification_email' => "contact-a@{$slug}.fr",
                    'slug' => "{$slug}-societe-a",
                    'is_airline_company' => false,
                ],
                'coworkers' => [
                    ['firstname' => 'Alice', 'lastname' => 'Martin', 'email' => "alice.martin@{$slug}-a.fr", 'phone' => '0600000001', 'has_leave' => false, 'departure_date' => null],
                    ['firstname' => 'Bruno', 'lastname' => 'Petit', 'email' => "bruno.petit@{$slug}-a.fr", 'phone' => '0600000002', 'has_leave' => false, 'departure_date' => null],
                ],
            ],
            [
                'client' => [
                    'company_name' => "{$label} Société B",
                    'trade_name' => "{$label} Société B",
                    'siret_number' => '87654321',
                    'address' => '2 avenue de la Démo',
                    'zip_code' => '95700',
                    'city' => 'Roissy-en-France',
                    'subcontractor_of' => 'REM Distribution',
                    'kbis_document' => 'demo/kbis-b.pdf',
                    'safety_document' => 'demo/safety-b.pdf',
                    'security_document' => 'demo/security-b.pdf',
                    'notification_email' => "contact-b@{$slug}.fr",
                    'slug' => "{$slug}-societe-b",
                    'is_airline_company' => true,
                ],
                'coworkers' => [
                    ['firstname' => 'Chloé', 'lastname' => 'Durand', 'email' => "chloe.durand@{$slug}-b.fr", 'phone' => '0600000003', 'has_leave' => false, 'departure_date' => null],
                    ['firstname' => 'David', 'lastname' => 'Moreau', 'email' => "david.moreau@{$slug}-b.fr", 'phone' => '0600000004', 'has_leave' => false, 'departure_date' => null],
                ],
            ],
        ];
    }
}
