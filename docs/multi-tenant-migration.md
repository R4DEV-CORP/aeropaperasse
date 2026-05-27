# Multi-Tenant Migration

This document tracks the migration from a single-instance app (`app.aeropaperasse.fr`) to a multi-tenant architecture where additional clients can run on their own subdomain (`client1.aeropaperasse.fr`, `client2.aeropaperasse.fr`, ...) on the same codebase. Some clients use a dedicated white-label subdomain; regular clients keep using `app.aeropaperasse.fr`.

It is the **source of truth** for architectural decisions, open questions, and the migration roadmap. Update it as decisions are taken or context changes — so any Claude session (or human contributor) can pick the work up.

---

## Status

- **Current phase:** POC (local, throwaway-safe)
- **Branch:** `poc-multitenant-l13` (created from `multitenant`)
- **Framework:** Laravel **13.11.2** (the codebase was bumped 10→11→12→13 since this doc was first written). Note: only the framework *version* was upgraded — the application **keeps the Laravel 10-style skeleton** (`app/Http/Kernel.php` and `app/Console/Kernel.php` still present; `bootstrap/app.php` is the old-style bootstrapper, not the L11+ config-driven `Application::configure()` builder). Middleware registration (e.g. `RoleMiddleware`) therefore still lives in `Http/Kernel.php`, and the L10-specific notes elsewhere in this doc remain valid.
- **Last updated:** 2026-05-27

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

| Display name | Replaces | Proposed enum value |
|---|---|---|
| **Super administrateur REM** | old `sadmin` | `rem_super_admin` *(to confirm at implementation)* |
| **Administrateur REM** | old `admin` | `rem_admin` *(to confirm)* |

### Tenant level (scoped to one tenant, carried by the `tenant_user` pivot)

| Display name | Origin | Proposed enum value |
|---|---|---|
| **Owner** | **new** role | `owner` *(to confirm)* |
| **Administrateur** | **new** role | `tenant_admin` *(to confirm — avoid clashing with the old REM `admin`)* |
| **sclient** | conserved as-is | `sclient` |
| **client** | conserved as-is | `client` |

The **role lives on the `tenant_user` pivot**, not on the user row — so the same user can be `Owner` on tenant A and `client` on tenant B.

`Owner` and `Administrateur` are **new** tenant-level roles, assigned manually after the migration. No existing row is auto-converted into them.

> **Implementation note:** the current code uses `User::role` plus helpers `isAdmin()` / `isSAdmin()` / `isClient()` / `isSClient()` and `RoleMiddleware` (`role:admin,sadmin`). These resolve a *global* role on the user. Under the new model the effective role is **contextual** (it depends on the current tenant), so these helpers and the middleware must be reworked to read the role from the `tenant_user` pivot for the active tenant (REM roles short-circuit to "access granted"). This refactor is part of the post-POC auth work.

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

- **Auth guard on the central connection.** `stancl/tenancy` switches the default DB connection to the tenant in tenant context, but `users` is central. The auth provider (and `password_reset_tokens`, Sanctum tokens) must therefore be **pinned to the central connection** explicitly, so login always queries the central directory regardless of the current tenant.
- **Shared login across subdomains.** Set `SESSION_DOMAIN=.aeropaperasse.fr` so the session cookie is shared across `app.*` and `client*.*` — a user logged in on one subdomain stays logged in on the others. The **session store must live outside the tenant switch** (central connection / Redis / file), otherwise moving subdomain would change the session table and drop the session.
- **Shared login ≠ shared access.** The session is shared, but every subdomain re-runs the per-tenant authorization (step 3 above). A user who is not a member of the current tenant stays authenticated but is sent to the **"no access to this space"** screen (see below).

### "No access" state (decided)

A logged-in user can land on a subdomain where they have **no `tenant_user` row** (e.g. a freshly created user, a user removed from a tenant, or a multi-tenant user hitting a wrong subdomain). This must render a graceful **"you don't have access to this space"** page — not a raw 403, not a forced logout. Built with the project's own `x-ui.*` primitives on `layouts::auth` (**no FluxUI**), consistent with the rest of the app.

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

