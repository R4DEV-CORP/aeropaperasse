<div class="mt-8">
    <div class="grid grid-cols-4 gap-4 mt-4">
        <x-badge-info-card title="Demandes totales" value="15" bg-color="violet-200" />
        <x-badge-info-card title="En attente" value="15" bg-color="yellow-200" />
        <x-badge-info-card title="Approuvées" value="15" bg-color="green-200" />
        <x-badge-info-card title="Rejetées" value="15" bg-color="red-200" />
    </div>
    <div class="flex items-center gap-3 mt-4">
        <flux:input icon="magnifying-glass" placeholder="Rechercher une demande..." />
        <flux:button variant="primary" icon="plus">Nouvelle demande</flux:button>
        <flux:button icon="arrow-path">Actualiser</flux:button>
    </div>
    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200">
        <flux:heading size="lg" class="px-4">Demandes récentes</flux:heading>
        <div class="mt-4 border-t border-gray-800/10">
            <table class="min-w-full divide-y divide-slate-800/10">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">RAISON SOCIALE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">RESPONSABLE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">STATUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    <tr>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">Nom company</p>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">Corentin Sarda</p>
                        </td>
                        <td class="px-3 py-2">
                            <flux:badge color="blue" size="sm">En attente</flux:badge>
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
