<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * Sanctum's token table (`personal_access_tokens`) lives in the central DB.
 * Pinning the model to the `central` connection avoids the default-connection
 * fallback to the active tenant, which 500s with
 * `Base table not found: tenant_<id>.personal_access_tokens`.
 * See docs/multi-tenant-migration.md (Cross-database gotchas).
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $connection = 'central';
}
