<?php

use App\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::select')]
#[Title('Choisir un espace')]
class extends Component
{
    public string $search = '';

    /**
     * Tenants the authenticated user belongs to, each with the absolute URL of its
     * subdomain. Links are cross-domain (the session cookie is shared across
     * *.aeropaperasse.*), so picking one keeps the user authenticated on arrival.
     *
     * @var array<int, array{id: string, name: string, domain: string, url: string}>
     */
    public array $spaces = [];

    public function mount(): void
    {
        $user = auth()->user();
        $scheme = request()->getScheme();

        // REM staff manage every tenant (cross-tenant access), so they see the full list;
        // everyone else only sees the tenants carried on their `tenant_user` pivot.
        $tenants = $user->isRemStaff()
            ? Tenant::query()->with('domains')->get()
            : $user->tenants()->with('domains')->get();

        $this->spaces = $tenants
            ->map(function ($tenant) use ($scheme): ?array {
                $domain = $tenant->domains->first()?->domain;

                if ($domain === null) {
                    return null;
                }

                return [
                    'id' => (string) $tenant->getTenantKey(),
                    'name' => $tenant->name ?: $domain,
                    'domain' => $domain,
                    'url' => $scheme.'://'.$domain.'/',
                ];
            })
            ->filter()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * Spaces matching the search term (by name or domain). Recomputed on each
     * `wire:model.live` keystroke.
     *
     * @return array<int, array{id: string, name: string, domain: string, url: string}>
     */
    #[Computed]
    public function filteredSpaces(): array
    {
        $term = trim(mb_strtolower($this->search));

        if ($term === '') {
            return $this->spaces;
        }

        return array_values(array_filter(
            $this->spaces,
            fn (array $space): bool => str_contains(mb_strtolower($space['name']), $term)
                || str_contains(mb_strtolower($space['domain']), $term),
        ));
    }
}; ?>

<div class="space-y-6">
    <div class="space-y-1">
        <h1 class="text-2xl font-semibold text-foreground">Choisir un espace</h1>
        <p class="text-sm text-foreground-muted">
            Sélectionnez l'espace auquel vous souhaitez accéder.
        </p>
    </div>

    @if (count($spaces) === 0)
        <x-ui.alert variant="warning">
            Votre compte n'est rattaché à aucun espace. Contactez l'administrateur.
        </x-ui.alert>
    @else
        <x-ui.input
            wire:model.live.debounce.250ms="search"
            type="search"
            placeholder="Rechercher un espace…"
            autofocus
        />

        @php($filtered = $this->filteredSpaces())

        @if (count($filtered) === 0)
            <p class="text-sm text-foreground-muted">
                Aucun espace ne correspond à « <span class="font-medium">{{ $search }}</span> ».
            </p>
        @else
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($filtered as $space)
                    <a
                        href="{{ $space['url'] }}"
                        wire:key="tenant-{{ $space['id'] }}"
                        class="group rounded-lg border border-border bg-white p-4 transition hover:border-accent hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                    >
                        <span class="block font-medium text-foreground group-hover:text-accent">
                            {{ $space['name'] }}
                        </span>
                        <span class="mt-1 block text-xs text-foreground-subtle">
                            {{ $space['domain'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</div>
