# Livewire Components — Single-File Component (SFC)

Reference: https://livewire.laravel.com/docs/4.x/components#creating-components

All stateful UI in this project uses the **Single-File Component** format. PHP class and Blade template live together in one `.blade.php` file under `resources/views/components/{domain}/`.

## Generating

```bash
php artisan make:livewire {domain}.{name}
```

Examples:

```bash
php artisan make:livewire activity-requests.create-form
php artisan make:livewire badge-requests.index
php artisan make:livewire coworkers.edit
```

Output: `resources/views/components/{domain}/⚡{name}.blade.php`.

**Convention de ce projet** : on garde le préfixe ⚡ que Livewire v4 ajoute automatiquement aux SFC (`make_command.emoji => true` dans `config/livewire.php`). Tous les fichiers SFC sont préfixés par ⚡ — c'est la signature visuelle Livewire et ça permet de les distinguer des autres `.blade.php`. Lors d'une recherche / `Glob`, penser à inclure le glyphe : `resources/views/components/**/⚡*.blade.php`.

## Anatomy of an SFC

```php
<?php

use Livewire\Component;
use App\Models\ActivityRequest;

new class extends Component {
    public string $title = '';
    public ?ActivityRequest $request = null;

    public function mount(?ActivityRequest $request = null): void
    {
        $this->request = $request;
        $this->title = $request?->title ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        // ... call an Action from app/Actions/, dispatch events, etc.
    }
}; ?>

<div class="space-y-4">
    <x-ui.input wire:model="title" label="Titre" />

    <div class="flex justify-end gap-2">
        <x-ui.button wire:click="save" variant="primary">
            Enregistrer
        </x-ui.button>
    </div>
</div>
```

### Key points

- `<?php ... ?>` block at the very top with `new class extends Component { ... };`
- The class is **anonymous** — no class name, no namespace
- Public properties are reactive and serialized to the frontend
- Protected properties are server-only (use them for keys, internal state)
- Methods become callable from the template via `wire:click`, `wire:submit`, etc.
- The Blade template MUST have a single root element (here `<div>`)
- The template MUST compose `<x-ui.*>` primitives for all atomic UI

## Rendering an SFC

```blade
<livewire:activity-requests.create-form />
<livewire:activity-requests.create-form :request="$activityRequest" />
```

Tags MUST be self-closing in v4 — `<livewire:foo />`, never `<livewire:foo>`.

## Lifecycle hooks

```php
new class extends Component {
    public string $search = '';

    public function mount(): void
    {
        // Run once when the component is first instantiated
    }

    public function updatedSearch(): void
    {
        // Reactive: runs every time $search changes
        $this->resetPage();
    }

    public function hydrate(): void
    {
        // Runs on every subsequent request to the component
    }
};
```

## Computed properties

```php
use Livewire\Attributes\Computed;

new class extends Component {
    #[Computed]
    public function items(): \Illuminate\Database\Eloquent\Collection
    {
        return Item::query()->latest()->get();
    }
};
```

In the template: `{{ $this->items }}` (the `$this->` prefix is required for computed properties).

## Validation

Use Livewire's built-in validation, or delegate to `app/Forms/{Name}FormValidator.php` for complex forms (existing convention in this project).

```php
public function save(): void
{
    $this->validate([
        'title' => ['required', 'string', 'max:255'],
    ]);
}
```

## Calling Actions

Per the project lifecycle, complex business logic belongs in `app/Actions/`. Livewire methods should only:

1. Validate
2. Build the DTO (`app/DataTransferObjects/`)
3. Call the Action
4. Handle the Result (success → redirect/dispatch, failure → set error state)

```php
public function save(SaveActivityRequestAction $action): void
{
    $this->validate([...]);

    $data = CreateActivityRequestData::fromComponent($this);
    $result = $action->execute($data);

    if ($result->failed()) {
        $this->addError('form', $result->message);
        return;
    }

    $this->dispatch('activity-request-created', id: $result->request->id);
    $this->redirect(route('activity-requests.show', $result->request), navigate: true);
}
```

## Loops require `wire:key`

```blade
@foreach ($items as $item)
    <div wire:key="item-{{ $item->id }}">
        <x-ui.badge>{{ $item->name }}</x-ui.badge>
    </div>
@endforeach
```

## Loading and dirty states

Use Blade primitives that already integrate `wire:loading` / `wire:dirty` (see `<x-ui.button>`), or apply directly:

```blade
<button wire:click="save" wire:loading.attr="disabled" wire:target="save">
    <span wire:loading.remove wire:target="save">Enregistrer</span>
    <span wire:loading wire:target="save">Enregistrement…</span>
</button>
```

## Forbidden in Livewire components

- Re-implementing primitives (button HTML, input HTML, alert HTML) — use `<x-ui.*>` instead
- Embedding business logic that should be in an Action
- Direct `DB::` queries — use Eloquent
- Inline validation rules duplicated across components — extract to a Form class

## Testing

```php
use App\Models\User;
use Livewire\Livewire;

public function test_create_form_saves_request(): void
{
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('activity-requests.create-form')
        ->set('title', 'My request')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('activity-requests.index'));
}
```

For testing the existence of the component on a page:

```php
$this->get('/activity-requests')
    ->assertSeeLivewire('activity-requests.index');
```
