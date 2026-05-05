---
name: new-livewire-component
description: Scaffold a new Livewire Single-File Component in resources/views/components/{domain}/. Use when the user says "create a livewire component", "scaffold a form/list/widget", or any request to build a stateful UI piece embedded inside a page.
---

# new-livewire-component

Scaffolds a new Livewire SFC in `resources/views/components/{domain}/`.

## Inputs

- `domain` (required) — business domain in kebab-case (e.g. `activity-requests`, `badge-requests`, `coworkers`).
- `name` (required) — component name in kebab-case (e.g. `create-form`, `list`, `filters`).

## Steps

1. **Read the conventions first**: `docs/ui/livewire-components.md`, `resources/views/components/CLAUDE.md`, and at least one neighboring component in the same domain (or a similar one) to match style.

2. **Confirm the component is NOT a primitive**: buttons, inputs, alerts, badges go in `resources/views/components/ui/`, not here. If unclear, redirect to `new-ui-primitive`.

3. **Run the artisan generator** (preferred over manual file creation):

   ```bash
   php artisan make:livewire {domain}.{name} --no-interaction
   ```

   This creates `resources/views/components/{domain}/⚡{name}.blade.php` (single-file format by default — the ⚡ prefix is the project convention, do not strip it).

4. **Replace the scaffolded content** with this skeleton, adapted to the user's intent:

   ```php
   <?php

   use Livewire\Component;
   // use other classes as needed

   new class extends Component {
       public string $someProp = '';

       public function mount(): void
       {
           // initialization
       }

       public function someAction(): void
       {
           $this->validate([
               'someProp' => ['required', 'string'],
           ]);

           // delegate to an Action from app/Actions/ for non-trivial logic
       }
   }; ?>

   <div class="space-y-4">
       <x-ui.input wire:model="someProp" label="..." />
       <x-ui.button wire:click="someAction" variant="primary">Submit</x-ui.button>
   </div>
   ```

5. **Always**:
   - Compose `<x-ui.*>` primitives — never re-implement HTML for buttons/inputs/alerts.
   - Single root element in the template.
   - Add `wire:key` in any loop.
   - Add type hints and explicit return types on all methods (project rule).
   - For non-trivial business logic, delegate to an Action in `app/Actions/` and pass a DTO from `app/DataTransferObjects/`.
   - For complex form validation, use a validator class from `app/Forms/`.
   - **Cross-page links use `wire:navigate`**: `<a href="{{ route('...') }}" wire:navigate>` or `<x-ui.button :href="route('...')" wire:navigate>`. From a method, use `$this->redirect(route('...'), navigate: true)`. Never write a `wire:click` whose only job is to call `$this->redirect(...)` — use a real link instead. Exceptions: external URLs, `mailto:`/`tel:`, `#anchors`, downloads, POST forms.

6. **Render it** with `<livewire:{domain}.{name} />` (self-closing — Livewire v4 requires this).

7. **Generate a test** when relevant:
   ```bash
   php artisan make:test {Domain}{Name}Test --no-interaction
   ```
   Use `Livewire::test('{domain}.{name}')` syntax.

8. **Run `vendor/bin/pint --dirty`** before finalizing.

## Forbidden during scaffolding

- Class-based components (`app/Livewire/...`) — SFC only.
- Re-implementing primitive HTML inline.
- `DB::` queries — use Eloquent.
- `<flux:*>` components (Flux is removed).
- Heavy business logic in component methods — extract to Actions.

## Reference

- `docs/ui/livewire-components.md` — full SFC patterns, lifecycle hooks, computed, testing
- `resources/views/components/CLAUDE.md` — local rules
- Root `CLAUDE.md` → "Request Lifecycle (Livewire)" — DTO + Action + Result pattern
