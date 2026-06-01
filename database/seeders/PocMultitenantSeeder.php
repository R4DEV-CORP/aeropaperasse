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
 * POC multi-tenant demo data. Idempotent. Run with:
 *   php artisan db:seed --class=PocMultitenantSeeder
 *
 * Seeds a full role matrix for browser/Playwright testing of the multi-tenant
 * migration. Every role/tenant combination has a dedicated account so we can
 * verify routing, sidebar, permissions and tenant isolation end-to-end.
 *
 * Accounts (password: `password` for all):
 *
 *  REM-level (cross-tenant, no pivot row — REM short-circuit grants access):
 *   - rem-admin@aeropaperasse.test          rem_super_admin
 *   - rem-admin-only@aeropaperasse.test     rem_admin
 *
 *  Tenant REM (app.aeropaperasse.test) — pivot rows on tenant `rem`:
 *   - owner@rem.test                        owner
 *   - tenant-admin@rem.test                 tenant_admin
 *   - aclient@rem.test                      aclient        (attached to Société A)
 *   - sclient@rem.test                      sclient        (attached to Société A)
 *   - client@rem.test                       client         (attached to Société A)
 *
 *  Tenant C1 (client1.aeropaperasse.test) — pivot rows on tenant `c1`:
 *   - owner@client1.test                    owner
 *   - tenant-admin@client1.test             tenant_admin
 *   - aclient@client1.test                  aclient        (attached to Société A)
 *   - sclient@client1.test                  sclient        (attached to Société A)
 *   - client@client1.test                   client         (attached to Société A)
 *
 *  Multi-tenant:
 *   - multi@aeropaperasse.test              sclient on REM + client on C1
 *
 * 2FA disabled + is_new=false on these accounts so local browser testing isn't
 * blocked by the email code (MAIL_MAILER=log) or the forced first-login
 * password change — this is demo data, not a security baseline.
 *
 * See docs/multi-tenant-migration.md (POC scope, Roles).
 */
class PocMultitenantSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSharedTrainings();

        $rem = Tenant::findOrFail('rem');
        $c1 = Tenant::findOrFail('c1');

        // Friendly display names (stored on the tenant's `data` column) — shown in the tenant chooser.
        $rem->update(['name' => 'REM Distribution']);
        $c1->update(['name' => 'Client 1']);

        $this->seedTenantBusinessData($rem, 'REM');
        $this->seedTenantBusinessData($c1, 'Client1');

        // REM-level staff. The `role` column carries the REM role; no pivot row needed —
        // the REM short-circuit in EnsureTenantMembership grants access to every tenant.
        $this->createRemStaff('rem-admin@aeropaperasse.test', 'REM Super Admin', Role::RemSuperAdmin->value);
        $this->createRemStaff('rem-admin-only@aeropaperasse.test', 'REM Admin', Role::RemAdmin->value);

        // Tenant-level users. They carry the legacy `users.role = client` as a sane fallback
        // (the column is non-null) — their *effective* role comes from the tenant_user pivot.
        $this->seedTenantRoleAccounts($rem, 'rem.test', 'REM');
        $this->seedTenantRoleAccounts($c1, 'client1.test', 'Client1');

        // Multi-tenant user — sclient on REM + client on C1. Used to verify contextualRole()
        // returns the correct role per active tenant in the browser.
        $this->seedMultiTenantUser($rem, $c1);
    }

    private function seedSharedTrainings(): void
    {
        // Catalogue extrait du dump pré-multi-tenant (database/data/trainings.php).
        // Idempotent via firstOrCreate sur le titre — re-seed ne duplique pas.
        $catalog = require database_path('data/trainings.php');

        foreach ($catalog as $entry) {
            Training::firstOrCreate(
                ['title' => $entry['title']],
                ['requires_airport' => $entry['requires_airport']],
            );
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

    private function createRemStaff(string $email, string $name, string $role): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'role' => $role,
                'can_access_formation' => true,
                'two_factor_enabled' => false,
                'is_new' => false,
            ],
        );
    }

    /**
     * Seed one account per tenant-level role on the given tenant. Client-scoped roles
     * (aclient/sclient/client) are linked to the tenant's first company via the pivot's
     * client_id, so company-scoped UI has something coherent to display.
     */
    private function seedTenantRoleAccounts(Tenant $tenant, string $emailDomain, string $tenantLabel): void
    {
        $firstClientId = $tenant->run(fn (): ?int => Client::query()->orderBy('id')->value('id'));

        $accounts = [
            ['owner', "Owner {$tenantLabel}", Role::Owner->value, null],
            ['tenant-admin', "Admin tenant {$tenantLabel}", Role::TenantAdmin->value, null],
            ['aclient', "AClient {$tenantLabel}", Role::AClient->value, $firstClientId],
            ['sclient', "SClient {$tenantLabel}", Role::SClient->value, $firstClientId],
            ['client', "Client {$tenantLabel}", Role::Client->value, $firstClientId],
        ];

        foreach ($accounts as [$prefix, $name, $tenantRole, $clientId]) {
            $user = User::updateOrCreate(
                ['email' => "{$prefix}@{$emailDomain}"],
                [
                    'name' => $name,
                    'password' => 'password',
                    'role' => Role::Client->value,
                    'can_access_formation' => true,
                    'two_factor_enabled' => false,
                    'is_new' => false,
                ],
            );

            $user->tenants()->syncWithoutDetaching([
                $tenant->getTenantKey() => [
                    'role' => $tenantRole,
                    'client_id' => $clientId,
                    'can_access_formation' => true,
                ],
            ]);
        }
    }

    private function seedMultiTenantUser(Tenant $rem, Tenant $c1): void
    {
        $remClientId = $rem->run(fn (): ?int => Client::query()->orderBy('id')->value('id'));
        $c1ClientId = $c1->run(fn (): ?int => Client::query()->orderBy('id')->value('id'));

        $user = User::updateOrCreate(
            ['email' => 'multi@aeropaperasse.test'],
            [
                'name' => 'Multi-Tenant User',
                'password' => 'password',
                'role' => Role::Client->value,
                'can_access_formation' => true,
                'two_factor_enabled' => false,
                'is_new' => false,
            ],
        );

        $user->tenants()->syncWithoutDetaching([
            $rem->getTenantKey() => [
                'role' => Role::SClient->value,
                'client_id' => $remClientId,
                'can_access_formation' => true,
            ],
            $c1->getTenantKey() => [
                'role' => Role::Client->value,
                'client_id' => $c1ClientId,
                'can_access_formation' => true,
            ],
        ]);
    }
}
