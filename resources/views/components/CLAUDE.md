# resources/views/components/

This directory holds **two distinct content types**, separated by subfolder:

| Subfolder | Content | Used as | Rules file |
|---|---|---|---|
| `components/ui/` | Stateless Blade primitives (button, input, alert…) | `<x-ui.button />` | `components/ui/CLAUDE.md` |
| `components/{domain}/` | Livewire Single-File Components (SFC), one folder per business domain | `<livewire:domain.name />` | this file |

The rules below apply to **Livewire SFC** in domain subfolders.

## Local rules (Livewire SFC)

- Each file is a **Livewire Single-File Component (SFC)** for Livewire v4: `<?php new class extends Component { ... }; ?>` followed by a Blade template.
- Components are grouped by business domain: `components/{domain}/⚡{name}.blade.php` (the ⚡ prefix is the Livewire v4 SFC convention — kept on in this project).
- Components MUST compose `<x-ui.*>` primitives — **never re-implement** buttons, inputs, alerts, etc. inline. If a primitive is missing, create it first in `resources/views/components/ui/`.
- Tags MUST be self-closed: `<livewire:domain.name />`, never `<livewire:domain.name>`.
- Single root element per template. Use `wire:key` in loops.
- Validate inputs and run authorization checks in component methods — they hit the backend like regular HTTP requests.
- Complex business logic belongs in `app/Actions/`, not in the component. Components orchestrate: validate → build DTO → call Action → handle Result.
- User-facing feedback after an action (success / failure / status change) → use the `InteractsWithToasts` trait and `$this->toast(...)`. Never use silent `session()->flash('message', ...)`. See `docs/ui/feedback.md`.
- **Cross-page links MUST use `wire:navigate`**: `<a href="{{ route(...) }}" wire:navigate>…</a>` or `<x-ui.button :href="route(...)" wire:navigate>…</x-ui.button>`. From a method, use `$this->redirect(route('...'), navigate: true)`. **Never** wire a `wire:click` to a method whose only job is to redirect — use a real link. See `docs/ui/architecture.md` → "Navigation between pages".

## Generating

```bash
php artisan make:livewire {domain}.{name}
```

Examples:
- `php artisan make:livewire activity-requests.create-form`
- `php artisan make:livewire badge-requests.list`

## Forbidden

- Class-based components (`app/Livewire/...`) — SFC only in this project.
- Re-implementing primitive HTML (button, input markup) instead of using `<x-ui.*>`.
- `DB::` queries — use Eloquent.
- Duplicating validation rules across components — extract to `app/Forms/`.

## Reference

- SFC anatomy, lifecycle hooks, computed properties, testing: `docs/ui/livewire-components.md`
- Available primitives: `docs/ui/blade-primitives.md`
- User feedback (toasts) after actions: `docs/ui/feedback.md`
- Application lifecycle (DTO + Action + Result pattern): root `CLAUDE.md` → "Request Lifecycle (Livewire)"
