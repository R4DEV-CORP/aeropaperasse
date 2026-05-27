<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Base case for tests that exercise tenant-scoped business models (clients, coworkers,
 * requests, badges…). Those tables live in a tenant database under the multi-DB model,
 * so the test boots a real tenant DB and initializes tenancy: the default connection
 * points at the tenant, while `User`/`Training` stay pinned to central.
 *
 * RefreshDatabase keeps the central schema fresh (transaction-wrapped); the per-test
 * tenant database is created (CreateDatabase + MigrateDatabase pipeline) and dropped.
 * The tenant also gets a domain so HTTP tests (`$this->get(route(...))`) are identified
 * on it by InitializeTenancyByDomain. See docs/multi-tenant-migration.md.
 */
abstract class TenantTestCase extends TestCase
{
    use RefreshDatabase;

    protected const TENANT_DOMAIN = 'tenant.test';

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Drop any leftover tenant DB from a previously crashed run, then provision fresh.
        DB::connection('central')->getPdo()->exec('DROP DATABASE IF EXISTS tenant_test');

        $this->tenant = Tenant::create(['id' => 'test']);
        $this->tenant->domains()->create(['domain' => self::TENANT_DOMAIN]);

        tenancy()->initialize($this->tenant);

        // Route generation + the test request Host resolve to the tenant's domain.
        config(['app.url' => 'http://'.self::TENANT_DOMAIN]);
        URL::forceRootUrl(config('app.url'));
    }

    protected function tearDown(): void
    {
        tenancy()->end();

        if (isset($this->tenant)) {
            $this->tenant->delete();
        }

        parent::tearDown();
    }
}
