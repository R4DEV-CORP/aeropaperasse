# Blade Primitives — `resources/views/components/ui/`

Primitives are **stateless**, reusable Blade components. They are the only allowed UI atoms in this project. Every Livewire component composes primitives — it never re-implements them.

The location `resources/views/components/ui/` is chosen so Laravel's native anonymous component resolver maps `<x-ui.{name} />` directly to `resources/views/components/ui/{name}.blade.php` without any service provider registration.

## Strict rules

- ✅ Pure Blade with `@props([...])`
- ✅ Tailwind v4 utility classes only
- ✅ Forward arbitrary attributes via `{{ $attributes->merge([...]) }}`
- ✅ Start every primitive with `@blaze` (see "Performance: @blaze" below)
- ❌ NO `dark:*` variant classes — single-theme project (light only)
- ❌ NO `wire:*` directives
- ❌ NO `@livewire(...)` or `<livewire:...>` inside primitives
- ❌ NO server-side data fetching, no Eloquent calls
- ❌ NO Alpine state that depends on Livewire (pure Alpine is fine for local UI state, e.g. menu open/closed)

If you find yourself needing server state, **stop** — extract a Livewire SFC in `components/{domain}/` that *uses* the primitive shell.

## Naming and location

```
resources/views/components/ui/
├── button.blade.php           → <x-ui.button />
├── input.blade.php            → <x-ui.input />
├── alert.blade.php            → <x-ui.alert />
├── badge.blade.php            → <x-ui.badge />
├── modal.blade.php            → <x-ui.modal />
└── form/
    ├── field.blade.php        → <x-ui.form.field />
    └── label.blade.php        → <x-ui.form.label />
```

Files: kebab-case. Tags: `<x-ui.{name} />`.

## Anatomy

### Minimal primitive — `button.blade.php`

```blade
@blaze

@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $base = 'inline-flex items-center justify-center font-medium rounded transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'bg-accent text-accent-foreground hover:bg-accent-content focus:ring-accent',
        'secondary' => 'bg-white text-slate-900 ring-1 ring-slate-200 hover:bg-slate-50',
        'ghost' => 'text-slate-700 hover:bg-slate-100',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
    ];

    $classes = "$base {$variants[$variant]} {$sizes[$size]}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
```

Usage:

```blade
<x-ui.button>Enregistrer</x-ui.button>
<x-ui.button variant="danger" wire:click="delete">Supprimer</x-ui.button>
<x-ui.button :href="route('clients.index')" wire:navigate variant="ghost">Retour</x-ui.button>
```

Note: even though primitives don't *contain* Livewire directives, they MUST forward attributes (via `$attributes->merge`) so consumers can attach `wire:click`, `wire:loading`, `wire:navigate`, etc. from the outside.

**Cross-page links** — when a button-as-link or any `<a>` navigates to another app page, always pair `:href` (or `href`) with `wire:navigate`. See `docs/ui/architecture.md` → "Navigation between pages".

### Form primitive with named slots — `input.blade.php`

```blade
@blaze

@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'id' => null,
])

@php
    $id = $id ?? 'input-' . uniqid();
    $hasError = ! empty($error);
    $borderClass = $hasError
        ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
        : 'border-slate-200 focus:border-accent focus:ring-accent';
@endphp

<div class="space-y-1">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $id }}"
        {{ $attributes->merge([
            'class' => "block w-full rounded bg-slate-50 focus:bg-white sm:text-sm $borderClass",
        ]) }}
    />

    @if ($hint && ! $hasError)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @if ($hasError)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
```

Usage from a Livewire component:

```blade
<x-ui.input
    wire:model="email"
    label="Email"
    type="email"
    :error="$errors->first('email')"
/>
```

## Conventions

- Start with `@blaze` (function-compiler optimization — see below)
- Define `@props([...])` directly under `@blaze` with sensible defaults
- Use `@php ... @endphp` for class concatenation logic
- Use a `$variants` / `$sizes` map pattern for variant-driven styling
- Always merge `$attributes` so consumers can pass `wire:*`, `id`, `data-*`
- Support both `href` (renders `<a>`) and `type` (renders `<button>`) where applicable
- Forward `{{ $slot }}` for content
- Use named slots (`<x-slot:icon>`) for multi-region primitives (e.g. cards with header/body/footer)
- **Never** use `dark:*` variant classes (single-theme project)

