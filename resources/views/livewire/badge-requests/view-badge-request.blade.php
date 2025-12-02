<div class="space-y-4">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">Demande de badge</flux:heading>
    </div>
    <flux:callout icon="information-circle" variant="secondary">
        <flux:callout.heading>Statut</flux:callout.heading>
        <flux:callout.text>
            @switch($badgeRequest->status)
                @case('pending_rem')    
                    <flux:badge icon="clock" color="yellow">En attente REM</flux:badge>
                    @break
                @case('rejected_rem')
                    <div class="flex items-center gap-2">
                        <flux:badge icon="x-circle" color="red">Rejetée (REM)</flux:badge>
                        <flux:text>Raison du rejet : {{ $badgeRequest->reject_reason }}</flux:text>
                    </div>
                    @break
                @case('pending_adp')
                    <flux:badge icon="clock" color="amber">En attente ADP</flux:badge>
                    @break
                @case('approved_adp')
                    <flux:badge icon="check-circle" color="green">Approuvée ADP</flux:badge>
                    @break
                @case('rejected_adp')
                    <div class="flex items-center gap-2">
                        <flux:badge icon="x-circle" color="red">Rejetée (ADP)</flux:badge>
                        <flux:text>Raison du rejet : {{ $badgeRequest->reject_reason }}</flux:text>
                    </div>
                    @break
                @case('pending_fabrication')
                    <flux:badge icon="clock" color="lime">En fabrication</flux:badge>
                    @break
                @case('ready_for_delivery')
                    <flux:badge icon="check-badge" color="blue">Prêt à être Remis</flux:badge>
                    @break
            @endswitch
        </flux:callout.text>
    </flux:callout>
    <div class="grid grid-cols-2 gap-2 border border-gray-800/10 p-4 rounded-lg">
        <div>
            <flux:heading size="lg">Informations sur le bénéficiaire</flux:heading>
            <p class="text-gray-800 font-medium mt-2">Nom</p>
            <flux:text>{{ $badgeRequest->coworker->lastname }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Prénom</p>
            <flux:text>{{ $badgeRequest->coworker->firstname }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Email</p>
            <flux:text>{{ $badgeRequest->coworker->email }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Téléphone</p>
            <flux:text>{{ $badgeRequest->coworker->phone }}</flux:text>
        </div>
        <div>
            <flux:heading size="lg">Informations sur la demande</flux:heading>
            <p class="text-gray-800 font-medium mt-2">Date de création</p>
            <flux:text>{{ $badgeRequest->created_at->format('d/m/Y') }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Dernière mise à jour</p>
            <flux:text>{{ $badgeRequest->updated_at->format('d/m/Y H:i') }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Aéroport</p>
                @switch($badgeRequest->activityRequest->airport)
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
            <p class="text-gray-800 font-medium mt-2">Demande d'habilitation</p>
            <flux:text>{{ $badgeRequest->activity_authorization ? 'Oui' : 'Non' }}</flux:text>
        </div>
        <div class="col-span-2">
            <flux:callout icon='information-circle' color="blue" inline class="mt-2">
                <flux:callout.heading>Voir la demande d'activité liée à cette demande de badge.</flux:callout.heading>
                <x-slot name="actions">
                    <flux:button 
                        href="/activity-requests"
                        wire:navigate
                        icon:trailing="arrow-top-right-on-square" 
                    >
                        Voir la demande d'activité
                    </flux:button>
                </x-slot>
            </flux:callout>
        </div>
    </div>
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg">Documents</flux:heading>
        
        @if (session()->has('error'))
            <flux:callout icon="exclamation-triangle" variant="danger" class="mt-2">
                <flux:callout.text>{{ session('error') }}</flux:callout.text>
            </flux:callout>
        @endif
        
        @if($badgeRequest->selfie_photo)
            <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                <flux:callout.heading>Photo de'identité</flux:callout.heading>
                @if(auth()->user()->isAdmin())
                <x-slot name="actions">
                    <flux:button 
                        variant="ghost" 
                        icon="document-arrow-down"
                        wire:click="downloadDocument('selfie_photo')"
                        wire:loading.attr="disabled"
                    >
                        Télécharger
                    </flux:button>
                </x-slot>
                @endif
            </flux:callout>
        @endif
        
        @if($badgeRequest->identification_card)
            <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                <flux:callout.heading>Carte d'identité</flux:callout.heading>
                @if(auth()->user()->isAdmin())
                <x-slot name="actions">
                    <flux:button 
                        variant="ghost" 
                        icon="document-arrow-down"
                        wire:click="downloadDocument('identification_card')"
                        wire:loading.attr="disabled"
                    >
                        Télécharger
                    </flux:button>
                </x-slot>
                @endif
            </flux:callout>
        @endif
        
        @if($badgeRequest->activity_authorization)
            <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                <flux:callout.heading>Autorisation d'activité</flux:callout.heading>
                @if(auth()->user()->isAdmin())
                <x-slot name="actions">
                    <flux:button 
                        variant="ghost" 
                        icon="document-arrow-down"
                        wire:click="downloadDocument('activity_authorization')"
                        wire:loading.attr="disabled"
                    >
                        Télécharger
                    </flux:button>
                </x-slot>
                @endif
            </flux:callout>
        @endif
        
        @if($badgeRequest->for_document)
            <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                <flux:callout.heading>Document FOR</flux:callout.heading>
                @if(auth()->user()->isAdmin())
                <x-slot name="actions">
                    <flux:button 
                        variant="ghost" 
                        icon="document-arrow-down"
                        wire:click="downloadDocument('for_document')"
                        wire:loading.attr="disabled"
                    >
                        Télécharger
                    </flux:button>
                </x-slot>
                @endif
            </flux:callout>
        @endif

        @if($badgeRequest->formation_certificate_document)
            <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                <flux:callout.heading>Certificat de formation</flux:callout.heading>
                @if(auth()->user()->isAdmin())
                <x-slot name="actions">
                    <flux:button 
                        variant="ghost" 
                        icon="document-arrow-down"
                        wire:click="downloadDocument('formation_certificate_document')"
                        wire:loading.attr="disabled"
                    >
                        Télécharger
                    </flux:button>
                </x-slot>
                @endif
            </flux:callout>
        @else
            @if($badgeRequest->validate_training)
                <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                    <flux:callout.heading>Certificat de formation</flux:callout.heading>
                    <flux:callout.text>Le demandeur du badge a attesté que le bénéficiaire serait formé en amont.</flux:callout.text>
                </flux:callout>
            @endif
        @endif

        @if($badgeRequest->invoice_document)
            <flux:callout icon="document-text" variant="secondary" inline class="mt-2">
                <flux:callout.heading>Facture</flux:callout.heading>
                @if(auth()->user()->isAdmin())
                <x-slot name="actions">
                    <flux:button 
                        variant="ghost" 
                        icon="document-arrow-down"
                        wire:click="downloadDocument('invoice_document')"
                        wire:loading.attr="disabled"
                    >
                        Télécharger
                    </flux:button>
                </x-slot>
                @endif
            </flux:callout>
        @endif
        
        @if(auth()->user()->isAdmin())
            @if($badgeRequest->selfie_photo ||  
                $badgeRequest->identification_card || 
                $badgeRequest->activity_authorization || 
                $badgeRequest->for_document || 
                $badgeRequest->formation_certificate_document || 
                $badgeRequest->invoice_document)
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
