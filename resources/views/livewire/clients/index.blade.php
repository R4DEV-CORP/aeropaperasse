<div class="mt-8">
    <div class="flex items-center gap-3 mt-4">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher une société..."/>
        <flux:modal.trigger name="new-client">
            <flux:button variant="primary" icon="plus">Nouvelle société</flux:button>
        </flux:modal.trigger>
    </div>
    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200 relative">
        <flux:heading size="lg" class="px-4">Sociétés</flux:heading>
        
         <!-- Indicateur de chargement -->
         <div wire:loading wire:target="search" 
             class="absolute top-[450px] left-1/2 transform -translate-x-1/2 bg-white/80 flex items-center justify-center z-10 rounded-xl px-4 py-2 shadow-lg">
            <div class="flex items-center gap-2 text-slate-600">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium">Chargement...</span>
            </div>
        </div>
        
        <div class="mt-4 border-t border-gray-800/10">
            <table class="min-w-full divide-y divide-slate-800/10">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">NOM</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">COLLABORATEURS</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE DE CREATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    <!-- Résultats de la recherche -->
                    @foreach($clients as $client)
                    <tr wire:loading.remove wire:target="search">
                        <td class="px-3 py-2" wire:click="developpeClient({{ $client->id }})">
                            <div class="flex items-center gap-2">
                                <div>
                                    <p class="text-gray-800 font-medium">{{ $client->company_name }}</p>
                                    <flux:text>{{ $client->trade_name }}</flux:text>
                                </div>
                                @if($openClientId != $client->id)
                                    <flux:icon.chevron-right class="size-4 hover:cursor-pointer hover:text-gray-500"/>
                                @else
                                    <flux:icon.chevron-down class="size-4 hover:cursor-pointer hover:text-gray-500"/>
                                @endif
                            </div>

                        </td>
                        <td class="px-3 py-2">
                            <a href="/coworkers"><flux:badge as="button" variant="pill" icon="user" class="hover:cursor-pointer">{{ $client->coworkers->count() }}</flux:badge></a>
                        </td>
                        <td class="px-3 py-2">{{ $client->created_at->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">
                            <div class="flex items-center">
                                <flux:button href="/clients/{{ $client->slug }}" wire:navigate icon="eye" icon:variant="outline" variant="subtle" square="true" tooltip="Voir" color="blue" class="hover:cursor-pointer"/>
                                @if(auth()->user()->isAdmin())
                                    <flux:modal.trigger :name="'edit-client-'.$client->id">
                                        <flux:button variant="subtle" icon="pencil-square" icon:variant="outline" square="true" tooltip="Modifier" class="!text-blue-500 hover:cursor-pointer"/>
                                    </flux:modal.trigger>
                                    <!-- Modal edition -->
                                    <flux:modal :name="'edit-client-'.$client->id" class="min-w-4xl !max-w-6xl" wire:key="edit-client-modal-{{ $client->id }}">
                                        <livewire:clients.edit-client-form :clientId="$client->id" />
                                    </flux:modal>
                                    <flux:button variant="subtle" icon="trash" icon:variant="outline" square="true" tooltip="Supprimer la société" class="!text-red-500 hover:cursor-pointer"/>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @if($openClientId == $client->id)
                    <tr wire:loading.remove wire:target="search" wire:key="developped-client-{{ $client->id }}" class="border-b border-slate-800/10">
                        <td colspan="2" class="px-3 py-2">
                            <div class="mt-4 p-4 bg-white rounded-lg border border-zinc-200">
                                <div class="flex justify-between">
                                    <flux:heading size="lg">Quota de bagdes</flux:heading>
                                    <flux:text>{{ $client->getActiveBadgeCount() }}/{{ $client->badge_limit }}</flux:text>
                                </div>
                                <div class="bg-slate-200 h-3 rounded-full w-full mt-4">
                                    <div class="h-full bg-green-600 rounded-full" style="width: {{ $client->getActiveBadgeCount() / $client->badge_limit * 100 }}%"></div>
                                </div>
                                <flux:text class="mt-2">La société dispose de <span class="font-medium">{{ $client->getActiveBadgeCount() }} badges.</span> Il leur reste donc <span class="font-medium">{{ $client->badge_limit - $client->getActiveBadgeCount() }} demandes de badge disponibles.</span></flux:text>
                            </div>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal création société -->
    <flux:modal name="new-client" :dismissible="false" class="min-w-4xl !max-w-6xl">
        <livewire:clients.create-client-form />
    </flux:modal>
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('close-modal', (event) => {
        const modalName = event.modal;
        if (modalName) {
            // Fermer la modal spécifique
            const modal = document.querySelector(`[data-modal="${modalName}"]`);
            if (modal) {
                modal.close();
            }
        }
    });
});
</script>