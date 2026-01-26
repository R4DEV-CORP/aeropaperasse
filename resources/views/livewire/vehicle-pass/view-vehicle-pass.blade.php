<div class="space-y-4">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">Demande de laissez passer</flux:heading>
    </div>
    <flux:callout icon="information-circle" variant="secondary">
        <flux:callout.heading>Statut</flux:callout.heading>
        <flux:callout.text>
            @switch($vehiclePass->status)
                @case('pending')
                    <flux:badge icon="clock" color="yellow">En attente</flux:badge>
                    @break
                @case('approved')
                    <flux:badge icon="check-circle" color="green">Approuvé</flux:badge>
                    @break
                @case('rejected')
                <div class="flex items-center gap-2">
                    <flux:badge icon="x-circle" color="red">Rejeté</flux:badge>
                    <flux:text>Raison du rejet : {{ $vehiclePass->reject_reason }}</flux:text>
                </div>
                    @break
            @endswitch
        </flux:callout.text>
    </flux:callout>
    <div class="grid grid-cols-2 gap-2 border border-gray-800/10 p-4 rounded-lg">
        <div>
            <flux:heading size="lg">Véhicule</flux:heading>
            <p class="text-gray-800 font-medium mt-2">Immatriculation</p>
            <flux:text>{{ $vehiclePass->plate_number }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Marque</p>
            <flux:text>{{ $vehiclePass->car_brand }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Aéroport</p>
            @switch($vehiclePass->airport)
                @case('CDG')
                    <flux:badge color="cyan" size="sm">CDG</flux:badge>
                    @break
                @case('ORY')
                    <flux:badge color="violet" size="sm">ORY</flux:badge>
                    @break
            @endswitch
        </div>
        <div>
            <flux:heading size="lg">Informations sur la demande de laissez passer</flux:heading>
            <p class="text-gray-800 font-medium mt-2">Date de création</p>
            <flux:text>{{ $vehiclePass->created_at->format('d/m/Y') }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Dernière mise à jour</p>
            <flux:text>{{ $vehiclePass->updated_at->format('d/m/Y H:i') }}</flux:text>
        </div>
    </div>
    <div>
        <flux:heading size="lg">Documents</flux:heading>
        @if($vehiclePass->certificate_of_registration)
            <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                <flux:callout.heading>Certificat d'immatriculation</flux:callout.heading>
                @if(auth()->user()->isAdmin())
                <x-slot name="actions">
                    <div class="flex gap-2">
                        @if($this->canViewDocument('certificate_of_registration'))
                            <flux:button 
                                variant="ghost" 
                                icon="eye"
                                wire:click="viewDocument('certificate_of_registration')"
                                wire:loading.attr="disabled"
                            >
                                Voir
                            </flux:button>
                        @endif
                        <flux:button 
                            variant="ghost" 
                            icon="document-arrow-down"
                            wire:click="downloadDocument('certificate_of_registration')"
                            wire:loading.attr="disabled"
                        >
                            Télécharger
                        </flux:button>
                    </div>
                </x-slot>
                @endif
            </flux:callout>
        @endif
        @if($vehiclePass->company_stamp)
            <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                <flux:callout.heading>Tampon de l'entreprise</flux:callout.heading>
                @if(auth()->user()->isAdmin())
                <x-slot name="actions">
                    <div class="flex gap-2">
                        @if($this->canViewDocument('company_stamp'))
                            <flux:button 
                                variant="ghost" 
                                icon="eye"
                                wire:click="viewDocument('company_stamp')"
                                wire:loading.attr="disabled"
                            >
                                Voir
                            </flux:button>
                        @endif
                        <flux:button 
                            variant="ghost" 
                            icon="document-arrow-down"
                            wire:click="downloadDocument('company_stamp')"
                            wire:loading.attr="disabled"
                        >
                            Télécharger
                        </flux:button>
                    </div>
                </x-slot>
                @endif
            </flux:callout>
        @endif
    </div>    
</div>
