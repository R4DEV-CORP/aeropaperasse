<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authorizes the authenticated (central) user against the current tenant.
 *
 * Runs after `auth` and after tenancy has been initialized. REM staff reach any
 * tenant; everyone else needs a `tenant_user` row for the active tenant. A logged-in
 * user who lacks access to the current tenant is never shown a raw 403 or logged out:
 *  - if they belong to at least one other tenant → sent to the tenant chooser, so any
 *    subdomain (notably the universal `app` portal) lets them pick a space they can reach;
 *  - if they belong to no tenant at all → the graceful "no access to this space" page.
 * The effective tenant role is stashed on the request.
 *
 * See docs/multi-tenant-migration.md (Auth model, "No access" state).
 */
class EnsureTenantMembership
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenant = tenant();

        if ($user === null || $tenant === null) {
            return $next($request);
        }

        $tenantId = $tenant->getTenantKey();

        if (! $user->belongsToTenant($tenantId)) {
            return $user->tenants()->exists()
                ? redirect()->route('tenant.choose')
                : redirect()->route('tenant.no-access');
        }

        $request->attributes->set('tenant_role', $user->effectiveRoleFor($tenantId));

        return $next($request);
    }
}
