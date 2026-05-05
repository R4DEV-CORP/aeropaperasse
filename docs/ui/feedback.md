# User feedback — Toasts

Reference: composant `<livewire:notifications.toast-stack />` monté globalement dans `resources/views/layouts/app.blade.php`.

Toute action utilisateur côté Livewire qui modifie l'état du système (création, mise à jour, suppression, transition de statut, envoi d'email, génération de document…) doit produire un **toast** — succès comme échec. C'est l'unique mécanisme de feedback transitoire dans ce projet.

## Règle

Après une action Livewire :

- **succès** → `$this->toast($message, 'success', $title)` (variant vert)
- **échec applicatif** (quota dépassé, doublon, etc.) → `$this->toast($message, 'danger', 'Erreur')` (variant rouge)
- **avertissement** non bloquant → `$this->toast($message, 'warning', $title)`
- **information** purement contextuelle → `$this->toast($message, 'info', $title)`

Le toast apparaît en bas-droite, ~6 secondes, dismissable au clic. Il survit aux navigations `wire:navigate` (le stack est dans un bloc `@persist` du layout).

## Comment l'appeler

### Depuis un composant Livewire (cas standard)

```php
use App\Livewire\Concerns\InteractsWithToasts;

new class extends Component
{
    use InteractsWithToasts;

    public function approve(int $id): void
    {
        // ... logique ...
        $this->toast('Demande approuvée avec succès.', 'success', 'Demande approuvée');
    }
}
```

Signature du helper :

```php
public function toast(
    string $message,
    string $variant = 'info',   // 'info' | 'success' | 'warning' | 'danger'
    ?string $title = null,
    int $ttl = 6000,            // durée d'affichage en ms (min 1000)
): void
```

Le helper appelle `$this->dispatch('toast', ...)` — le toast-stack persistant écoute et empile.

### Depuis un controller HTTP / un redirect non-Livewire

Pour les rares cas hors Livewire (redirect classique d'un controller, middleware, etc.), passer par le flash session — le toast-stack consomme `session()->pull('toast')` à chaque montage :

```php
session()->flash('toast', [
    'message' => 'Demande approuvée.',
    'variant' => 'success',
    'title'   => 'Bravo',
]);

return redirect()->route('activity-requests.index');
```

Plusieurs toasts en une fois :

```php
session()->flash('toast', [
    ['message' => 'Étape 1 OK', 'variant' => 'success'],
    ['message' => 'Étape 2 a généré un avertissement', 'variant' => 'warning'],
]);
```

> **Ne pas utiliser le flash session depuis un composant Livewire qui ne redirige pas** — le payload resterait en session jusqu'au prochain rendu et se déclencherait en double. Depuis un composant Livewire, utiliser exclusivement `$this->toast(...)`.

## Anti-patterns

| À ne pas faire | À faire |
|---|---|
| `session()->flash('message', '...')` dans un composant Livewire | `$this->toast(...)` |
| `$this->successMessage = '...'` dans une propriété (rendu manuel) | `$this->toast(...)` |
| `<x-ui.alert />` inline pour signaler un succès transitoire | `$this->toast(...)` |
| Toast pour une erreur de validation de formulaire | Laisser dans `$errors` et afficher via `:error="$errors->first('field')"` sur l'input |
| Toast d'information permanente (ex: "ce client n'a pas de quota") | `<x-ui.alert variant="warning">` dans la page (persistant) |

## Variants — quand choisir lequel

| Variant | Couleur | Cas typiques |
|---|---|---|
| `success` | emerald | Création, mise à jour, approbation, envoi d'email, export généré |
| `danger` | red | Erreur applicative attendue (quota, doublon, autorisation), exception remontée à l'utilisateur |
| `warning` | amber | Action partielle, état dégradé (ex: badge créé mais email non envoyé), action irréversible signalée |
| `info` | blue | Information neutre, rappel, état de fond |

## Erreurs de validation

Les erreurs de validation **ne sont pas des toasts**. Elles vivent dans `$errors`, sous le champ concerné, via :

```blade
<x-ui.input wire:model="email" :error="$errors->first('email')" />
```

Un toast peut compléter en cas d'échec global (ex: "Plusieurs champs sont invalides"), mais c'est l'exception, pas la règle.

## Tester

```php
// Depuis le composant qui dispatch
Livewire::test(MyComponent::class)
    ->call('approve', $id)
    ->assertDispatched('toast', message: 'Demande approuvée avec succès.', variant: 'success');

// Côté toast-stack (intégration)
session()->flash('toast', ['message' => 'Hi', 'variant' => 'success']);
Livewire::test('notifications.toast-stack')
    ->assertCount('toasts', 1)
    ->assertSee('Hi');
```

Voir `tests/Feature/Livewire/Notifications/ToastStackTest.php` pour la suite complète.

## Architecture

- **Primitive** : `resources/views/components/ui/toast.blade.php` (`<x-ui.toast />`) — atome stateless, 4 variants, slot, bouton fermer.
- **Stack** : `resources/views/components/notifications/⚡toast-stack.blade.php` — SFC qui empile, anime (Alpine `x-transition`), purge, lit `session('toast')` au mount, écoute l'événement `toast`.
- **Trait** : `app/Livewire/Concerns/InteractsWithToasts.php` — ergonomie pour `$this->toast(...)`.
- **Injection globale** : `resources/views/layouts/app.blade.php` → `@persist('toast-stack') <livewire:notifications.toast-stack /> @endpersist` avant `@livewireScripts`.

## Reference

- Primitive : `docs/ui/blade-primitives.md`
- SFC stack : `docs/ui/livewire-components.md`
- Architecture globale : `docs/ui/architecture.md`
