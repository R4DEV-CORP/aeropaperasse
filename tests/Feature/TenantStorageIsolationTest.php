<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TenantTestCase;

/**
 * Uploaded documents use the `public` disk. Under tenancy the disk root is suffixed to
 * the tenant's storage dir, and tenant files are served through stancl's tenant-asset
 * route (`tenant_asset()`), not the shared `public/storage` symlink. This proves both
 * the write and the serving stay scoped to the current tenant.
 * See docs/multi-tenant-migration.md (tenant-aware infrastructure).
 */
class TenantStorageIsolationTest extends TenantTestCase
{
    public function test_public_disk_writes_and_tenant_asset_serving_are_tenant_scoped(): void
    {
        Storage::disk('public')->put('docs/hello.txt', 'tenant-isolated-content');

        // The file physically lives under the tenant's suffixed storage path.
        $path = str_replace('\\', '/', Storage::disk('public')->path('docs/hello.txt'));
        $this->assertStringContainsString('tenanttest', $path);

        // It is served (tenant-resolved) through the stancl tenant-asset route.
        $this->get(tenant_asset('docs/hello.txt'))->assertOk();
    }
}
