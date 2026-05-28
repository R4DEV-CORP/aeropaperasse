<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| This application registers ALL its routes in routes/web.php, which is already
| wrapped in the tenancy middleware (InitializeTenancyByDomain + PreventAccess-
| FromCentralDomains). This file is intentionally left without routes: stancl's
| default placeholder `GET /` route was removed because it shadowed the real `/`
| route in web.php (it is registered later, so it overwrote it).
|
| Add tenant-only routes here only if they must NOT also resolve on the central
| domain. See docs/multi-tenant-migration.md (Routing).
|
*/
