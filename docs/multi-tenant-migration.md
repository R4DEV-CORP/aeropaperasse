# Multi-Tenant Migration

This document tracks the migration from a single-instance app (`app.aeropaperasse.fr`) to a multi-tenant architecture where additional clients can run on their own subdomain (`client1.aeropaperasse.fr`, `client2.aeropaperasse.fr`, ...) on the same codebase.

It is the **source of truth** for architectural decisions, open questions, and the migration roadmap. Update it as decisions are taken or context changes — so any Claude session (or human contributor) can pick the work up.

---

## Status

- **Current phase:** POC (local, throwaway-safe)
- **Branch:** `poc-multitenant` (to be created from `migration-multitenant`)
- **Last updated:** 2026-05-12

---

## Motivation & business context

Today `app.aeropaperasse.fr` is operated by **REM**. Airport client companies submit badge/activity/vehicle requests on the site, and REM staff process them and forward the files to **ADP** (the airport authority). ADP does not interact with the site.

Some new clients want to **use the platform to manage their own data and requests, but submit to ADP themselves** (white-label use case). They need their own isolated space — separate users, separate data, separate URL.

Constraints:
- `app.aeropaperasse.fr` keeps running indefinitely (REM tenant) alongside the new subdomains.
- **One single codebase** must serve both `app.*` and `client*.*` — no fork.
- Volume short-term: < 10 tenants. Long-term: self-service signup with paid plans.
- REM staff must be able to **access all tenants** with their own accounts (cross-tenant access).

---

## Architecture decision

### Approach: multi-database tenancy via `stancl/tenancy`

Each tenant gets its **own MySQL database**. A central database holds the tenant registry and shared catalogs.

**Why multi-DB rather than single-DB with `tenant_id` column:**

- True isolation: no risk of data leak via a forgotten query scope.
- Per-tenant backups and restore — useful commercially ("your data, your database") and for RGPD.
- Provisioning a new tenant = create a DB + run migrations → automatable, natural path to self-service.
- Existing data migrates cleanly: the current production database becomes the REM tenant DB. No data shuffling.

**Cost / complexity accepted:**

- Every queue job, command, scheduler entry must be tenant-aware (`stancl/tenancy` provides the helpers, but it needs discipline).
- Two migration directories: `database/migrations/` (central) and `database/migrations/tenant/` (per-tenant).
- File storage isolation per tenant (`storage/app/tenant{id}/...`) instead of a single shared bucket.

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
| **Central** | `users` (super-admins only) | REM staff — see auth model below |
| **Central** | `trainings` | Training catalog is **shared across all tenants** |
| **Tenant** | `users` | Tenant-scoped users (`sclient`, `client` roles today) |
| **Tenant** | `clients` | Companies served by this tenant |
| **Tenant** | `coworkers` | Employees of those companies |
| **Tenant** | `activity_requests`, `badge_requests`, `vehicle_passes`, `badges` | All request workflows |
| **Tenant** | `coworker_training` (pivot, eventually) | Per-tenant assignment to the shared `trainings` catalog |

Anything else added later (subscriptions, billing, tenant settings) lives **central**.

---

## Auth model

Two distinct user populations:

| Population | Stored in | Current roles | Scope |
|---|---|---|---|
| **REM super-admins** | `central.users` | `sadmin`, `admin` | Can authenticate on **any** subdomain. See the data of the tenant they're currently visiting. |
| **Tenant users** | `tenant_X.users` | `sclient`, `client` | Can only authenticate on their own tenant's subdomain. |

**Login flow on any subdomain:**

