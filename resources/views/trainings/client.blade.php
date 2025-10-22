<x-layouts.app>
    <div>
        <flux:heading size="xl">Gestion des formations</flux:heading>
        <flux:text class="mt-2">Gérez toutes les formations de vos collaborateurs depuis cette interface</flux:text>
    </div>
    <livewire:training.show :slug="$slug" />
</x-layouts.app>