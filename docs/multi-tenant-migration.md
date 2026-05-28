# Multi-Tenant Migration

This document tracks the migration from a single-instance app (`app.aeropaperasse.fr`) to a multi-tenant architecture where additional clients can run on their own subdomain (`client1.aeropaperasse.fr`, `client2.aeropaperasse.fr`, ...) on the same codebase. Some clients use a dedicated white-label subdomain; regular clients keep using `app.aeropaperasse.fr`.

It is the **source of truth** for architectural decisions, open questions, and the migration roadmap. Update it as decisions are taken or context changes — so any Claude session (or human contributor) can pick the work up.

---

## Status

- **Current phase:** POC (local, throwaway-safe)
- **Branch:** `poc-multitenant-l13` (created from `multitenant`)
- **Framework:** Laravel **13.11.2** (the codebase was bumped 10→11→12→13 since this doc was first written). Note: only the framework *version* was upgraded — the application **keeps the Laravel 10-style skeleton** (`app/Http/Kernel.php` and `app/Console/Kernel.php` still present; `bootstrap/app.php` is the old-style bootstrapper, not the L11+ config-driven `Application::configure()` builder). Middleware registration (e.g. `RoleMiddleware`) therefore still lives in `Http/Kernel.php`, and the L10-specific notes elsewhere in this doc remain valid.
- **Last updated:** 2026-05-28

### Resume here (handoff) {#resume-here}