1. Try to authenticate against the current tenant's `users` table.
2. On failure, fall back to `central.users` (super-admin).
3. On success via central, the session is flagged "super-admin" but stays in the current tenant context (the super-admin sees this tenant's data).

For the POC there is no "switch tenant" UI — REM staff just type the subdomain in their browser and log in.

---

## Open questions

### Q1 — Users & roles refactor for production migration

**Status: not decided.** The current `users` table mixes the 4 roles. To move from the current state to the target auth model, that table needs to be split — `sadmin`/`admin` rows move to `central.users`, `sclient`/`client` rows move into the REM tenant DB's `users` table.

Open sub-questions:
- Do we keep the **same role enum** in both tables, or do central users get a simpler `super_admin` boolean and tenant users keep the `sclient`/`client` distinction only?
- A REM staff member who is also a client user of another tenant (edge case) — possible at all? If yes, are they two rows or one with cross-tenant capability?
- Existing relations: `coworker.user_id`, `client.user_id`, `*_request.user_id`, etc. — these point to the current `users` table. After the split, do they all point to tenant users only? (Likely yes, but to confirm by auditing FK usage.)
- Password reset flows, email uniqueness — does an email need to be unique across central + all tenants, or only within its scope?

This needs to be designed **before** the production cutover (post-POC), but the POC does not need to solve it. The POC seeds separate central and tenant users from scratch.

### Q2 — Where do super-admins land after login?

When a REM super-admin logs in on `app.aeropaperasse.fr`, they see REM tenant data. When they log in on `client1.aeropaperasse.fr`, they see C1 data. Fine. But:
- Is there a central admin landing (e.g. `admin.aeropaperasse.fr`) that lists all tenants and lets a super-admin pick one?
- Or does it stay subdomain-driven only (REM staff bookmark each subdomain)?

Not blocking for POC.

### Q3 — DNS provider for wildcard SSL

Let's Encrypt DNS-01 wildcard requires API access to the DNS provider. To confirm: which DNS provider hosts `aeropaperasse.fr`, and does Forge already integrate with it? If not, we'll need a manual cert renewal flow or a switch of DNS provider.

### Q4 — Training catalog — read-only from tenants, or writable?

The catalog is central. Who can edit it? Only REM super-admins from a central admin UI? Or do tenants get to add their own entries that REM later promotes to global? POC reads from central; write flow is post-POC.

---

## POC scope

**Goal:** prove that the current Laravel 10 + Livewire v4 stack runs cleanly under `stancl/tenancy` with two isolated tenants, REM cross-tenant auth, and a shared training catalog.

### In scope

1. New branch `poc-multitenant` from `migration-multitenant`.
2. Install `stancl/tenancy` (v3 confirmed compatible with Laravel 10 — to verify at install time).
3. Three local databases: `aeropaperasse_central`, `tenant_rem`, `tenant_c1`.
4. Central schema: `tenants`, `domains`, `users` (REM), `trainings`.
5. Tenant schema (minimal subset to demonstrate): `users`, `clients`, `coworkers`.
6. Local domains: `app.aeropaperasse.test`, `client1.aeropaperasse.test` via Windows hosts + Laragon Apache vhost with `ServerAlias *.aeropaperasse.test`.
7. Auth guards: tenant guard (default in tenant context) + central guard (fallback for super-admins).
8. Seeder: 1 REM super-admin (central), 1 user per tenant, a few demo `clients`/`coworkers` per tenant, a couple of shared `trainings`.
9. **Smoke tests:**
   - A user of C1 cannot log in on `app.*`.
   - A REM super-admin can log in on both subdomains and sees the correct data.
   - One existing Livewire page (probably `pages/clients/index`) renders the right per-tenant data on both subdomains.
   - The shared `trainings` catalog is readable from both tenants.

### Out of POC (deliberately deferred)

- Migration of the real REM data into `tenant_rem`.
- Refactor of the production `users` table (see Q1).
- File storage isolation per tenant (`storage/app/tenant{id}/`).
- Queue jobs tenant-awareness.
- Scheduler iterating over tenants.
- Resend (email) tenant context.
- PDF generation tenant context.
- Laravel Scout per-tenant indexing.
- Forge configuration (wildcard site, wildcard SSL, deploy script).
- Self-service signup / billing.
- "Switch tenant" UI for super-admins (Q2).

---

## Roadmap after POC

Once the POC is validated, the post-POC roadmap (high level — to be detailed when we get there):

1. **Auth refactor** — design and execute the users-table split (Q1).
2. **Real-data migration plan** — promote the existing production DB to `tenant_rem`, extract central data (trainings + super-admins) into the central DB.
3. **Tenant-aware infrastructure** — queues, scheduler, storage, mail, PDF, Scout.
4. **Forge production setup** — wildcard site, wildcard SSL, deploy workflow that runs both central and tenant migrations.
5. **Tenant provisioning flow** — admin UI or command to create a new tenant (and eventually a self-service version with billing).
6. **Cutover plan** — staging validation, production cutover, rollback plan.

---

## References

- [stancl/tenancy documentation](https://tenancyforlaravel.com/)
- Existing UI architecture: [`docs/ui/architecture.md`](ui/architecture.md)
- Project root: [`CLAUDE.md`](../CLAUDE.md)
