<div class="mt-8">
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" />
    @endif
    <div class="grid grid-cols-4 gap-4 mt-4">
        <x-badge-info-card title="Demandes totales" value="0" bg-color="blue-200" />
        <x-badge-info-card title="En attente" value="0" bg-color="yellow-200" />
        <x-badge-info-card title="Approuvées" value="0" bg-color="green-200" />
        <x-badge-info-card title="Rejetées" value="0" bg-color="red-200" />
    </div>
    <div class="flex items-center gap-3 mt-4">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher une demande..." />
        <flux:modal.trigger name="new-vehicle-pass-request">
            <flux:button variant="primary" icon="document-plus">Nouvelle demande</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200">
        <flux:heading size="lg" class="px-4">Demande de laissez-passer</flux:heading>
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
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">AEROPORT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">VEHICULE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">STATUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE DE CREATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @foreach($vehiclePasses as $vehiclePass)
                    <tr>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $vehiclePass->client->company_name }}</p>
                            <flux:text>{{ $vehiclePass->client->trade_name }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            @switch($vehiclePass->airport)
                                @case('CDG')
                                    <flux:badge color="cyan" size="sm">CDG</flux:badge>
                                    @break
                                @case('ORY')
                                    <flux:badge color="violet" size="sm">ORY</flux:badge>
                                    @break
                                @case('LBG')
                                    <flux:badge color="lime" size="sm">LBG</flux:badge>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-3 py-2">
                            <flux:badge icon="hashtag" size="sm">{{ $vehiclePass->plate_number }}</flux:badge>
                            <flux:text>{{ $vehiclePass->car_brand }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            @switch($vehiclePass->status)
                                @case('pending')
                                    <flux:badge icon="clock" color="yellow" size="sm">En attente</flux:badge>
                                    @break
                                @case('approved')
                                    <flux:badge icon="check-circle" color="green" size="sm">Approuvé</flux:badge>
                                    @break
                                @case('rejected')
                                    <flux:badge icon="x-circle" color="red" size="sm">Rejeté</flux:badge>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-3 py-2">{{ $vehiclePass->created_at->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">
                            <flux:button variant="primary" icon="eye" size="sm">Voir</flux:button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
     <!-- Modal création vehicle pass -->
     <flux:modal name="new-vehicle-pass-request" :dismissible="false" class="min-w-4xl !max-w-6xl">
        <livewire:vehicle-pass.create-vehicle-pass-form />
    </flux:modal>
</div>