## Performance: `@blaze`

This project uses [livewire/blaze](https://github.com/livewire/blaze) to compile anonymous Blade primitives into optimized PHP functions (97% rendering overhead reduction in benchmarks). Every primitive in `resources/views/components/ui/` MUST opt in via the `@blaze` directive at the very top of the file.

### Default — function compiler

```blade
@blaze

@props([...])

...
```

The plain `@blaze` directive enables the **function compiler** strategy. It is safe for any primitive in this project — no caveats — and should be the default everywhere in `components/ui/`.

### `@blaze(memo: true)` — runtime memoization

Cache repeated renders by component name + props. Use ONLY for very-frequently-rendered, slot-less primitives where the same props yield the same HTML — typically icons:

```blade
@blaze(memo: true)

@props(['name'])

<x-dynamic-component :component="'icon-' . $name" />
```

> **Hard limitation:** memoization does not work on components with slots. If your primitive uses `{{ $slot }}`, do not use `memo: true`.

### `@blaze(fold: true)` — compile-time folding

Pre-renders the component to static HTML at compile time. Powerful but risky — use only when the component's output depends ONLY on its props.

**NEVER fold a primitive that touches global state**:
- Database / Eloquent queries
- `auth()->check()`, `@auth`, `@can`
- `session('...')`, `request()->...`
- Validation errors (`$errors->...`)
- `now()`, `today()`, `Carbon::...`
- CSRF tokens (`csrf_field()`, `@csrf`)

In practice, primitives in this project rarely qualify for `fold: true` because:
- Form primitives read `$errors` (input, select, textarea, otp-input).
- `input.blade.php` calls `uniqid()` per render — not foldable.
- Anything pulling theme tokens at runtime is fine to fold, but the win is small vs. the function compiler.

When in doubt, stick with plain `@blaze`.

#### Selective folding — `safe`, `unsafe`, `@unblaze`

If you DO fold and need to keep one prop dynamic (pass-through):

```blade
@blaze(fold: true, safe: ['level'])

@props(['level' => 1])

<h{{ $level }}>{{ $slot }}</h{{ $level }}>
```

For dynamic blocks inside a folded primitive (e.g. error display), wrap them with `@unblaze`:

```blade
@blaze(fold: true)

@props(['name', 'label'])

<div>
    <label>{{ $label }}</label>
    <input name="{{ $name }}">

    @unblaze(scope: ['name' => $name])
        @if ($errors->has($scope['name']))
            {{ $errors->first($scope['name']) }}
        @endif
    @endunblaze
</div>
```

### Things Blaze does NOT support

Blaze is anonymous-Blade-only. It does not optimize:
- Class-based components
- The `$component` variable
- View composers / creators / lifecycle events
- Auto-injected `View::share()` variables
- Cross-boundary `@aware` between Blade and Blaze components
- Components rendered via the `view()` function

None of these patterns are used by primitives in `components/ui/`, so this constraint is fine in practice.

### Debug mode

To profile primitive rendering locally, set `BLAZE_DEBUG=true` in `.env` (or call `Blaze::debug()` in a service provider). This adds an overlay with render times and a flame chart. Do not commit `BLAZE_DEBUG=true`.

## Inventory

The primitives currently available in `resources/views/components/ui/` (run `ls resources/views/components/ui` for the authoritative list):

`alert`, `badge`, `breadcrumb`, `button`, `card`, `checkbox`, `dropdown`, `dropdown-item`, `file-upload`, `flyout`, `input`, `modal`, `otp-input`, `radio-cards`, `segmented`, `select`, `split-button`, `stat-card`, `table`, `textarea`, `toast`, `tooltip`.

Build new primitives on demand, not preemptively. Before creating one, check that no existing primitive already covers the need.

## Testing primitives

Primitives are tested through the Livewire components that consume them. There's no dedicated test layer for primitives — if a button renders correctly inside a form component test, that's enough.

If you need to test rendering in isolation:

```php
public function test_button_renders_with_variant(): void
{
    $rendered = Blade::render('<x-ui.button variant="danger">Delete</x-ui.button>');

    $this->assertStringContainsString('bg-red-600', $rendered);
    $this->assertStringContainsString('Delete', $rendered);
}
```
