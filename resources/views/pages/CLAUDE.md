# resources/views/pages/

You are working inside the **Livewire page components** directory. The rules here are non-negotiable.

## Local rules

- Each file is a Livewire SFC representing a **full page** reachable via a URL.
- Pages are grouped by domain: `pages/{domain}/⚡{name}.blade.php` (the ⚡ prefix is the Livewire v4 SFC convention — kept on in this project).
- Every page MUST be routed via `Route::livewire('/path', 'pages::domain.name')` in `routes/web.php`. **NEVER** use `Route::get()` for pages.
- Every page declares its layout and title via PHP attributes:
  ```php
  #[Layout('layouts::app')]   // or 'layouts::auth' for the auth flow
  #[Title('My page')]
  ```
- The default layout is `layouts::app`. Use `layouts::auth` only for login, 2FA, password reset.
- Layout files live at `resources/views/layouts/{name}.blade.php`. Generate with `php artisan livewire:layout`.
- Pages MAY embed Livewire SFC via `<livewire:domain.name />` and MUST compose `<x-ui.*>` primitives for atomic UI.
- Authorization gates beyond route middleware go in `mount()` (e.g. role-based redirects).
- User-facing feedback after an action (success / failure / status change) → use the `InteractsWithToasts` trait and `$this->toast(...)`. Never use silent `session()->flash('message', ...)`. See `docs/ui/feedback.md`.
- **Navigation between pages MUST use `wire:navigate`** (SPA-style, no full reload):
  - Anchors: `<a href="{{ route('...') }}" wire:navigate>…</a>`
  - Button-as-link: `<x-ui.button :href="route('...')" wire:navigate>…</x-ui.button>`
  - Server-side redirects: `$this->redirect(route('...'), navigate: true)`
  - **NEVER** use `wire:click="someMethod"` where `someMethod` only does `$this->redirect(...)` — use a real `<a wire:navigate>` instead.
  - Exceptions: external URLs, `mailto:`/`tel:`, `#anchors`, downloads, POST forms (logout). See `docs/ui/architecture.md` → "Navigation between pages".

## Generating

```bash
php artisan make:livewire pages::{domain}.{name}
```

Examples:
- `php artisan make:livewire pages::activity-requests.index`
- `php artisan make:livewire pages::auth.login`
- `php artisan make:livewire pages::clients.show`

Then add the route:

```php
Route::livewire('/activity-requests', 'pages::activity-requests.index')
    ->middleware('auth')
    ->name('activity-requests.index');
```

## Forbidden

- Wrapping a page in a Blade view that contains `<livewire:...>` — bind the component directly via `Route::livewire(...)`.
- Mixing layout selection logic in templates — use the `#[Layout(...)]` attribute.
- Querying without eager loading (N+1 risk) — use `with()` / `load()`.

## Reference

- Page anatomy, layouts, route binding, testing: `docs/ui/livewire-pages.md`
- Layout files convention: `docs/ui/livewire-pages.md` → "Layouts" section
- User feedback (toasts) after actions: `docs/ui/feedback.md`
- The big picture: `docs/ui/architecture.md`