A "switch tenant" UI is the natural home for this once a user can belong to several tenants. Not blocking for POC.

### Q3 — DNS provider for wildcard SSL

Let's Encrypt DNS-01 wildcard requires API access to the DNS provider. To confirm: which DNS provider hosts `aeropaperasse.fr`, and does Forge already integrate with it? If not, we'll need a manual cert renewal flow or a switch of DNS provider.

### Q4 — Training catalog — read-only from tenants, or writable?

The catalog is central. Who can edit it? Only REM staff from a central admin UI? Or do tenants get to add their own entries that REM later promotes to global? POC reads from central; write flow is post-POC.

### Q-FK — Cross-database user references {#q-fk}

Tenant tables reference `central.users` without a DB-level FK (see [Cross-database references](#cross-database-references)). To confirm: how is orphan/cascade behaviour handled on user deletion or tenant removal (observer, soft-delete, nullify)? And do we need a periodic integrity check?

### Q-REM — How is REM cross-tenant access represented at scale? {#q-rem}

POC model: REM staff get a `tenant_user` row per tenant (auto-attached at provisioning). At self-service scale this fan-out is awkward (every new tenant must back-fill every REM user). Alternative: a global REM flag/role on the user that bypasses the pivot. Decide before self-service provisioning.

### Q-ROLES — Final enum values & helper refactor {#q-roles}

The machine values in the [Roles](#roles) tables are proposals. To finalize: exact enum/string values, and the rework of `isAdmin()`/`isSAdmin()`/`isClient()`/`isSClient()` + `RoleMiddleware` into **tenant-contextual** checks.

### Q-CLIENT — User ↔ company (client) association becomes tenant-contextual {#q-client}

Today a `client` / `sclient` user is tied to **their company** via `users.client_id` (FK → `clients`), and `User::client()` / `User::coworker()` resolve from it. Under the multi-DB model `clients` lives in a **tenant** database, so:
- a central `users.client_id` can no longer reference it (cross-DB), and
- a user on several tenants can't carry a single global company link.

**Decided — model it now (POC):** a **nullable `client_id` on the `tenant_user` pivot** holds the company this user maps to *within that tenant* (plain int, no FK — it references the tenant DB's `clients`, integrity app-enforced). `User::client()` becomes **contextual to the active tenant** (resolve the pivot row for `(user, current tenant)`, then load `Client` on the tenant connection). The cross-DB FK (`users.client_id` → `clients`) is dropped. The legacy `users.client_id` column is **kept physically** (nullable, no FK) but **deprecated** — `tenant_user.client_id` is the source of truth. Migrating every existing call-site of `$user->client` to the contextual resolution and finally removing the dead column are finished as part of the post-POC auth/roles refactor ([Q-ROLES](#q-roles)).

> **Resolved (was Q1 — users table split):** the `users` table is **not** split. All users live in `central.users`; per-tenant role lives on the `tenant_user` pivot; a user can belong to several tenants. Email is unique across the single central directory.

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

1. **Auth/roles refactor** — finalize enum values, rework role helpers + `RoleMiddleware` to read the tenant-contextual role from `tenant_user` (Q-ROLES).
2. **Real-data migration plan** — promote the existing DB to the central DB, carve business tables into `tenant_rem`, back-fill `tenant_user` rows, map roles (see [Migration of existing data](#migration-of-existing-data)).
3. **Tenant-aware infrastructure** — queues, scheduler, storage, mail, PDF, Scout.
4. **Forge production setup** — wildcard site, wildcard SSL, deploy workflow that runs both central and tenant migrations.
5. **Tenant provisioning flow** — admin UI or command to create a new tenant (DB + migrations + REM `tenant_user` back-fill), then a self-service version with billing.
6. **Cutover plan** — staging validation, production cutover, rollback plan.

---

## References

- [stancl/tenancy documentation](https://tenancyforlaravel.com/)
- Existing UI architecture: [`docs/ui/architecture.md`](ui/architecture.md)
- Project root: [`CLAUDE.md`](../CLAUDE.md)
