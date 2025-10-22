<div class="mt-8">
    <div class="grid grid-cols-3 gap-4 mt-4">
        <x-badge-info-card title="Nombre de sociétés" value="1" bg-color="blue-200" />
        <x-badge-info-card title="Nombre de collaborateurs" value="1" bg-color="green-200" />
        <x-badge-info-card title="Arrivent à expiration (6 mois)" value="1" bg-color="yellow-200" />
    </div>
    <div class="flex items-center gap-3 mt-4">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher une société..."/>
        <flux:modal.trigger name="add-coworker-to-formation-modal">
            <flux:button variant="primary" icon="plus">Attribuer une formation</flux:button>
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
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">SOCIETE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">COLLABORATEURS</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">FORMATIONS ACTIVES</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @foreach($clients as $client)
                    <tr wire:loading.remove wire:target="search" wire:key="client-{{ $client->id }}" class="border-b border-slate-800/10">
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $client->company_name }}</p>
                            <flux:text>{{ $client->trade_name }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            <a href="/coworkers"><flux:badge as="button" variant="pill" icon="user" class="hover:cursor-pointer">{{ $client->coworkers->count() }}</flux:badge></a>
                        </td>
                        <td class="px-3 py-2">0</td>
                        <td class="px-3 py-2">
                            <div class="flex items-center">
                                <flux:button href="/trainings/client/{{ $client->slug }}" wire:navigate icon="eye" icon:variant="outline" variant="subtle" square="true" tooltip="Voir" color="blue" class="hover:cursor-pointer"/>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal attribution formation -->
    <flux:modal :dismissible="false" name="add-coworker-to-formation-modal" class="min-w-4xl !max-w-6xl">
        <livewire:training.add-coworker-to-training-form />
    </flux:modal>
</div>
