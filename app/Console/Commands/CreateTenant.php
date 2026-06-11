<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Domain;

/**
 * Provisions a new tenant reproducibly: creates the tenant row, which fires
 * TenantCreated → CreateDatabase + MigrateDatabase synchronously (see
 * App\Providers\TenancyServiceProvider), so the tenant database and its schema
 * exist when this command returns. Then attaches the hosting domain.
 *
 * Replaces the manual tinker flow used during the POC. Business data is seeded
 * separately (db:seed). See docs/multi-tenant-migration.md (Tenant provisioning).
 */
class CreateTenant extends Command
{
    /**
     * @var string
     */
    protected $signature = 'tenant:create
        {id : The tenant identifier (e.g. rem, client1)}
        {domain : The host that resolves this tenant (e.g. dev.aeropaperasse.fr)}
        {--name= : Friendly display name shown in the tenant chooser}';

    /**
     * @var string
     */
    protected $description = 'Provisionne un tenant (création de la base + migrations) et lui attache un domaine';

    public function handle(): int
    {
        $id = $this->argument('id');
        $domain = $this->argument('domain');
        $name = $this->option('name') ?: $id;

        if (Tenant::find($id) !== null) {
            $this->error("Le tenant « {$id} » existe déjà.");

            return self::FAILURE;
        }

        if (Domain::query()->where('domain', $domain)->exists()) {
            $this->error("Le domaine « {$domain} » est déjà attribué à un tenant.");

            return self::FAILURE;
        }

        $this->info("Création du tenant « {$id} » (base tenant_{$id} + migrations)…");

        $tenant = Tenant::create([
            'id' => $id,
            'name' => $name,
        ]);

        $tenant->domains()->create(['domain' => $domain]);

        $this->info("Tenant « {$id} » provisionné et accessible sur https://{$domain}.");

        return self::SUCCESS;
    }
}
