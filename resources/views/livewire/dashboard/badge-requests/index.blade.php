<div class="mt-8">
    <div class="flex items-center gap-3">
        <flux:input icon="magnifying-glass" placeholder="Rechercher une demande..." />
        <flux:button variant="primary" icon="plus">Nouvelle demande</flux:button>
        <flux:button icon="arrow-path">Actualiser</flux:button>
    </div>
    <div class="mt-4 p-4 bg-white rounded-lg border border-zinc-200">
        <div class="flex justify-between">
            <flux:heading size="lg">Quota de bagdes</flux:heading>
            <flux:text>2/9</flux:text>
        </div>
        <div class="bg-slate-200 h-3 rounded-full w-full mt-4">
            <div class="h-full w-1/2 bg-green-600 rounded-full"></div>
        </div>
        <flux:text class="mt-2">Vous disposez de <span class="font-medium">2 badges.</span> Il vous reste donc <span class="font-medium">7 demandes de badge disponibles.</span></flux:text>
    </div>
    <div class="grid grid-cols-4 gap-4 mt-4">
        <x-badge-info-card title="En attente REM" value="15" bg-color="yellow-200" />
        <x-badge-info-card title="En attente ADP" value="15" bg-color="amber-200" />
        <x-badge-info-card title="Approuvé ADP" value="15" bg-color="green-200" />
        <x-badge-info-card title="En fabrication" value="15" bg-color="lime-200" />
        <x-badge-info-card title="Rejetté REM" value="15" bg-color="red-200" />
        <x-badge-info-card title="Rejetté ADP" value="15" bg-color="red-200" />
        <x-badge-info-card title="Prêt à être Remis" value="15" bg-color="blue-200" />
        <x-badge-info-card title="Demandes totales" value="15" bg-color="violet-200" />
    </div>
    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200">
        <flux:heading size="lg" class="px-4">Demandes récentes</flux:heading>
        <div class="mt-4 border-t border-gray-800/10">
            <table class="min-w-full divide-y divide-slate-800/10">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ID</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DEMANDEUR</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">CONTACT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">STATUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    <tr>
                        <td class="px-3 py-2">1</td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">Dupin Nicolas</p>
                            <flux:text>Aéro drame</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">Dupin Nicolas</p>
                            <flux:text>0674859641</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            <flux:badge color="yellow" size="sm">Attente REM</flux:badge>
                        </td>
                        <td class="px-3 py-2">19/08/2025</td>
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
                </tbody>
            </table>
        </div>
    </div>
</div>
