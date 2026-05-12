---
name: new-livewire-page
description: Scaffold a new Livewire page component in resources/views/pages/{domain}/ and wire its route. Use when the user says "create a new page", "add a route", "scaffold an index/show/edit page", or any request to build a full-page Livewire component reachable via URL.
---

# new-livewire-page

Scaffolds a new Livewire page component and wires the corresponding `Route::livewire(...)` entry.

## Inputs

- `domain` (required) — business domain in kebab-case (e.g. `activity-requests`, `clients`, `auth`).
- `name` (required) — page name in kebab-case (e.g. `index`, `show`, `create`, `login`).
- `path` (required) — URL path (e.g. `/activity-requests`, `/clients/{slug}`).
- `layout` (optional, default `app`) — `app` for authenticated pages, `auth` for login/2FA/password.
- `middleware` (optional) — additional middleware (e.g. `role:admin,sadmin`).

## Steps

1. **Read the conventions first**: `docs/ui/livewire-pages.md`, `resources/views/pages/CLAUDE.md`, and an existing page in a similar domain.

2. **Run the artisan generator**:

   ```bash
   php artisan make:livewire pages::{domain}.{name} --no-interaction
   ```

   This creates `resources/views/pages/{domain}/⚡{name}.blade.php` (the ⚡ prefix is the project convention, do not strip it).

3. **Replace the scaffolded content** with this skeleton, adapted to intent:

   ```php
   <?php

   use Livewire\Component;
   use Livewire\Attributes\Layout;
   use Livewire\Attributes\Title;
   // use App\Models\... as needed

   new
   #[Layout('layouts::app')]
   #[Title('Page title')]
   class extends Component {
       public function with(): array
       {
           return [
               // data passed to the view
           ];
       }
   }; ?>

   <div class="p-6 space-y-6">
       <header class="flex items-center justify-between">
           <h1 class="text-2xl font-semibold">Page title</h1>
       </header>

       <!-- compose <x-ui.*> primitives and embed <livewire:domain.name /> SFCs -->
   </div>
   ```

4. **Add the route** in `routes/web.php`. Group with similar routes (auth-protected, role-restricted, etc.):

   ```php
   Route::livewire('{path}', 'pages::{domain}.{name}')
       ->middleware('auth')
       ->name('{domain}.{name}');
   ```

   For the auth flow:
   ```php
   Route::livewire('/login', 'pages::auth.login')->name('auth.login');
   ```

5. **For routes with model binding**, use the slug/id parameter naming the project already uses:

   ```php
   Route::livewire('/clients/{client:slug}', 'pages::clients.show')
       ->middleware('auth')
       ->name('clients.view');
   ```

   And in the page:
   ```php
   public Client $client; // auto-resolved
   ```

6. **Always**:
   - `#[Layout('layouts::app')]` for authenticated pages, `#[Layout('layouts::auth')]` for the auth flow.
   - `#[Title('...')]` for the page title.
   - Compose `<x-ui.*>` primitives and embed Livewire SFC via `<livewire:domain.name />` rather than reimplementing UI.
   - Eager-load relations in `with()` to avoid N+1.
   - Authorization redirects (e.g. `isClient()` check) go in `mount()`.
   - **Cross-page links use `wire:navigate`**: `<a href="{{ route('...') }}" wire:navigate>` or `<x-ui.button :href="route('...')" wire:navigate>`. Server-side redirects from methods use `$this->redirect(route('...'), navigate: true)`. Never write a `wire:click` whose only job is to redirect — use a real link. Exceptions: external URLs, `mailto:`/`tel:`, `#anchors`, downloads, POST forms.

7. **Generate a feature test**:
   ```bash
   php artisan make:test {Domain}{Name}PageTest --no-interaction
   ```
   Test the route loads, the right layout is used, and `assertSeeLivewire('pages::{domain}.{name}')`.

8. **Run `vendor/bin/pint --dirty`** before finalizing.

## Forbidden during scaffolding

- `Route::get('/path', SomeComponent::class)` or `Route::get('/path', fn () => view(...))` — must be `Route::livewire(...)`.
- Wrapping the page in a Blade view that contains `<livewire:...>` — bind directly.
- Re-implementing primitive HTML inline.
- Cross-page links without `wire:navigate` (or `wire:click="redirectMethod"` patterns that just redirect).

## Reference

- `docs/ui/livewire-pages.md` — full page patterns, layouts, model binding
- `resources/views/pages/CLAUDE.md` — local rules
- `docs/ui/architecture.md` — why this routing model
