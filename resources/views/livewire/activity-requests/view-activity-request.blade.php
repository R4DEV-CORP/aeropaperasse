<div class="space-y-4">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">Demande d'activité</flux:heading>
    </div>
    <flux:callout icon="information-circle" variant="secondary">
        <flux:callout.heading>Statut</flux:callout.heading>
        <flux:callout.text>
            @if($activityRequest->status === 'approved')
                <flux:badge icon="check-circle" color="green">Approuvée</flux:badge>
            @elseif($activityRequest->status === 'rejected')
                <flux:badge icon="x-circle" color="red">Rejetée</flux:badge>
            @elseif($activityRequest->status === 'pending')
                <flux:badge icon="clock" color="yellow">En attente</flux:badge>
            @endif
        </flux:callout.text>
    </flux:callout>

    <div class="grid grid-cols-2 gap-2 border border-gray-800/10 p-4 rounded-lg">
        <div>
            <flux:heading size="lg">Informations sur l'entreprise</flux:heading>
            <div class="mt-2">
                <p class="text-gray-800 font-medium mt-2">Raison sociale</p>
                <flux:text>{{ $activityRequest->client->company_name }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Nom commercial</p>
                <flux:text>{{ $activityRequest->client->trade_name }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">SIRET</p>
                <flux:text>{{ $activityRequest->client->siret_number }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Adresse</p>
                <flux:text>{{ $activityRequest->client->address }}</flux:text>
                <flux:text>{{ $activityRequest->client->zip_code }} {{ $activityRequest->client->city }}</flux:text>
            </div>
        </div>
        <div>
            <flux:heading size="lg">Responsable</flux:heading>
            <div class="mt-2">
                <p class="text-gray-800 font-medium mt-2">Nom et prénom</p>
                <flux:text>{{ $activityRequest->manager_firstname }} {{ $activityRequest->manager_lastname }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Fonction</p>
                <flux:text>{{ $activityRequest->manager_role }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Email</p>
                <flux:text>{{ $activityRequest->manager_email }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Téléphone</p>
                <flux:text>{{ $activityRequest->manager_phone }}</flux:text>
            </div>
        </div>
    </div>

    <div class="border border-gray-800/10 p-4 rounded-lg">
        <div>
            <flux:heading size="lg">Informations sur l'activité</flux:heading>
            <div class="mt-2">
                <p class="text-gray-800 font-medium mt-2">Description</p>
                <flux:text>{{ $activityRequest->description }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Nombre de personnes</p>
                <flux:text>{{ $activityRequest->person_count }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Nombre de véhicules</p>
                <flux:text>{{ $activityRequest->vehicule_count }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Denomination des clients</p>
                <flux:text>{{ $activityRequest->customer_names }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Aéroport</p>
                @switch($activityRequest->airport)
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
            </div>
        </div>
    </div>

    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg">Documents</flux:heading>
        
        @if (session()->has('error'))
            <flux:callout icon="exclamation-triangle" variant="danger" class="mt-2">
                <flux:callout.text>{{ session('error') }}</flux:callout.text>
            </flux:callout>
        @endif
        
        @if($activityRequest->customer_certificate_document)
            <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                <flux:callout.heading>Attestation client</flux:callout.heading>
                <x-slot name="actions">
                    <flux:button 
                        variant="ghost" 
                        icon="document-arrow-down"
                        wire:click="downloadDocument('customer_certificate_document')"
                        wire:loading.attr="disabled"
                    >
                        Télécharger
                    </flux:button>
                </x-slot>
            </flux:callout>
        @endif
        
        @if($activityRequest->prefectural_agreement_document)
            <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                <flux:callout.heading>Agrément préfectoral</flux:callout.heading>
                <x-slot name="actions">
                    <flux:button 
                        variant="ghost" 
                        icon="document-arrow-down"
                        wire:click="downloadDocument('prefectural_agreement_document')"
                        wire:loading.attr="disabled"
                    >
                        Télécharger
                    </flux:button>
                </x-slot>
            </flux:callout>
        @endif
        
        @if($activityRequest->iata_contract_document)
            <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                <flux:callout.heading>Contrat IATA</flux:callout.heading>
                <x-slot name="actions">
                    <flux:button 
                        variant="ghost" 
                        icon="document-arrow-down"
                        wire:click="downloadDocument('iata_contract_document')"
                        wire:loading.attr="disabled"
                    >
                        Télécharger
                    </flux:button>
                </x-slot>
            </flux:callout>
        @endif
        
        @if($activityRequest->cta_document)
            <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                <flux:callout.heading>CTA</flux:callout.heading>
                <x-slot name="actions">
                    <flux:button 
                        variant="ghost" 
                        icon="document-arrow-down"
                        wire:click="downloadDocument('cta_document')"
                        wire:loading.attr="disabled"
                    >
                        Télécharger
                    </flux:button>
                </x-slot>
            </flux:callout>
        @endif
        
        @if($activityRequest->customer_certificate_document || 
            $activityRequest->prefectural_agreement_document || 
            $activityRequest->iata_contract_document || 
            $activityRequest->cta_document)
            <flux:button 
                variant="primary" 
                icon="document-arrow-down" 
                class="w-full mt-2"
                wire:click="downloadAllDocuments"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="downloadAllDocuments">Télécharger tous les documents</span>
                <span wire:loading wire:target="downloadAllDocuments">Préparation du téléchargement...</span>
            </flux:button>
        @else
            <flux:callout icon="information-circle" variant="secondary" class="mt-2">
                <flux:callout.text>Aucun document disponible pour cette demande.</flux:callout.text>
            </flux:callout>
        @endif
    </div>
    <flux:separator class="my-4"/>
    <div>
        <flux:heading size="lg">Commentaires</flux:heading>
        
        @if (session()->has('message'))
            <flux:callout icon="check-circle" variant="success" class="mt-2">
                <flux:callout.text>{{ session('message') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="mt-2">
            <flux:textarea 
                wire:model="comment" 
                placeholder="Ajouter un commentaire" 
                rows="3"
            />
            @error('comment')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
            <div class="flex gap-2 mt-2">
                <flux:spacer />
                <flux:button 
                    variant="primary" 
                    icon="paper-airplane"
                    wire:click="sendComment"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="sendComment">Envoyer</span>
                    <span wire:loading wire:target="sendComment">Envoi en cours...</span>
                </flux:button>
            </div>
        </div>

        <div class="mt-4 space-y-3">
            @forelse($comments as $comment)
                <div class="border border-gray-800/10 p-3 rounded-lg">
                    <div class="flex items-center gap-2">
                        <p class="text-gray-800 font-medium">{{ $comment->user->name }}</p>
                        @if($comment->user->isAdmin())
                            <flux:badge size="sm" color="red">Admin</flux:badge>
                        @else
                            <flux:badge size="sm">{{ $comment->user->client->trade_name }}</flux:badge>
                        @endif
                        <span class="text-gray-500 text-sm ml-2">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <flux:text class="mt-1">{{ $comment->content }}</flux:text>
                </div>
            @empty
                <p class="text-gray-500 text-sm italic">Aucun commentaire pour le moment.</p>
            @endforelse
        </div>
    </div>
</div>
