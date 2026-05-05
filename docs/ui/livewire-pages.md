# Livewire Page Components

References:
- https://livewire.laravel.com/docs/4.x/components#creating-page-components
- https://livewire.laravel.com/docs/4.x/pages

A **page component** is a Livewire SFC routed directly via `Route::livewire(...)`. It owns the full page (layout slot fills with its template).

## Generating

```bash
php artisan make:livewire pages::{domain}.{name}
```

Examples:

```bash
php artisan make:livewire pages::activity-requests.index
php artisan make:livewire pages::auth.login
php artisan make:livewire pages::clients.show
```

Output: `resources/views/pages/{domain}/⚡{name}.blade.php`.

**Convention de ce projet** : tous les SFC (composants ET pages) sont préfixés par ⚡ — c'est l'option par défaut de Livewire v4 (`make_command.emoji => true`) et on la conserve volontairement pour distinguer les SFC des autres Blade. Pour rechercher / globber : `resources/views/pages/**/⚡*.blade.php`.

## Routing

Always use `Route::livewire()` with the dot-notation reference. The string `pages::activity-requests.index` resolves to `resources/views/pages/activity-requests/index.blade.php`.

```php
// routes/web.php
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('auth.login'));

Route::livewire('/login', 'pages::auth.login')->name('auth.login');
Route::livewire('/verify-2fa', 'pages::auth.verify-2fa')->name('auth.verify-2fa');

Route::middleware('auth')->group(function () {
    Route::livewire('/activity-requests', 'pages::activity-requests.index')
        ->name('activity-requests.index');

    Route::livewire('/clients/{slug}', 'pages::clients.show')
        ->name('clients.view');

    Route::livewire('/badge-requests', 'pages::badge-requests.index')
        ->name('badge-requests.index');
});
```

**Forbidden:**
- `Route::get('/foo', SomeComponent::class)` — wrong v4 idiom
- `Route::get('/foo', fn () => view('foo'))` returning a Blade file that contains `<livewire:foo />` — adds a useless wrapper

## Navigation between pages

All cross-page links MUST use `wire:navigate` so transitions stay SPA-style (no full reload, layout preserved).

```blade
{{-- Plain anchor --}}
<a href="{{ route('clients.view', $client) }}" wire:navigate>{{ $client->name }}</a>

{{-- Button-as-link via the primitive --}}
<x-ui.button :href="route('activity-requests.form')" wire:navigate>
    Nouvelle demande
</x-ui.button>
```

From a Livewire method, redirect with the `navigate` flag:

```php
public function save(): void
{
    // ...
    $this->redirect(route('activity-requests.show', $request), navigate: true);
}
```

**Anti-pattern** — `wire:click` calling a method that only redirects:

```blade
{{-- ❌ Forces a server round-trip just to navigate --}}
<button wire:click="editDraft({{ $draft->id }})">Reprendre</button>

{{-- ✅ Direct navigation, no round-trip --}}
<a href="{{ route('activity-requests.form', $draft) }}" wire:navigate>Reprendre</a>
```

Exceptions (omit `wire:navigate`): external URLs, `mailto:` / `tel:`, same-page anchors (`#section`), file downloads, POST forms (e.g. logout).

Reference: https://livewire.laravel.com/docs/4.x/navigate

## Anatomy of a page component

```php
<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Client;

new
#[Layout('layouts::app')]
#[Title('Sociétés')]
class extends Component {
    public string $search = '';

    public function with(): array
    {
        return [
            'clients' => Client::query()
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->paginate(20),
        ];
    }
}; ?>

<div class="p-6 space-y-6">
    <header class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Sociétés</h1>
        <x-ui.button :href="route('clients.create')">Nouvelle société</x-ui.button>
    </header>

    <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Rechercher…" />

    <livewire:clients.list :clients="$clients" />
</div>
```

### Key points

- `#[Layout('layouts::app')]` — selects the layout (default is `layouts::app` if omitted)
- `#[Title('...')]` — sets `<title>` for the page
- The page component MAY embed other Livewire SFC via `<livewire:domain.name />`
- The page component MUST compose `<x-ui.*>` primitives for atomic UI

## Route parameters

### Implicit binding (preferred)

```php
// routes/web.php
Route::livewire('/clients/{client:slug}', 'pages::clients.show')->name('clients.view');
```

```php
<?php
use App\Models\Client;
use Livewire\Component;

new class extends Component {
    public Client $client; // auto-resolved via slug
}; ?>
```

### Explicit `mount()`

```php
new class extends Component {
    public Client $client;

    public function mount(string $slug): void
    {
        $this->client = Client::where('slug', $slug)->firstOrFail();
    }
};
```

## Layouts

Reference: https://livewire.laravel.com/docs/4.x/pages#layouts

Layout files MUST live in `resources/views/layouts/`. The `layouts` namespace is registered in `config/livewire.php` (`component_namespaces`), making them addressable as `layouts::app`, `layouts::auth`, etc.

Two layouts only in this project:

| Layout | Used for | File |
|---|---|---|
| `layouts::app` | Authenticated app pages (default) | `resources/views/layouts/app.blade.php` |
| `layouts::auth` | Login, 2FA, password reset | `resources/views/layouts/auth.blade.php` |

### Generating a layout

```bash
php artisan livewire:layout
```

This creates `resources/views/layouts/app.blade.php` if it doesn't exist. To create `auth.blade.php`, copy the generated `app.blade.php` and adapt it.

### Selecting the layout

**Per-page (preferred)** — via the `#[Layout]` PHP attribute:
```php
new
#[Layout('layouts::auth')]
#[Title('Connexion')]
class extends Component { /* ... */ };
```

**Per-page (alternative)** — from the `render()` method (only when using class-based components, which we don't):
```php
public function render() {
    return $this->view()->layout('layouts::auth');
}
```

**Globally** — via `component_layout` in `config/livewire.php`:
```php
'component_layout' => 'layouts::app',
```

### Layout file structure

A layout MUST include `{{ $slot }}` (where the page renders) and the Livewire directives `@livewireStyles` / `@livewireScripts`.

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="h-full bg-slate-50 text-slate-900 antialiased">
        {{ $slot }}
        @livewireScripts
    </body>
</html>
```

### Named layout slots

Beyond the default `{{ $slot }}`, layouts can expose additional named slots. For example, exposing a `$lang` slot in the layout:

```blade
<html lang="{{ str_replace('_', '-', $lang ?? app()->getLocale()) }}">
```

A page component can then populate it by placing an `<x-slot>` element OUTSIDE the root element of the template:

```blade
<x-slot:lang>fr</x-slot>
<div>
    <!-- page content -->
</div>
```

## Authorization

Page components are responsible for their own access control. The route's middleware (`auth`, `role:admin`) handles auth/role gates. For finer rules, use `mount()` with a `Gate::authorize(...)` or a redirect:

```php
public function mount(): void
{
    if (auth()->user()->isClient()) {
        $this->redirect(route('clients.view', auth()->user()->client->slug));
    }
}
```

## Embedded components

Page components frequently delegate sub-sections to nested Livewire SFC for reusability and isolation:

```blade
<div>
    <livewire:activity-requests.filters />
    <livewire:activity-requests.list />
</div>
```

## Testing

```php
public function test_clients_index_renders(): void
{
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('clients.index'))
        ->assertOk()
        ->assertSeeLivewire('pages::clients.index');
}
```
