# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.30
- laravel/framework (LARAVEL) - v10
- laravel/mcp (MCP) - v0
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v3
- laravel/scout (SCOUT) - v10
- livewire/livewire (LIVEWIRE) - v4
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v10
- tailwindcss (TAILWINDCSS) - v4

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v10 rules ===

## Laravel 10

- Use the `search-docs` tool to get version specific documentation.
- Middleware typically live in `app/Http/Middleware/` and service providers in `app/Providers/`.
- Laravel 10 has a `bootstrap/app.php` file that creates the application instance and binds kernel contracts, but does not use it for application configuration like Laravel 11:
    - Middleware registration is in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule registration is in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`
- When using Eloquent model casts, you must use `protected $casts = [];` and not the `casts()` method. The `casts()` method isn't available on models in Laravel 10.


=== mcp/core rules ===

## Laravel MCP

- MCP (Model Context Protocol) is very new. You must use the `search-docs` tool to get documentation for how to write and test Laravel MCP servers, tools, resources, and prompts effectively.
- MCP servers need to be registered with a route or handle in `routes/ai.php`. Typically, they will be registered using `Mcp::web()` to register a HTTP streaming MCP server.
- Servers are very testable - use the `search-docs` tool to find testing instructions.
- Do not run `mcp:start`. This command hangs waiting for JSON RPC MCP requests.
- Some MCP clients use Node, which has its own certificate store. If a user tries to connect to their web MCP server locally using https://, it could fail due to this reason. They will need to switch to http:// during local development.


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v4 rules ===

## Livewire 4

### Component formats
- Default format is **Single-File Component (SFC)**: PHP class + Blade template in one `.blade.php` file.
- SFC structure: a `<?php new class extends Component { ... }; ?>` block at the top, followed by the Blade template.
- Use `php artisan make:livewire <name>` (SFC by default), `--mfc` for multi-file, `--class` for class-based.

### Self-closing tags (breaking change from v3)
- Component tags MUST be self-closed: `<livewire:foo />`, never `<livewire:foo>`.

### Routing
- Full-page components MUST be routed with `Route::livewire('/path', 'pages::domain.name')`.
- Never use `Route::get('/path', Component::class)` or `Route::get('/path', fn() => view(...))` for Livewire pages.

### Layouts
- Layouts live in `resources/views/layouts/` (e.g. `app.blade.php`, `auth.blade.php`).
- Reference them as `layouts::app`, `layouts::auth` (the `layouts` namespace is registered in `config/livewire.php`).
- Apply per-component with the `#[Layout('layouts::auth')]` PHP attribute, or globally via `component_layout` config.
- Set page titles with `#[Title('...')]` PHP attribute.

### wire:model behavior (breaking change from v3)
- `wire:model` no longer captures events from child elements. Use `.deep` modifier if needed.
- `wire:model.blur` and `wire:model.change` now control client-side sync timing too. Use `.live.blur` / `.live.change` for v3-style behavior.

### Alpine
- Alpine is bundled with Livewire — do not include it manually.
- Plugins included: persist, intersect, collapse, focus.

### Best practices
- Components require a single root element.
- Use `wire:loading` / `wire:dirty` for loading states.
- Add `wire:key` in loops.
- Prefer lifecycle hooks (`mount()`, `updatedFoo()`) for initialization and reactive side effects.
- Validate inputs and run authorization in Livewire actions — they hit the backend like regular HTTP requests.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- This project uses a **single light theme**. Dark mode is NOT supported — never add `dark:*` variant classes.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed.
<code-snippet name="Extending Theme in CSS" lang="css">
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
</code-snippet>

- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |
</laravel-boost-guidelines>

---

## Application Overview

**Aéropaperasse** is an airport security management platform. It handles administrative requests (activity authorizations, badge requests, vehicle passes) for airport clients (companies), with document generation (PDFs), email notifications, and a training catalog.

## User Roles

