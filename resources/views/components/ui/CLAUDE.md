# resources/views/components/ui/

You are working inside the **Blade primitives** directory. The rules here are non-negotiable.

This directory lives under `resources/views/components/` so Laravel's native anonymous component resolver maps `<x-ui.{name} />` directly to `resources/views/components/ui/{name}.blade.php` — no service provider registration needed.

## Local rules

- This directory contains **stateless** Blade components only.
- Files are pure Blade with `@props([...])` — no PHP class.
- Every primitive MUST start with `@blaze` (function-compiler optimization via [livewire/blaze](https://github.com/livewire/blaze)). The directive sits on line 1, above `@props`. Do not use `@blaze(memo: true)` on slotted primitives, and do not use `@blaze(fold: true)` on anything that reads `$errors`, `auth()`, session, request, `now()`, or calls `uniqid()` — see `docs/ui/blade-primitives.md` → "Performance: @blaze".
- **NEVER** use `wire:*` directives, `@livewire(...)`, or `<livewire:...>` in any file here. If state is needed, the file does not belong here — it belongs in a domain subfolder of `components/` (Livewire SFC).
- All primitives MUST forward attributes via `{{ $attributes->merge([...]) }}` so consumers can attach `wire:click`, `id`, `data-*`, etc. from the outside.
- Tailwind v4 utility classes only. **Single light theme** — never use `dark:*` variant classes.
- Use semantic theme tokens (`bg-accent`, `text-accent-foreground`) over raw palette values when applicable. Tokens are defined in `resources/css/app.css`.

## Naming

- Files: kebab-case (`button.blade.php`, `alert.blade.php`).
- Tags: `<x-ui.{name} />` (e.g. `<x-ui.button>`).
- Subdirectories allowed for grouping (e.g. `components/ui/form/field.blade.php` → `<x-ui.form.field />`).

## Reference

- Patterns and full inventory: `docs/ui/blade-primitives.md`
- Theme tokens and theme rules: `docs/ui/styling.md`
- The big picture (why this separation exists): `docs/ui/architecture.md`
