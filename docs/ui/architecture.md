# UI Architecture

This document is the source of truth for the UI architecture of Aéropaperasse. The rules below are **inviolable** and reflect the post-Flux migration decisions.

## Core principle

There is a **hard boundary** between two kinds of UI building blocks:

| | Blade primitives | Livewire components |
|---|---|---|
| State | None (stateless) | Server-state, reactive |
| Server interaction | None | Full Livewire lifecycle |
| Location | `resources/views/components/ui/` | `resources/views/components/{domain}/` |
| Format | Blade `@props` component | Livewire Single-File Component (SFC) |
| Used as | `<x-ui.button />` | `<livewire:domain.name />` |
| Examples | button, input, alert, badge, modal-shell | create-activity-request-form, badge-list, training-search |

Livewire components **must compose** Blade primitives — never re-implement buttons or inputs inside a Livewire component.

## Directory map

```
resources/
├── css/
│   └── app.css                          # Tailwind v4 + theme tokens (@theme)
└── views/
    ├── components/                      # contains BOTH Blade primitives AND Livewire SFC, separated by subfolder
    │   ├── ui/                          # Blade primitives (stateless), addressed as <x-ui.*>
    │   │   ├── button.blade.php
    │   │   ├── input.blade.php
    │   │   ├── alert.blade.php
    │   │   └── ...
    │   ├── activity-requests/           # Livewire SFC, addressed as <livewire:activity-requests.*>
    │   │   ├── ⚡create-form.blade.php   # SFC files are prefixed with ⚡ (Livewire v4 convention, kept on)
    │   │   └── ⚡view.blade.php
    │   ├── badge-requests/
    │   ├── coworkers/
    │   └── ...
    ├── pages/                           # Livewire page components (one per route)
    │   ├── auth/
    │   │   ├── ⚡login.blade.php
    │   │   └── ⚡verify-2fa.blade.php
    │   ├── activity-requests/
    │   │   └── ⚡index.blade.php
    │   └── ...
    └── layouts/                         # Livewire layouts
        ├── app.blade.php                # Authenticated app shell
        └── auth.blade.php               # Login / 2FA / password flow
```

## Routing

All page routes MUST use `Route::livewire()`. The second argument is the dot-notation reference to the page component.

```php
Route::livewire('/activity-requests', 'pages::activity-requests.index')
    ->middleware('auth')
    ->name('activity-requests.index');

Route::livewire('/clients/{slug}', 'pages::clients.show')
    ->middleware('auth')
    ->name('clients.view');
```

The `pages::` and `layouts::` namespaces are registered in `config/livewire.php` under `component_namespaces` (defaults shipped by Livewire v4 — already aligned with this architecture):

```php
'component_namespaces' => [
    'layouts' => resource_path('views/layouts'),
    'pages' => resource_path('views/pages'),
],

'component_layout' => 'layouts::app',

'make_command' => [
    'type' => 'sfc',     // Single-file by default
    'emoji' => true,     // SFC files are prefixed with ⚡ (project convention — kept on)
],
```

The dot-notation `pages::activity-requests.index` resolves to `resources/views/pages/activity-requests/⚡index.blade.php` (Livewire automatically locates the ⚡-prefixed SFC).

## Decision flow

When you need to build a piece of UI, walk this tree:

1. **Is it stateless and reusable across many views?** (button, badge, alert, label, icon-wrapper)
   → Blade primitive in `resources/views/components/ui/`

2. **Is it stateful but embedded inside a page?** (form, list with filters, dropdown menu, modal triggered by an action)
   → Livewire SFC in `resources/views/components/{domain}/`

3. **Is it a full page reachable via a URL?**
   → Livewire SFC in `resources/views/pages/{domain}/` + `Route::livewire(...)` entry

If a primitive grows state (e.g. a dropdown that needs server data), do NOT add Livewire to `ui/` — extract it as a Livewire component in `components/{domain}/` (or `components/shared/` if cross-domain) that *uses* the primitive shell.

## What this replaces

This architecture replaces the previous Flux UI / Livewire v3 stack:
- `<flux:*>` components → either `<x-ui.*>` primitives or Livewire SFC
- `app/Livewire/{Domain}/{Name}.php` + `resources/views/livewire/{domain}/{name}.blade.php` → `resources/views/components/{domain}/{name}.blade.php` (single file)
- `Route::get(..., fn () => view(...))` returning a Blade wrapper around `<livewire:...>` → `Route::livewire(...)` directly to a page component

## Files

- Component creation patterns: see `livewire-components.md`
- Page component patterns: see `livewire-pages.md`
- Blade primitive patterns: see `blade-primitives.md`
- Styling tokens and theme rules: see `styling.md`