**Where we stopped:** the POC is functionally complete and the **full test suite is green (67/67)** under multi-tenancy. Everything below works locally except the manual DNS/vhost step (#5), which is the only thing left before browser end-to-end testing.

**Done:**
- `stancl/tenancy` v3.10 installed & configured; central connection `central` → `aeropaperasse_central`; tenant DB naming `tenant_<id>`; custom `App\Models\Tenant` (multi-DB).
- Migrations partitioned: **10 central / 36 tenant** (`database/migrations/tenant/`); all cross-DB FKs to `users`/`trainings` removed (columns kept as plain ints).
- Central pivot `tenant_user` (`user_id`, `tenant_id`, `role`, nullable `client_id`).
- Central auth + per-tenant authorization: `EnsureTenantMembership` middleware (REM `admin`/`sadmin` bypass → all tenants; otherwise `tenant_user` lookup; otherwise redirect to `tenant.no-access`). Web routes wrapped in `InitializeTenancyByDomain` + `PreventAccessFromCentralDomains`. `no-access` page is `pages/auth/⚡no-access.blade.php`.
- Sessions on central, central-pinned models, `exists:central.*` rules, `User::newRelatedInstance()` override — see [Cross-database gotchas](#poc-gotchas).
- POC seeder `PocMultitenantSeeder` + tenant test harness `Tests\TenantTestCase`; all 8 business test files converted.

**Immediate next step — finish #5 (DNS/vhost), then browser test:**
1. Add to `C:\Windows\System32\drivers\etc\hosts` (admin): `127.0.0.1 app.aeropaperasse.test` and `127.0.0.1 client1.aeropaperasse.test`.
2. Reload Apache in Laragon (vhost `C:\laragon\etc\apache2\sites-enabled\aeropaperasse-multitenant.conf` is already in place; `ServerAlias *.aeropaperasse.test` → project `public/`).
3. Browse `http://app.aeropaperasse.test` and `http://client1.aeropaperasse.test`.

**Environment notes for resuming:**
- Use Laragon's PHP 8.3 for artisan: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe` (a stray PHP 8.5 shadows the PATH).
- Rebuild local state from scratch if needed: drop `tenant_rem`/`tenant_c1`/`tenant_test`, `php artisan migrate:fresh`, recreate the two tenants (ids `rem` → `app.aeropaperasse.test`, `c1` → `client1.aeropaperasse.test`), then `php artisan db:seed --class=PocMultitenantSeeder`.
- **Running the test suite resets `aeropaperasse_central`** (RefreshDatabase) — re-seed afterwards.
- Seeded accounts (password `password`): `rem-admin@aeropaperasse.test` (REM super-admin, reaches both subdomains) and `owner@client1.test` (Owner of C1 only; rejected on `app.*` → no-access page).

**Git state:** this work is uncommitted on branch `poc-multitenant-l13`. It also exists as two "Initial commit" commits on `dev`/`origin/dev` (an accidental branch-switch committed it there); cleaning `dev` was deliberately deferred (would rewrite published history). Decide commit strategy before pushing.

**Then continue with:** the [Roadmap after POC](#roadmap-after-poc).

**Update (2026-05-28) — Q-ROLES partially done + password broker pinned:**
- Password broker pinned to central (`config/auth.php` → `passwords.users.connection = 'central'`).
- Role values finalized in `App\Enums\Role` and the stored REM values renamed (central data migration `2026_05_28_000001_rename_rem_role_values`: `sadmin → rem_super_admin`, `admin → rem_admin`; tenant values conserved). All `'admin'`/`'sadmin'` literals across app code, views, validators, seeders and tests were updated; `aclient` was added to the role taxonomy (kept as-is).
- A typed contextual accessor `User::effectiveRole(?tenantId): ?Role` was added; the global helpers (`isAdmin()` etc.) were kept (now enum-based) and **call-site migration to the contextual role remains progressive** ([Q-ROLES](#q-roles)).
- Verified: full suite **green**; tinker smoke confirms `rem_super_admin` reaches both tenants and the C1 `owner` is pivot-resolved and rejected on `app.*`.
- **Caveat:** the raw SQL dumps (`dump.sql`, `database/dump.sql`) still contain pre-rename role values — they predate the rename and are not part of the `migrate:fresh` + seed flow; restoring from them would need a manual role fix.

**Update (2026-05-28) — scheduler tenant-awareness (roadmap item 3):**
- Both expiry commands now run **per tenant** (`Tenant::all()->each->run(...)`): `NotifyBadgeExpiry` (tenant `badges`) and `NotifyTrainingExpiry` (rewritten onto tenant `coworker_trainings`; `TrainingExpiryNotification` now takes a `CoworkerTraining`). New tests `NotifyBadgeExpiryTest` + `NotifyTrainingExpiryTest` (2 tenants each). Full suite **71/71 green**.
- Audit findings recorded under [Roadmap → item 3](#roadmap-after-poc): queues N/A (no jobs), Scout auto-isolated (database driver).

**Update (2026-05-28) — universal login + tenant chooser:**
- A logged-in user who lacks access to the current tenant but belongs to others is now sent to a **tenant chooser** (`/choose-tenant`) instead of the no-access page; users with zero tenants still get no-access. `app.*` is the universal portal. See [Universal login + tenant chooser](#universal-login--tenant-chooser-decided-implemented). New `tests/Feature/TenantChooserTest`; full suite **77/77 green**; verified end-to-end in the browser.
- Two integration bugs fixed along the way (see [gotchas](#poc-gotchas)): stancl's placeholder `GET /` in `routes/tenant.php` shadowed the app's `/`; and `User::tenants()` referenced the base stancl Tenant (no `domains` relation) instead of `App\Models\Tenant`.
- Local browser testing: Vite dev server CORS fixed in `vite.config.js`; POC seeder accounts now have 2FA + first-login-password-change disabled.
- **Chooser UX:** dedicated `layouts::select`, a live search box, and tenant **names** (on the tenant `data` column) instead of raw links. For **REM staff** the chooser lists **all** tenants; they reach it via a **"Changer d'espace"** link in the app sidebar (shown to REM or any user with ≥2 tenants). Full suite **79/79 green**; verified end-to-end in the browser.

**Update (2026-05-28) — storage/PDF/mail tenant-awareness (roadmap item 3, done):** uploads auto-isolate to the tenant dir (public-disk root override); live views now serve documents via `tenant_asset()` instead of the central `public/storage` symlink; the overview PDF streams tenant-scoped data; mail sends in tenant context. New `tests/Feature/TenantStorageIsolationTest`; full suite **80/80 green**. Details under [Roadmap → item 3](#roadmap-after-poc).

**Update (2026-05-28) — Q-ROLES contextual role checks (roadmap item 1, done):** tenant-level helpers (`isClient`/`isSClient`/`isAClient`) now read `User::contextualRole()` (active-tenant pivot role, falling back to the global `users.role` column), so a multi-tenant user gets the right role per tenant; REM helpers stay global; `RoleMiddleware` reworked too. Non-breaking (fallback) and done without rewriting the ~236 call-sites. New `tests/Feature/TenantRoleContextTest`; full suite **83/83 green**. See [Q-ROLES](#q-roles).

**Update (2026-05-28) — role display + a cross-DB bug it surfaced:** role **badges** now show the active-tenant role (`contextualRole()` in the coworker/company views). Verifying this revealed a **pre-existing tenancy bug**: cross-DB `whereHas('user'/'training', …)` 500'd `coworkers/index` (always, via `statistics()`) and the `companies/trainings-list` search. Fixed for the **live** pages with the central-id-list pattern (see [gotchas](#poc-gotchas)); legacy API left as-is. New `tests/Feature/CoworkerRoleDisplayTest`; full suite **85/85 green**.

---

## Motivation & business context

Today `app.aeropaperasse.fr` is operated by **REM**. Airport client companies submit badge/activity/vehicle requests on the site, and REM staff process them and forward the files to **ADP** (the airport authority). ADP does not interact with the site.

Some new clients want to **use the platform to manage their own data and requests, but submit to ADP themselves** (white-label use case). They need their own isolated space — separate data, separate URL.

Constraints:
- `app.aeropaperasse.fr` keeps running indefinitely (REM tenant) alongside the new subdomains.
- **One single codebase** must serve both `app.*` and `client*.*` — no fork.
- Volume short-term: < 10 tenants. Long-term: self-service signup with paid plans.
- REM staff must be able to **access all tenants** with their own accounts (cross-tenant access).
- A single user account can be granted access to **several tenants**, potentially with a **different role on each**.

---

## Architecture decision

### Approach: multi-database tenancy via `stancl/tenancy`

Each tenant gets its **own MySQL database**. A central database holds the tenant registry, the **shared user directory**, and shared catalogs.

**Why multi-DB rather than single-DB with `tenant_id` column:**

- True isolation: no risk of data leak via a forgotten query scope.
- Per-tenant backups and restore — useful commercially ("your data, your database") and for RGPD.
- Provisioning a new tenant = create a DB + run migrations → automatable, natural path to self-service.
- Existing data migrates cleanly: the current production database becomes the **central database** (it already holds users, trainings and the REM-tenant business data; the business tables are then carved into the REM tenant DB).

**Cost / complexity accepted:**

- Every queue job, command, scheduler entry must be tenant-aware (`stancl/tenancy` provides the helpers, but it needs discipline).
- Two migration directories: `database/migrations/` (central) and `database/migrations/tenant/` (per-tenant).
- File storage isolation per tenant (`storage/app/tenant{id}/...`) instead of a single shared bucket.
- Tenant tables that reference a user can no longer use a DB-level foreign key (the `users` table lives in another database) — see [Cross-database references](#cross-database-references).

### Routing

- Wildcard DNS: `*.aeropaperasse.fr` → server.
- Wildcard SSL: Let's Encrypt DNS-01 challenge (Cloudflare/Route53 — to confirm based on the current DNS provider).
- Single Forge site catches both `app.aeropaperasse.fr` and `*.aeropaperasse.fr`.
- Middleware `InitializeTenancyByDomain` resolves the tenant from the host on every web request.

---

## Data partition (central vs tenant)

| Database | Tables | Notes |
|---|---|---|
| **Central** | `tenants`, `domains` | `stancl/tenancy` registry |
| **Central** | `users` | **All** users — REM staff *and* tenant users. Single directory (see [Auth model](#auth-model)). |
| **Central** | `tenant_user` (pivot) | Links a user to a tenant **with a role for that tenant**. A user can appear on several tenants with different roles. |
| **Central** | `trainings` | Training catalog is **shared across all tenants** |
| **Tenant** | `clients` | Companies served by this tenant |
| **Tenant** | `coworkers` | Employees of those companies |
| **Tenant** | `activity_requests`, `badge_requests`, `vehicle_passes`, `badges` | All request workflows |
| **Tenant** | `coworker_training` (pivot, eventually) | Per-tenant assignment to the shared `trainings` catalog |

Anything else added later (subscriptions, billing, tenant settings) lives **central**.

### Cross-database references

Tenant tables historically reference the current `users` table (`coworker.user_id`, `client.user_id`, `*_request.user_id`, ...). Once `users` lives in the central DB and these tables live in tenant DBs, **MySQL cannot enforce a foreign key across databases**.

Decision: keep the `user_id` columns as **plain unsigned integers without a FK constraint**; integrity is enforced in application code (and these always resolve to a central user). Cascade/orphan behaviour on user deletion must be handled explicitly (observer or service), not by the database. See [Q-FK](#q-fk).

**Loading user data across the boundary — decided: Option A (keep the relation).** The `User` model is **pinned to the central connection**; tenant models keep their `user()` relation, but it resolves across connections, so a classic `with('user')` eager-load is not available. Instead, load users **by batch** (collect the `user_id`s of a result set and fetch them in one `User::whereIn('id', ...)` query, keyed by id) on the list screens that display user info. This keeps a **single source of truth** for users (no duplication). The rejected alternative (Option B) was denormalising display fields (name, email) into tenant tables — only worth it on a very hot read path, at the cost of keeping copies in sync.

### Search isolation (Scout) — decided

Tenant data must be **strictly isolated in search**: a search performed in tenant A's context returns only tenant A's records. With the multi-DB model this is achieved by **prefixing the Scout index name per tenant** — each tenant-scoped `Searchable` model resolves `searchableAs()` to a tenant-specific index (e.g. `tenant{id}_coworkers`), via `searchableAs()` override or the `stancl/tenancy` Scout integration. The shared `trainings` catalog keeps a **single central index** (it is common to all tenants by design). To do at implementation: audit which models carry the `Searchable` trait and apply the per-tenant prefix to the tenant-scoped ones. Index (re)building must run per tenant.

---

## Roles

Two role levels, decided:

### REM level (central, cross-tenant)

REM staff. These roles grant access to **every tenant** (present and future).

| Display name | Replaces | Enum value (`App\Enums\Role`) |
|---|---|---|
| **Super administrateur REM** | old `sadmin` | `rem_super_admin` |
| **Administrateur REM** | old `admin` | `rem_admin` |

### Tenant level (scoped to one tenant, carried by the `tenant_user` pivot)

| Display name | Origin | Enum value (`App\Enums\Role`) |
|---|---|---|
| **Owner** | **new** role | `owner` |
| **Administrateur** | **new** role | `tenant_admin` |
| **AClient** | conserved as-is | `aclient` |
| **sclient** | conserved as-is | `sclient` |
| **client** | conserved as-is | `client` |

The **role lives on the `tenant_user` pivot**, not on the user row — so the same user can be `Owner` on tenant A and `client` on tenant B.

`Owner` and `Administrateur` are **new** tenant-level roles, assigned manually after the migration. No existing row is auto-converted into them.

`AClient` is a pre-existing tenant-level role: an `sclient` with one extra right — creating badges **within the scope of its own company**. Kept as-is for both `app.aeropaperasse` and the new tenants; it may be renamed later (machine value stays `aclient` for now). It was absent from the earlier draft of these tables.

> **Implementation note (status):** the canonical role values now live in the `App\Enums\Role` backed enum, and the stored `users.role` REM values were renamed via a central data migration (`2026_05_28_000001_rename_rem_role_values`: `sadmin → rem_super_admin`, `admin → rem_admin`; tenant values unchanged). The existing global helpers `isAdmin()` / `isSAdmin()` / `isClient()` / `isSClient()` / `isAClient()` were kept (now compare against the enum) and a **typed contextual accessor** `User::effectiveRole(?tenantId): ?Role` was added (REM role short-circuits; otherwise the `tenant_user` pivot role for the active tenant). **Still to do (progressive):** migrate the ~236 call-sites that read the *global* helpers over to the contextual `effectiveRole()` where they should depend on the active tenant, and rework `RoleMiddleware` (`role:admin,sadmin`, legacy API only) accordingly.

---

## Auth model

A **single central user directory** — there is no longer a split between "central users" and "tenant users". Access and role are derived from the `tenant_user` pivot (plus the REM-level roles).

| Concept | Where | Notes |
|---|---|---|
| User identity & credentials | `central.users` | One row per person, one email, one password. |
| Tenant membership + role | `central.tenant_user` | One row per (user, tenant) with the tenant-level role. |
| REM cross-tenant capability | REM-level role | Grants access to all tenants — see provisioning note below. |

**Login flow on any subdomain:**

1. Resolve the tenant from the host (`InitializeTenancyByDomain`).
2. Authenticate the credentials against the **central** `users` table (single guard).
3. Authorize access to the current tenant:
   - **REM-level role** → access granted to any tenant.
   - Otherwise → look up `tenant_user` for `(user, current tenant)`. A row → access granted with **that** role. No row → access denied for this subdomain.
4. The **effective role** for the request is the REM role (if REM) or the pivot role for the current tenant. All authorization checks use this contextual role.

**REM cross-tenant access & provisioning:** to honour the "role per tenant via pivot" model literally, provisioning a new tenant **auto-creates `tenant_user` rows for all REM staff** so they keep cross-tenant access. At self-service scale this fan-out may be replaced by treating REM roles as a global user attribute that bypasses the pivot — see [Q-REM](#q-rem).

### Guard & session (decided)

- **Auth guard on the central connection.** `stancl/tenancy` switches the default DB connection to the tenant in tenant context, but `users` is central. The auth provider (and `password_reset_tokens`, Sanctum tokens) must therefore be **pinned to the central connection** explicitly, so login always queries the central directory regardless of the current tenant. **Done:** the password broker is pinned via `config/auth.php` → `passwords.users.connection = 'central'` (the `password_reset_tokens` migration already lives in central). Sanctum `personal_access_tokens` is **not yet pinned** (API legacy, non-blocking).
- **Shared login across subdomains.** Set `SESSION_DOMAIN=.aeropaperasse.fr` so the session cookie is shared across `app.*` and `client*.*` — a user logged in on one subdomain stays logged in on the others. The **session store must live outside the tenant switch** (central connection / Redis / file), otherwise moving subdomain would change the session table and drop the session.
- **Shared login ≠ shared access.** The session is shared, but every subdomain re-runs the per-tenant authorization (step 3 above). A user who is not a member of the current tenant stays authenticated but is sent to the **"no access to this space"** screen (see below).

### Universal login + tenant chooser (decided, implemented)

Any subdomain authenticates against the central directory, so **anyone can log in anywhere** — `app.*` acts as the universal portal. What differs is where a logged-in user goes when they **lack access to the tenant they landed on** (no `tenant_user` row and not REM staff):

- **They belong to ≥1 other tenant** → redirected to a **tenant chooser** (`/choose-tenant`, route `tenant.choose`) that lists their tenants with a link to each one's subdomain. The session cookie is shared across `*.aeropaperasse.*`, so picking one keeps them authenticated on arrival. Example: a C1-only user logging in on `app.*` is sent to the chooser and picks C1; on `client2.*` they get the chooser (listing C1) and never see C2's data.
- **They belong to no tenant at all** → the graceful **"no access to this space"** page (`tenant.no-access`).

REM staff reach every tenant directly (short-circuit), so the gate never *redirects* them to the chooser — but when they open it (`/choose-tenant`), it lists **all** tenants (`Tenant::all()`), since they manage every space, rather than a pivot-scoped list. REM staff keep landing on `app` after login (unchanged); they reach the chooser on demand via a **"Changer d'espace"** link in the app sidebar (`layouts::app`). That link is shown to anyone who can reach more than one space — REM staff, or a user with ≥2 `tenant_user` rows. Both chooser/no-access pages are full Livewire pages under `pages/auth/`, built from `x-ui.*` primitives, and live **outside** the `tenant.member` gate (with `auth` only) to avoid redirect loops. The logic lives in `EnsureTenantMembership`. The `/` route is a smart entry: authenticated → the user's landing (`UserRedirectService`), guest → login — so the chooser's links to each tenant's `/` land inside the app.

**Chooser UI:** the no-access page stays on `layouts::auth`, but the chooser uses its own **dedicated layout `layouts::select`** (a wide, full-page layout — not `app`/`auth`) because a user may belong to many tenants. It shows a **live search box** (`wire:model.live` + a `#[Computed]` filter over the in-memory list) and renders each tenant as a card showing its **friendly name** (with the domain as a subtitle), not a raw link. Tenant names are stored on the tenant's `data` column (`$tenant->name`, set by the seeder / at provisioning); the chooser falls back to the domain when no name is set.

---

## Migration of existing data

The current single database becomes the **central** database. Roles map as follows:

| Current `users.role` | Becomes | Lands in |
|---|---|---|
| `sadmin` | **Super administrateur REM** | stays in `central.users` as a REM-level role |
| `admin` | **Administrateur REM** | stays in `central.users` as a REM-level role |
| `sclient` | `sclient` (unchanged) | `central.users` + a `tenant_user` row for the **REM tenant** |
| `client` | `client` (unchanged) | `central.users` + a `tenant_user` row for the **REM tenant** |

The current business data (`clients`, `coworkers`, `*_requests`, `badges`, ...) is carved out into the **REM tenant database**. The `trainings` catalog stays central. `Owner` / `Administrateur` tenant roles are assigned manually afterwards where needed.

### Production continuity & data preservation (INVIOLABLE)

`app.aeropaperasse.fr` is **live production for REM** and must keep running with **zero data loss**. The current production database holds the only copy of REM's business data — the migration must treat it as such. The POC never touches it (it runs against throwaway local databases); the rules below govern the real cutover (roadmap item 2).

The carve-out is **copy-then-verify-then-drop**, never an in-place destructive transform:

1. **Full backup** of the production database, restorable independently, taken immediately before cutover.
2. **Freeze writes** for the cutover window (maintenance / read-only mode) so the copy is consistent and nothing is written mid-migration.
3. The production database becomes the **central** database: it already holds `users` and `trainings`; we add the tenancy registry (`tenants`, `domains`, `tenant_user`) and back-fill the REM `tenant_user` rows.
4. **Copy** the business tables (`clients`, `coworkers`, `*_requests`, `badges`, ...) into the freshly provisioned `tenant_rem` database.
5. **Verify** `tenant_rem` against the source: row counts per table, key relationships, spot-checks on documents/files. The business tables in central are **dropped only after** this verification passes.
6. **Rollback plan:** until the drop in step 5, central still contains the original business tables, so aborting = restore writes and point `app.*` back. After the drop, rollback = restore the step-1 backup.

Validate the whole sequence on a **staging copy of production** before doing it for real.

---

## Open questions

### Q2 — Where do super-admins land after login?

When a REM super-admin logs in on `app.aeropaperasse.fr`, they see REM tenant data. When they log in on `client1.aeropaperasse.fr`, they see C1 data. Fine. But:
- Is there a central admin landing (e.g. `admin.aeropaperasse.fr`) that lists all tenants and lets a super-admin pick one?
- Or does it stay subdomain-driven only (REM staff bookmark each subdomain)?

A "switch tenant" UI is the natural home for this once a user can belong to several tenants. **Addressed:** the [tenant chooser](#universal-login--tenant-chooser-decided-implemented) handles both cases — non-REM users who land on a tenant they can't access (lists their tenants), and **REM staff** who open it via the **"Changer d'espace"** sidebar link (lists *all* tenants). Decided: REM staff keep landing on `app` after login (not auto-routed to the chooser). A dedicated `admin.aeropaperasse.fr` central landing remains optional/future.

### Q3 — DNS provider for wildcard SSL

Let's Encrypt DNS-01 wildcard requires API access to the DNS provider. To confirm: which DNS provider hosts `aeropaperasse.fr`, and does Forge already integrate with it? If not, we'll need a manual cert renewal flow or a switch of DNS provider.

### Q4 — Training catalog — read-only from tenants, or writable?

The catalog is central. Who can edit it? Only REM staff from a central admin UI? Or do tenants get to add their own entries that REM later promotes to global? POC reads from central; write flow is post-POC.

### Q-FK — Cross-database user references {#q-fk}

Tenant tables reference `central.users` without a DB-level FK (see [Cross-database references](#cross-database-references)). To confirm: how is orphan/cascade behaviour handled on user deletion or tenant removal (observer, soft-delete, nullify)? And do we need a periodic integrity check?

### Q-REM — How is REM cross-tenant access represented at scale? {#q-rem}

POC model: REM staff get a `tenant_user` row per tenant (auto-attached at provisioning). At self-service scale this fan-out is awkward (every new tenant must back-fill every REM user). Alternative: a global REM flag/role on the user that bypasses the pivot. Decide before self-service provisioning.

### Q-ROLES — Final enum values & helper refactor {#q-roles}

**Resolved (enum values, 2026-05-28):** the machine values are now final and live in `App\Enums\Role`: `rem_super_admin`, `rem_admin`, `owner`, `tenant_admin`, `aclient`, `sclient`, `client`. The REM values were renamed in the stored data (central migration); tenant values were conserved. The password broker was pinned to central.

**Resolved (helper refactor, 2026-05-28):** role checks are now tenant-contextual, and — crucially — without touching the ~236 call-sites. The split:
- **REM-level helpers are global** (read `users.role`): `isRemStaff()`, `isAdmin()` (= REM staff), `isSAdmin()`. REM-ness isn't tenant-scoped, and keeping it global avoids a cycle with `effectiveRole()`.
- **Tenant-level helpers are contextual**: `isClient()`/`isSClient()`/`isAClient()` read `User::contextualRole()` = `effectiveRole()?->value ?? users.role` — i.e. the role on the **active tenant's** `tenant_user` pivot, **falling back to the global column** when no pivot resolves (central/console context, or a user with no row). The fallback makes the change non-breaking (single-tenant users and existing tests, whose pivot role matches the column, are unaffected) while a **multi-tenant user with different roles per tenant now gets the correct role on each**. `effectiveRole()` is memoized per request.
- The legacy `RoleMiddleware` now checks `contextualRole()` too.
- Covered by `tests/Feature/TenantRoleContextTest`. Full suite **83/83 green**.

**Role display (done, 2026-05-28):** the role **badges** in the coworker/company views (`coworkers/index`, `coworkers/show`, `companies/coworkers-list`) now read `contextualRole()` and the badge maps gained `owner`/`tenant_admin` entries — so a coworker's badge shows the active-tenant role. (The role *edit* form still sets the global `users.role` column — making role assignment per-tenant is a separate, larger change.) Also pending: choosing a final display name/value for `aclient`.

### Q-CLIENT — User ↔ company (client) association becomes tenant-contextual {#q-client}

Today a `client` / `sclient` user is tied to **their company** via `users.client_id` (FK → `clients`), and `User::client()` / `User::coworker()` resolve from it. Under the multi-DB model `clients` lives in a **tenant** database, so:
- a central `users.client_id` can no longer reference it (cross-DB), and
- a user on several tenants can't carry a single global company link.

**Decided — model it now (POC):** a **nullable `client_id` on the `tenant_user` pivot** holds the company this user maps to *within that tenant* (plain int, no FK — it references the tenant DB's `clients`, integrity app-enforced). `User::client()` / `User::coworker()` become **contextual to the active tenant**. The cross-DB FK (`users.client_id` → `clients`) is dropped. The legacy `users.client_id` / `users.coworker_id` columns are **kept physically** (nullable, no FK) but **deprecated** — `tenant_user.client_id` is the source of truth.

**Implemented (POC):** `User` overrides `newRelatedInstance()` so it does **not** force the central connection onto related models — relations to tenant models (`client`, `coworker`, `badgeRequests`, …) resolve on the **active tenant** connection, while central-pinned models (`Tenant`, `Training`) keep central. This is what makes `$user->client` work in tenant context without per-relation rewrites. Migrating every call-site to read the company from `tenant_user.client_id` (rather than the deprecated column) and removing the dead columns remain post-POC ([Q-ROLES](#q-roles)).

> **Resolved (was Q1 — users table split):** the `users` table is **not** split. All users live in `central.users`; per-tenant role lives on the `tenant_user` pivot; a user can belong to several tenants. Email is unique across the single central directory.

### Cross-database gotchas resolved during the POC {#poc-gotchas}

Recurring patterns to know when touching this code (all because the default connection is swapped to the tenant in tenant context, while central tables stay central):

- **Sessions** are on the central connection (`SESSION_DRIVER=database`, `SESSION_CONNECTION=central`, `sessions` migration in central) so the store lives outside the tenant switch — required because `FilesystemTenancyBootstrapper` suffixes `storage_path`.
- **Local HTTP testing — `SESSION_SECURE_COOKIE=false` in `.env`.** `config/session.php` defaults `secure` to `true`; over plain `http://*.aeropaperasse.test` the browser then drops the (secure) session + XSRF cookies, so no session persists and every Livewire request returns **419 "page expired"** (the login form appears to reload back to itself). Set `SESSION_SECURE_COOKIE=false` for local dev; it **must be `true` in production** (HTTPS). `SESSION_DOMAIN=.aeropaperasse.test` (leading dot) shares the cookie across `app.*`/`client*.*`.
- **Central-pinned models** (`$connection = 'central'`): `User`, `Training`, `TwoFactorCode` (2FA is issued during login, which runs in tenant context). `Tenant`/`Domain` are pinned by the package (`CentralConnection`).
- **Validation `exists`/`unique` rules against central tables** must name the connection: `exists:central.trainings,id` (a bare `exists:trainings,id` would hit the tenant DB). Rules against tenant tables (`clients`, `coworkers`, …) stay unqualified.
- **`User` relations to tenant models** resolve via the `newRelatedInstance()` override (see Q-CLIENT).
- **No cross-DB query *filters* (`whereHas`/`orWhereHas`/`whereDoesntHave`) against central relations.** Eager loading a central relation from a tenant model (`with('user')`) works (separate query), but a `whereHas('user', …)` builds a correlated subquery on the **tenant** connection → `tenant_X.users doesn't exist` (500). This had silently broken `coworkers/index` (4 spots, incl. the always-run `statistics()`) and the `companies/trainings-list` search under tenancy. **Workaround (applied, the doc's "batch" pattern):** fetch the matching ids from the central model (`User::where(...)->pluck('id')`, `Training::where('title','like',…)->pluck('id')`) and filter the tenant query with `whereIn('user_id', …)` / `whereNotIn(...)` / `whereIn('training_id', …)`. The same pattern still applies to any new cross-DB filter. *(The legacy API controllers — ClientController/TrainingController/BadgeRequestController/DiscussionController — still contain cross-DB `whereHas('user')` and were left as-is.)*
- **Assets (Vite / `asset()`):** `config/tenancy.php` → `asset_helper_tenancy = false`. With it on, the `FilesystemTenancyBootstrapper` rewrites every `asset()` URL (including the `@vite` build files and the `public/images` logo) to the per-tenant `/tenancy/assets/*` route, which serves tenant storage — so the Tailwind/Vite build and the logo 404'd and pages rendered unstyled. All `asset()` usages here target **global** static files (build, logo); tenant files (documents/PDFs) go through Storage download routes, so global `asset()` is correct.
- **Vite dev server cross-origin:** the dev server (`npm run dev` / `composer run dev`) is a different origin (`127.0.0.1:5173`) than the tenant domains, so its ES-module/HMR assets are loaded cross-origin and were blocked by the browser (styles missing, images fine). Fixed in `vite.config.js` with `server.cors` allowing `*.aeropaperasse.test` (+ `host: 127.0.0.1`). Restart the dev server after changing that config. (`npm run build` also works without a dev server.)
- **`routes/tenant.php` placeholder shadowed `/`:** stancl's scaffolding registers a default `GET /` (returning a string) under the tenant domains; it loads **after** `routes/web.php`, so it **overwrote** the app's `/` route. Removed it — this app puts all routes in `web.php` (already wrapped in tenancy middleware); `routes/tenant.php` is now empty.
- **`User::tenants()` must reference `App\Models\Tenant`** (not the base `Stancl\Tenancy\Database\Models\Tenant`): only the app model has the `HasDomains` trait, so `->tenants()->with('domains')` (used by the chooser) needs it. Achieved by *not* importing the base class in `User` (same namespace resolves to `App\Models\Tenant`).
- **Tests** touching tenant-scoped models extend `Tests\TenantTestCase` (boots a real tenant DB + initializes tenancy + gives it a domain for HTTP tests). Non-REM users acting in a tenant need a `tenant_user` row (or use a REM `admin`/`sadmin` who bypasses the membership gate). The POC seeder accounts have `two_factor_enabled = false` and `is_new = false` so local browser testing isn't blocked by the email 2FA code (`MAIL_MAILER=log`) or the forced first-login password change.

---

## POC scope

**Goal:** prove that the current Laravel 13 + Livewire v4 stack runs cleanly under `stancl/tenancy` with two isolated tenants, a single central user directory, tenant membership via the `tenant_user` pivot, REM cross-tenant access, and a shared training catalog.

### In scope

1. New branch `poc-multitenant-l13` from `multitenant` (the up-to-date Laravel 13 branch). The earlier `poc-multitenant` / `migration-multitenant` branches predate the framework upgrade and are abandoned.
2. Install `stancl/tenancy` — **v3.10+ supports `^10|^11|^12|^13`**, so Laravel 13 is covered.
3. Three local databases: `aeropaperasse_central`, `tenant_rem`, `tenant_c1`.
4. Central schema: `tenants`, `domains`, `users` (all users), `tenant_user` (pivot with role), `trainings`.
5. Tenant schema (minimal subset to demonstrate): `clients`, `coworkers` (no `users` table in tenant DBs).
6. Local domains: `app.aeropaperasse.test`, `client1.aeropaperasse.test` via Windows hosts + Laragon Apache vhost with `ServerAlias *.aeropaperasse.test`.
7. Single central auth guard + tenant-aware authorization (REM role → all tenants; otherwise pivot lookup for the current tenant).
8. Seeder:
   - 1 REM `Super administrateur REM` (central) with cross-tenant access to both tenants.
   - 1 user attached only to C1 (e.g. `Owner` of C1) — must be rejected on `app.*`.
   - a few demo `clients`/`coworkers` per tenant, a couple of shared `trainings`.
9. **Smoke tests:**
   - A user attached only to C1 cannot log in on `app.*` (no `tenant_user` row for the REM tenant).
   - A REM super-admin can log in on both subdomains and sees the correct per-tenant data.
   - A user attached to both tenants with **different roles** gets the right effective role on each subdomain.
   - One existing Livewire page (probably `pages/clients/index`) renders the right per-tenant data on both subdomains.
   - The shared `trainings` catalog is readable from both tenants.

### Out of POC (deliberately deferred)

- Migration of the real REM data into `tenant_rem` (carve-out of business tables).
- Helper/middleware refactor to tenant-contextual role checks (Q-ROLES).
- Cross-database user reference integrity (Q-FK).
- File storage isolation per tenant (`storage/app/tenant{id}/`).
- Queue jobs tenant-awareness.
- Scheduler iterating over tenants.
- Resend (email) tenant context.
- PDF generation tenant context.
- Laravel Scout per-tenant indexing.
- Forge configuration (wildcard site, wildcard SSL, deploy script).
- Self-service signup / billing.
- "Switch tenant" UI for users with multiple tenants (Q2).

---

## Roadmap after POC

Once the POC is validated, the post-POC roadmap (high level — to be detailed when we get there):

1. **Auth/roles refactor — done.** Enum values finalized (`App\Enums\Role`), REM values renamed in stored data, role helpers + `RoleMiddleware` made tenant-contextual (`contextualRole()` = active-tenant pivot role with global fallback; REM helpers stay global). See [Q-ROLES](#q-roles). Remaining sliver: role *display* (raw column) and the `aclient` rename.
2. **Real-data migration plan** — promote the existing DB to the central DB, carve business tables into `tenant_rem`, back-fill `tenant_user` rows, map roles (see [Migration of existing data](#migration-of-existing-data)).
3. **Tenant-aware infrastructure** — queues, scheduler, storage, mail, PDF, Scout. **Audit (2026-05-28):**
   - **Queues:** no `ShouldQueue` jobs in the app → nothing to make tenant-aware.
   - **Scout:** driver is `database` (`config/scout.php`); the searchable models (`Client`, `ActivityRequest`, `BadgeRequest`) are tenant-scoped, so `::search()` hits the active tenant DB directly → isolation is **already automatic**, no per-tenant index needed. The per-tenant index-prefix decision only applies if migrating to an external engine (Algolia/Meilisearch/Typesense).
   - **Scheduler:** both expiry commands **now iterate `Tenant::all()` and run per tenant** via `$tenant->run()`, with the log path resolved once in central. `NotifyBadgeExpiry` (`badges:check-expiry`) reads tenant `badges`; `NotifyTrainingExpiry` (`trainings:check-expiry`) was **rewritten to read the tenant `coworker_trainings` table** (the live training-assignment model — `user_trainings` was a dead legacy table with no migration) and notifies each coworker; its `TrainingExpiryNotification` mailable now takes a `CoworkerTraining`. Covered by `tests/Feature/NotifyBadgeExpiryTest` and `tests/Feature/NotifyTrainingExpiryTest` (2 tenants each, isolation + out-of-window). Schedule entries unchanged. *(Legacy `App\Models\UserTraining` + `TrainingController` API still reference `user_trainings` — untouched, legacy API.)*
   - **Storage (done):** uploads use the `public` disk; the `FilesystemTenancyBootstrapper` overrides that disk's root to the tenant dir, so **writes are auto-isolated** (`storage/tenant{id}/app/public/...`). Reads/downloads via `Storage::disk('public')->path()/download()` resolve to the tenant dir (already correct). **Serving by URL was the gap:** `asset('storage/…')` / `Storage::disk('public')->url()` go through the shared `public/storage` symlink (central) — they were replaced with **`tenant_asset($path)`** (stancl's tenant-asset route `/tenancy/assets/{path}`, which serves the tenant's `storage/app/public` with path-traversal protection) in the live views (`companies/show`, `vehicle-pass/show`, `trainings/show`, `activity-requests/show`). This is why `asset_helper_tenancy` stays `false` (global `asset()` for build/logo) while documents use the explicit `tenant_asset()`. Covered by `tests/Feature/TenantStorageIsolationTest`. *(Legacy `ClientController`/`TrainingController` API still use `Storage::url()` — untouched, legacy.)*
   - **PDF (ok):** `ClientOverviewPdfService` streams the PDF on the fly from tenant-scoped queries (no stored file) → already isolated.
   - **Mail (ok for now):** mailables send in the tenant request context against the central mail config; no per-tenant from-address/branding is configured (add later if needed).
4. **Forge production setup** — wildcard site, wildcard SSL, deploy workflow that runs both central and tenant migrations.
5. **Tenant provisioning flow** — admin UI or command to create a new tenant (DB + migrations + REM `tenant_user` back-fill), then a self-service version with billing.
6. **Cutover plan** — staging validation, production cutover, rollback plan.

---

## References

- [stancl/tenancy documentation](https://tenancyforlaravel.com/)
- Existing UI architecture: [`docs/ui/architecture.md`](ui/architecture.md)
- Project root: [`CLAUDE.md`](../CLAUDE.md)
