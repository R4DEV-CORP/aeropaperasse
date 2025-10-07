<div class="mt-8">
    <div class="flex items-center gap-3 mt-4">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher une société..."/>
        <flux:modal.trigger name="new-client">
            <flux:button variant="primary" icon="plus">Nouvelle société</flux:button>
        </flux:modal.trigger>

        <flux:button icon="arrow-path">Actualiser</flux:button>
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
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">SIRET</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">COLLABORATEURS</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE DE CREATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    <!-- Résultats de la recherche -->
                    @foreach($clients as $client)
                    <tr wire:loading.remove wire:target="search">
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $client->company_name }}</p>
                            <flux:text>{{ $client->trade_name }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $client->siret_number }}</p>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $client->users->count() }}</p>
                        </td>
                        <td class="px-3 py-2">{{ $client->created_at->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">   
                                <flux:icon.eye class="size-6"/>    
                                <flux:icon.check-circle class="size-6" />    
                                <flux:icon.x-circle class="size-6" />   
                                <flux:icon.arrow-down-tray class="size-6" /> 
                                <flux:icon.arrow-left-circle class="size-6" />
                                <flux:icon.wrench class="size-6" />
                            </div>
                        </td>
                    </tr>
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