---
name: new-ui-primitive
description: Scaffold a new stateless Blade primitive in resources/views/components/ui/. Use when the user says "create a new UI primitive", "add an x-ui.* component", "scaffold a button/input/alert/badge", or any request to build a stateless, reusable Blade UI atom.
---

# new-ui-primitive

Scaffolds a new Blade primitive in `resources/views/components/ui/`.

## Inputs

- `name` (required) — kebab-case primitive name (e.g. `button`, `alert`, `form-field`).
- Optional sub-path for grouping (e.g. `form/field` → `resources/views/components/ui/form/field.blade.php`).

## Steps

1. **Read the conventions first**: `docs/ui/blade-primitives.md` and `docs/ui/styling.md`. Match the existing primitives in `resources/views/components/ui/` for prop naming and variant maps.

2. **Confirm the primitive belongs in `components/ui/`**: it must be stateless (no `wire:*`, no Eloquent, no server data). If state is needed, redirect the user toward `new-livewire-component` instead.

3. **Create the file** at `resources/views/components/ui/{path}/{name}.blade.php` using this skeleton:

   ```blade
   @blaze

   @props([
       'variant' => 'default',
       // ... other props with defaults
   ])

   @php
       $base = '...';
       $variants = [
           'default' => '...',
           // ...
       ];
       $classes = "$base {$variants[$variant]}";
   @endphp

   <element {{ $attributes->merge(['class' => $classes]) }}>
       {{ $slot }}
   </element>
   ```

4. **Always**:
   - Start the file with `@blaze` on line 1 (function-compiler optimization via [livewire/blaze](https://github.com/livewire/blaze)). Plain `@blaze` is the safe default — do not use `memo:` or `fold:` unless the primitive's constraints clearly fit (see `docs/ui/blade-primitives.md` → "Performance: @blaze").
   - Define `@props([...])` directly under `@blaze` with sensible defaults.
   - Use `{{ $attributes->merge([...]) }}` so consumers can pass `wire:*`, `id`, `data-*`, `class`.
   - **Never** use `dark:*` variant classes (single light theme — project rule).
   - Use semantic tokens (`bg-accent`, `text-accent-foreground`) where possible.
   - Forward `{{ $slot }}` for content. Use named slots for multi-region primitives.

5. **Show a usage example** in the response so the user can copy-paste it into a component.

6. **Run `vendor/bin/pint --dirty`** if any PHP was touched (rare for primitives, but possible if helpers were edited).

## Forbidden during scaffolding

- Adding `wire:*` directives.
- Importing or referencing Livewire.
- Inline Eloquent or HTTP calls.
- Hard-coded raw color values when a theme token exists.

## Reference

- `docs/ui/blade-primitives.md` — patterns and full inventory
- `docs/ui/styling.md` — tokens, theme rules, spacing
