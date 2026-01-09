<div>
    @if(auth()->user()->isAdmin())
    <div class="flex items-center gap-2 py-2">
        <flux:icon.arrow-long-left class="size-4"/>
        <flux:link href="/clients" variant="ghost" wire:navigate inline>Retour à la liste des sociétés</flux:link>
    </div>
    @endif
    <flux:heading size="xl">{{ $client->company_name }}</flux:heading>
    <div class="flex items-end justify-between gap-2">
        <flux:text class="mt-2">Consultez et modifiez les informations de cette société.</flux:text>
        <div class="flex items-center gap-2">
            @if(!auth()->user()->isClient())
            <flux:modal.trigger name="edit-client">
                <flux:button icon="pencil-square" tooltip="Modifier la société" class="hover:cursor-pointer">Modifier la société</flux:button>
            </flux:modal.trigger>
            <flux:modal :name="'edit-client'" class="min-w-4xl !max-w-6xl" wire:key="edit-client-modal">
                <livewire:clients.edit-client-form :clientId="$client->id" />
            </flux:modal>
            @endif
            <flux:button icon="arrow-down-tray" tooltip="Télécharger le bilan" class="hover:cursor-pointer" wire:click="downloadOverview">Télécharger le bilan</flux:button>
        </div>
    </div>
    <div class="grid grid-cols-3 gap-2 border border-gray-800/10 p-4 rounded-lg bg-white mt-4">
        <flux:heading size="lg" class="col-span-3">Informations sur la société</flux:heading>
        <div>
            <div>
                <p class="text-gray-800 mt-2">Raison sociale</p>
                <flux:text>{{ $client->company_name }}</flux:text>
            </div>
            <div>
                <p class="text-gray-800 mt-2">Nom commercial</p>
                <flux:text>{{ $client->trade_name }}</flux:text>
            </div>
            <div>
                <p class="text-gray-800 mt-2">SIRET</p>
                <flux:text>{{ $client->siret_number }}</flux:text>
            </div>
        </div>
        <div>
            <div>
                <p class="text-gray-800 mt-2">Adresse</p>
                <flux:text>{{ $client->address }}</flux:text>
            </div>
            <div>
                <p class="text-gray-800 mt-2">Code postal</p>
                <flux:text>{{ $client->zip_code }}</flux:text>
            </div>
            <div>
                <p class="text-gray-800 mt-2">Ville</p>
                <flux:text>{{ $client->city }}</flux:text>
            </div>
        </div>
        <div>
            <div>
                <p class="text-gray-800 mt-2">Sous traitant de</p>
                <flux:text>{{ is_null($client->subcontractor_of) ? 'Aucun' : $client->subcontractor_of }}</flux:text>
            </div>
            <div>
                <p class="text-gray-800 mt-2">Email de notification</p>
                <flux:text>{{ is_null($client->notification_email) ? 'Aucune' : $client->notification_email }}</flux:text>
            </div>
            <div>
                <p class="text-gray-800 mt-2">Compagnie aérienne</p>
                <flux:badge color="{{ $client->is_airline_company ? 'green' : 'red' }}" size="sm" class="mt-2">{{ $client->is_airline_company ? 'Oui' : 'Non' }}</flux:badge>
            </div>
        </div>
    </div>

    <!-- Contacts -->
    <div class="grid grid-cols-3 gap-2 border border-gray-800/10 p-4 rounded-lg bg-white mt-4">
        <flux:heading size="lg" class="col-span-3">Contacts</flux:heading>
        @foreach($client->contacts as $contact)
            <div>
                <div>
                    <p class="text-gray-800 mt-2">{{ $contact->getRoleLabelAttribute() }}</p>
                    <flux:text>{{ $contact->firstname }} {{ $contact->lastname }}</flux:text>
                </div>
                <div>
                    <p class="text-gray-800 mt-2">Email</p>
                    <flux:text>{{ $contact->email }}</flux:text>
                </div>
                <div>
                    <p class="text-gray-800 mt-2">Téléphone</p>
                    <flux:text>{{ $contact->phone }}</flux:text>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Note: Les quotas sont maintenant gérés au niveau de chaque demande d'activité -->

    <!-- Documents -->
    <div class="grid grid-cols-3 gap-2 border border-gray-800/10 p-4 rounded-lg bg-white mt-4">
        <flux:heading size="lg" class="col-span-3">Documents</flux:heading>
        <div>
            <p class="text-gray-800 mt-2">KBIS</p>
            @if($client->kbis_document) 
                <flux:badge icon="check" color="green" size="sm" class="mt-2">Présent</flux:badge>
            @else
                <flux:badge icon="x-circle" color="red" size="sm" class="mt-2">Absent</flux:badge>
            @endif
        </div>
        <div>
            <p class="text-gray-800 mt-2">Référents sûreté</p>
            @if($client->safety_document) 
                <flux:badge icon="check" color="green" size="sm" class="mt-2">Présent</flux:badge>
            @else
                <flux:badge icon="x-circle" color="red" size="sm" class="mt-2">Absent</flux:badge>
            @endif
        </div>
        <div>
            <p class="text-gray-800 mt-2">Correspondant sécurité</p>
            @if($client->security_document) 
                <flux:badge icon="check" color="green" size="sm" class="mt-2">Présent</flux:badge>
            @else
                <flux:badge icon="x-circle" color="red" size="sm" class="mt-2">Absent</flux:badge>
            @endif
        </div>
    </div>
</div>