The `User` model has a `role` field with these values:
- `sadmin` / `admin` — internal staff with full access
- `sclient` — client supervisor (can manage their company's users)
- `client` — regular client user (limited to their own company's data)

Role checks use helper methods: `isAdmin()`, `isSAdmin()`, `isClient()`, `isSClient()`. API routes are protected by `RoleMiddleware` via `role:admin,sadmin` etc.

Clients with `can_access_formation = false` are redirected away from training routes.

## Architecture

This application is a **Laravel + Livewire monolith**. It started as a REST API but has been migrated to server-rendered Livewire pages. The API layer (`routes/api.php` + `app/Http/Controllers/`) is legacy and largely unused — prefer Livewire for all new features.

- **Livewire layer** (`routes/web.php`) — The primary layer. Web routes return Blade views that embed Livewire components. New features should be built here.
- **API layer** (`routes/api.php`) — Legacy. Do not add new API routes or controllers unless explicitly asked.

### Key Directories

| Directory | Purpose |
|---|---|
| `app/Actions/` | Single-responsibility action classes (e.g. `SaveActivityRequestAction`) — prefer these for complex business logic |
| `app/Services/` | Document generation (PDF via `spatie/laravel-pdf`), email, certificate services |
| `app/DataTransferObjects/` | DTOs for passing validated data to actions (e.g. `CreateActivityRequestData`) |
| `app/Forms/` | Form data classes and validators used by Livewire (e.g. `ActivityRequestFormData`, `ActivityRequestFormValidator`) |
| `app/Livewire/` | Livewire components organized by feature domain |
| `app/Observers/` | Eloquent model observers |
| `app/Validators/` | Custom validator classes |

### Domain Features

- **ActivityRequests** — Airport activity authorization requests with status workflow (draft → pending → approved/rejected) and document upload (KBIS, CTA, AAO, etc.)
- **BadgeRequests** — Badge/access card requests with similar draft/submit/approve workflow
- **VehiclePasses** — Vehicle access pass requests
- **Clients** — Company management with overview PDF export
- **Coworkers** — Employee management linked to clients
- **Training** — Training catalog with user assignment, certificate upload/download; uses Laravel Scout for search
- **Discussions** — Internal messaging/discussion system (partially commented out)
- **Badges** — Physical badge inventory management

### Document Services

Services in `app/Services/` ending in `DocumentService` handle PDF generation using `spatie/laravel-pdf`. Files are stored in `storage/app/public/`. Email services use `resend/resend-laravel`.

### Request Lifecycle (Livewire)

1. Livewire component method → validates input (inline or via `app/Forms/` validators)
2. Creates a DTO (`CreateXxxData`) and calls an Action from `app/Actions/`
3. Action wraps in a DB transaction, calls Services for documents/emails
4. Action returns a Result object (e.g. `ActivityRequestResult`) with success/failure state

## UI Architecture (INVIOLABLE)

This project enforces a **strict separation** between stateless primitives and stateful components. These rules are non-negotiable.

### Blade primitives — `resources/views/components/ui/`
- Stateless UI atoms ONLY: button, input, alert, badge, modal-shell, etc.
- Pure Blade `@props` components. **NEVER** use Livewire directives (`wire:*`, `@livewire`) inside them.
- Used as `<x-ui.button />`, `<x-ui.input />`.
- Naming: kebab-case files (`button.blade.php`).
- Every primitive starts with `@blaze` on line 1 ([livewire/blaze](https://github.com/livewire/blaze) function-compiler optimization). Plain `@blaze` is the safe default — see `docs/ui/blade-primitives.md` → "Performance: @blaze" before using `@blaze(memo: true)` or `@blaze(fold: true)`.

### Livewire components (Single-File) — `resources/views/components/{domain}/`
- All stateful/interactive components live here, grouped by business domain (e.g. `components/activity-requests/create-form.blade.php`).
- **Single-File Component (SFC) format ONLY** — PHP class + Blade template in one file.
- Components MUST compose `<x-ui.*>` primitives — never re-implement a button, an input, etc.
- Reference: https://livewire.laravel.com/docs/4.x/components#creating-components

### Livewire page components — `resources/views/pages/{domain}/`
- Full-page Livewire components live here, grouped by domain.
- Use `#[Layout('layouts::app')]` or `#[Layout('layouts::auth')]` and `#[Title('...')]`.
- Reference: https://livewire.laravel.com/docs/4.x/components#creating-page-components

### Routing
- All page routes MUST use `Route::livewire('/path', 'pages::domain.name')`.
- **NEVER** use `Route::get(..., Component::class)`, never render Livewire pages via `@livewire()` in a Blade file.
- Reference: https://livewire.laravel.com/docs/4.x/pages#routing-to-components

### Navigation between pages (INVIOLABLE)
- Any link that navigates from one app page to another MUST use `wire:navigate` (SPA-style transitions, no full reload).
- This applies to:
  - Plain anchors → `<a href="{{ route('...') }}" wire:navigate>…</a>`
  - Button-as-link → `<x-ui.button :href="route('...')" wire:navigate>…</x-ui.button>` (the primitive forwards `wire:navigate` via `$attributes->merge`)
  - Server-side redirects from a Livewire component → `$this->redirect(route('...'), navigate: true)`
- **NEVER** use `wire:click` calling a method that only does `$this->redirect(...)` — it forces a server round-trip just to navigate. Use a real `<a wire:navigate>` (or `:href` on the button primitive) instead.
- Exceptions (no `wire:navigate`): external URLs, `mailto:` / `tel:`, same-page anchors (`#section`), file downloads, POST forms (e.g. logout).
- Reference: https://livewire.laravel.com/docs/4.x/navigate

### Layouts — `resources/views/layouts/`
- Livewire layouts only: `app.blade.php` (authenticated app), `auth.blade.php` (login flow).
- Layouts must include `{{ $slot }}` and use the `layouts::` namespace when referenced.
- Reference: https://livewire.laravel.com/docs/4.x/pages#layouts

### Styling
- Tailwind v4 only — theme tokens live in `resources/css/app.css` (`@theme` directive).
- **Single light theme**: do NOT use `dark:*` variant classes anywhere. No external CSS framework.

### User feedback
- After any user action that changes server state (create/update/delete/status transition/email/document export), surface the result as a **toast**. Use the trait `App\Livewire\Concerns\InteractsWithToasts` and call `$this->toast($message, $variant, $title)` (variants: `success`, `danger`, `warning`, `info`).
- **NEVER** use `session()->flash('message', ...)` from a Livewire component — no view reads it. From an HTTP controller / non-Livewire redirect, flash `session()->flash('toast', [...])` instead.
- Validation errors stay in `$errors` and render via `:error="$errors->first('field')"` on the input — they are NOT toasts.
- See `docs/ui/feedback.md` for the full guide (variants, anti-patterns, testing).

### Decision flow when creating UI
1. Is it a primitive (no state, no server interaction)? → Blade in `resources/views/components/ui/`
2. Is it stateful but embedded inside another view? → Livewire SFC in `resources/views/components/{domain}/`
3. Is it a full page accessed via a URL? → Livewire SFC in `resources/views/pages/{domain}/` + `Route::livewire(...)`

For full conventions, patterns and examples, see `docs/ui/`.

## Common Commands

```bash
# Run all tests
php artisan test

# Run a single test by name
php artisan test --filter=testName

# Run tests in a specific file
php artisan test tests/Feature/ExampleTest.php

# Fix code style
vendor/bin/pint --dirty

# Rebuild frontend assets
npm run build
```
