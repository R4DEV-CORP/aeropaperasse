<div class="mt-8">
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" />
    @endif
    <div class="grid grid-cols-4 gap-4 mt-4">
        <div wire:click="filterByStatus('pending_rem')" class="cursor-pointer transition-transform hover:scale-101 {{ $selectedStatus === 'pending_rem' ? 'ring-2 ring-yellow-500 rounded-lg' : '' }}">
            <x-badge-info-card title="En attente REM" value="{{ $statistics['pending_rem'] }}" bg-color="yellow-200" />
        </div>
        <div wire:click="filterByStatus('pending_adp')" class="cursor-pointer transition-transform hover:scale-101 {{ $selectedStatus === 'pending_adp' ? 'ring-2 ring-amber-500 rounded-lg' : '' }}">
            <x-badge-info-card title="En attente ADP" value="{{ $statistics['pending_adp'] }}" bg-color="amber-200" />
        </div>
        <div wire:click="filterByStatus('approved_adp')" class="cursor-pointer transition-transform hover:scale-101 {{ $selectedStatus === 'approved_adp' ? 'ring-2 ring-green-500 rounded-lg' : '' }}">
            <x-badge-info-card title="Approuvé ADP" value="{{ $statistics['approved_adp'] }}" bg-color="green-200" />
        </div>
        <div wire:click="filterByStatus('pending_fabrication')" class="cursor-pointer transition-transform hover:scale-101 {{ $selectedStatus === 'pending_fabrication' ? 'ring-2 ring-lime-500 rounded-lg' : '' }}">
            <x-badge-info-card title="En fabrication" value="{{ $statistics['pending_fabrication'] }}" bg-color="lime-200" />
        </div>
        <div wire:click="filterByStatus('rejected_rem')" class="cursor-pointer transition-transform hover:scale-101 {{ $selectedStatus === 'rejected_rem' ? 'ring-2 ring-red-500 rounded-lg' : '' }}">
            <x-badge-info-card title="Dossier incomplet" value="{{ $statistics['rejected_rem'] }}" bg-color="red-200" />
        </div>
        <div wire:click="filterByStatus('rejected_adp')" class="cursor-pointer transition-transform hover:scale-101 {{ $selectedStatus === 'rejected_adp' ? 'ring-2 ring-red-500 rounded-lg' : '' }}">
            <x-badge-info-card title="Rejetté ADP" value="{{ $statistics['rejected_adp'] }}" bg-color="red-200" />
        </div>
        <div wire:click="filterByStatus('ready_for_delivery')" class="cursor-pointer transition-transform hover:scale-101 {{ $selectedStatus === 'ready_for_delivery' ? 'ring-2 ring-blue-500 rounded-lg' : '' }}">
            <x-badge-info-card title="Prêt à être Remis" value="{{ $statistics['ready_for_delivery'] }}" bg-color="blue-200" />
        </div>
        <div wire:click="filterByStatus('delivered')" class="cursor-pointer transition-transform hover:scale-101 {{ $selectedStatus === 'delivered' ? 'ring-2 ring-emerald-500 rounded-lg' : '' }}">
            <x-badge-info-card title="Remis" value="{{ $statistics['delivered'] }}" bg-color="emerald-200" />
        </div>
    </div>
    <div class="flex items-center gap-3 mt-4 flex-wrap">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher une demande..." class="flex-1 min-w-[200px]" />
        <select wire:model.live="selectedAirport" 
                class="min-w-[180px] px-3 py-2 border border-gray-300 rounded-md bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tous les aéroports</option>
            <option value="CDG">CDG</option>
            <option value="ORY">ORY</option>
            <option value="LBG">LBG</option>
        </select>
        @if($selectedAirport || $selectedStatus || $search)
            <flux:button variant="ghost" icon="x-mark" wire:click="resetFilters">Réinitialiser</flux:button>
        @endif
        @if(!auth()->user()->isClient())
            <flux:modal.trigger name="new-badge-request">
                <flux:button variant="primary" icon="plus">Nouvelle demande</flux:button>
            </flux:modal.trigger>
        @endif
    </div>
    @if($selectedAirport || $selectedStatus)
        <div class="mt-2 flex items-center gap-2 flex-wrap">
            <flux:text class="text-sm text-gray-600">Filtres actifs :</flux:text>
            @if($selectedStatus)
                <flux:badge color="blue" size="sm" class="cursor-pointer" wire:click="$set('selectedStatus', null)">
                    @switch($selectedStatus)
                        @case('pending_rem')
                            En attente REM
                            @break
                        @case('pending_adp')
                            En attente ADP
                            @break
                        @case('approved_adp')
                            Approuvé ADP
                            @break
                        @case('pending_fabrication')
                            En fabrication
                            @break
                        @case('rejected_rem')
                            Dossier incomplet
                            @break
                        @case('rejected_adp')
                            Rejetté ADP
                            @break
                        @case('ready_for_delivery')
                            Prêt à être Remis
                            @break
                        @case('delivered')
                            Remis
                            @break
                    @endswitch
                    <span class="ml-1">×</span>
                </flux:badge>
            @endif
            @if($selectedAirport)
                <flux:badge color="cyan" size="sm" class="cursor-pointer" wire:click="$set('selectedAirport', null)">
                    {{ $selectedAirport }}
                    <span class="ml-1">×</span>
                </flux:badge>
            @endif
        </div>
    @endif
    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200">
        <flux:heading size="lg" class="px-4">Demandes récentes</flux:heading>
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
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DEMANDEUR</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">CONTACT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">STATUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">AEROPORT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @if($badgeRequests->count() > 0)
                    @foreach($badgeRequests as $badgeRequest)
                    <tr wire:loading.remove wire:target="search" wire:key="badge-request-{{ $badgeRequest->id }}" class="{{ $badgeRequest->status == 'terminated' ? 'opacity-50' : '' }}">
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $badgeRequest->coworker->firstname }} {{ $badgeRequest->coworker->lastname }}</p>
                            <flux:text>{{ $badgeRequest->activityRequest->client->company_name }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $badgeRequest->coworker->email }}</p>
                            <flux:text>{{ $badgeRequest->coworker->phone }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            @switch($badgeRequest->status)
                                @case('pending_rem')
                                    <flux:badge icon="clock" color="yellow" size="sm">En attente REM</flux:badge>
                                    @break
                                @case('rejected_rem')
                                    <flux:badge icon="x-circle" color="red" size="sm">Dossier incomplet</flux:badge>
                                    @break
                                @case('pending_adp')
                                    <flux:badge icon="clock" color="amber" size="sm">En attente ADP</flux:badge>
                                    @break
                                @case('approved_adp')
                                    <flux:badge icon="check-circle" color="green" size="sm">Approuvé ADP</flux:badge>
                                    @break
                                @case('rejected_adp')
                                    <flux:badge icon="x-circle" color="red" size="sm">Rejeté ADP</flux:badge>
                                    @break
                                @case('pending_fabrication')
                                    <flux:badge icon="clock" color="lime" size="sm">En fabrication</flux:badge>
                                    @break
                                @case('ready_for_delivery')
                                    <flux:badge icon="check-badge" color="blue" size="sm">Prêt à être Remis</flux:badge>
                                    @break
                                @case('delivered')
                                    <flux:badge icon="check-circle" color="emerald" size="sm">Remis</flux:badge>
                                    @break
                                @case('terminated')
                                    <flux:badge icon="check-circle" color="violet" size="sm">Terminé</flux:badge>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-3 py-2">{{ $badgeRequest->created_at->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">
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
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center">   
                                <flux:modal.trigger :name="'view-badge-request-'.$badgeRequest->id">
                                    <flux:button icon="eye" icon:variant="outline" variant="subtle" square="true" tooltip="Voir" color="blue" class="hover:cursor-pointer"/>
                                </flux:modal.trigger>
                                <!-- Modal visualisation demande -->
                                <flux:modal :name="'view-badge-request-'.$badgeRequest->id" class="min-w-4xl !max-w-6xl">
                                    <livewire:badge-requests.view-badge-request :badgeRequest="$badgeRequest" wire:key="badge-request-modal-view-{{ $badgeRequest->id }}"/>
                                </flux:modal>
                                @if(auth()->user()->isAdmin())
                                    @if($badgeRequest->status == 'pending_rem')
                                        <flux:button variant="subtle" icon="check-circle" icon:variant="outline" square="true" tooltip="Approuver (REM)" wire:click="approveRem({{ $badgeRequest->id }})" class="!text-green-500 hover:cursor-pointer"/>
                                        <flux:modal.trigger :name="'reject-badge-request-rem-'.$badgeRequest->id">
                                            <flux:button variant="subtle" icon="x-circle" icon:variant="outline" square="true" tooltip="Rejetter (REM)" class="!text-red-500 hover:cursor-pointer"/>
                                        </flux:modal.trigger>
                                        <flux:modal :name="'reject-badge-request-rem-'.$badgeRequest->id" class="min-w-4xl !max-w-6xl space-y-4">
                                            <flux:heading size="lg">Rejeter la demande (REM)</flux:heading>
                                            <form wire:submit="rejectRem({{ $badgeRequest->id }})">
                                                <flux:field>
                                                    <flux:textarea wire:model="rejectReason" label="Motif du rejet" placeholder="Motif du rejet" />
                                                </flux:field>
                                                <div class="flex items-center justify-end mt-2">
                                                    <flux:button variant="danger" icon="x-circle" icon:variant="outline" type="submit" class="hover:cursor-pointer">Rejeter</flux:button>
                                                </div>
                                            </form>
                                        </flux:modal>
                                    @endif
                                    @if($badgeRequest->status == 'pending_adp')
                                        <flux:button variant="subtle" icon="arrow-left-circle" icon:variant="outline" square="true" tooltip="Retour en attente REM" wire:click="backToPendingRem({{ $badgeRequest->id }})" class="!text-amber-500 hover:cursor-pointer"/>
                                        <flux:button variant="subtle" icon="check-circle" icon:variant="outline" square="true" tooltip="Approuver (ADP)" wire:click="approveAdp({{ $badgeRequest->id }})" class="!text-green-500 hover:cursor-pointer"/>
                                        <flux:modal.trigger :name="'reject-badge-request-adp-'.$badgeRequest->id">
                                            <flux:button variant="subtle" icon="x-circle" icon:variant="outline" square="true" tooltip="Rejetter (ADP)" class="!text-red-500 hover:cursor-pointer"/>
                                        </flux:modal.trigger>
                                        <flux:modal :name="'reject-badge-request-adp-'.$badgeRequest->id" class="min-w-4xl !max-w-6xl space-y-4">
                                            <flux:heading size="lg">Rejeter la demande (ADP)</flux:heading>
                                            <form wire:submit="rejectAdp({{ $badgeRequest->id }})">
                                                <flux:field>
                                                    <flux:textarea wire:model="rejectReason" label="Motif du rejet" placeholder="Motif du rejet" />
                                                </flux:field>
                                                <div class="flex items-center justify-end mt-2">
                                                    <flux:button variant="danger" icon="x-circle" icon:variant="outline" type="submit" class="hover:cursor-pointer">Rejeter</flux:button>
                                                </div>
                                            </form>
                                        </flux:modal>
                                    @endif
                                    @if($badgeRequest->status == 'approved_adp')
                                        <flux:button variant="subtle" icon="arrow-left-circle" icon:variant="outline" square="true" tooltip="Retour en attente ADP" wire:click="backToPendingAdp({{ $badgeRequest->id }})" class="!text-amber-500 hover:cursor-pointer"/>
                                        <flux:button variant="subtle" icon="check-circle" icon:variant="outline" square="true" tooltip="Passer en fabrication" wire:click="fabrication({{ $badgeRequest->id }})" class="!text-green-500 hover:cursor-pointer"/>
                                    @endif
                                    @if($badgeRequest->status == 'pending_fabrication')
                                        <flux:button variant="subtle" icon="arrow-left-circle" icon:variant="outline" square="true" tooltip="Retour en approuvé ADP" wire:click="backToApprovedAdp({{ $badgeRequest->id }})" class="!text-amber-500 hover:cursor-pointer"/>
                                        <flux:button variant="subtle" icon="check-circle" icon:variant="outline" square="true" tooltip="Passer à remettre" wire:click="toDelivery({{ $badgeRequest->id }})" class="!text-green-500 hover:cursor-pointer"/>
                                    @endif
                                    @if($badgeRequest->status == 'ready_for_delivery')
                                        <flux:modal.trigger :name="'deliver-badge-request-'.$badgeRequest->id">
                                            <flux:button variant="subtle" icon="check-circle" icon:variant="outline" square="true" tooltip="Marquer comme remis" class="!text-emerald-500 hover:cursor-pointer"/>
                                        </flux:modal.trigger>
                                        <flux:modal :name="'deliver-badge-request-'.$badgeRequest->id" class="min-w-4xl !max-w-6xl space-y-4">
                                            <flux:heading size="lg">Marquer le badge comme remis</flux:heading>
                                            <form wire:submit="deliver({{ $badgeRequest->id }})">
                                                <flux:field>
                                                    <flux:label>Photo du badge remis *</flux:label>
                                                    <flux:input type="file" wire:model="deliveryPhoto" accept="image/jpeg,image/png,image/jpg" />
                                                    @error('deliveryPhoto')
                                                        <flux:error>{{ $message }}</flux:error>
                                                    @enderror
                                                    @if($deliveryPhoto)
                                                        <flux:text class="mt-1 text-sm text-gray-600">Fichier sélectionné : {{ $deliveryPhoto->getClientOriginalName() }}</flux:text>
                                                    @endif
                                                </flux:field>
                                                <div class="flex items-center justify-end mt-4 gap-2">
                                                    <flux:button variant="ghost" wire:click="closeDeliverModal" type="button">Annuler</flux:button>
                                                    <flux:button variant="primary" icon="check-circle" type="submit" wire:loading.attr="disabled">
                                                        <span wire:loading.remove wire:target="deliver">Confirmer la remise</span>
                                                        <span wire:loading wire:target="deliver">Traitement...</span>
                                                    </flux:button>
                                                </div>
                                            </form>
                                        </flux:modal>
                                    @endif
                                    @if(auth()->user()->isSAdmin())
                                        @if(in_array($badgeRequest->status, ['rejected_rem', 'rejected_adp']))
                                            <flux:button variant="subtle" icon="arrow-path" icon:variant="outline" square="true" tooltip="Rouvrir la demande" wire:click="reopenRequest({{ $badgeRequest->id }})" wire:confirm="Êtes-vous sûr de vouloir rouvrir cette demande ? Elle repassera en statut brouillon." class="!text-blue-500 hover:cursor-pointer"/>
                                        @endif
                                    @endif
                                @endif
                                @if(auth()->user()->isAdmin())
                                    <flux:button variant="subtle" icon="document-arrow-down" icon:variant="outline" square="true" tooltip="Télécharger les documents" wire:click="downloadDocuments({{ $badgeRequest->id }})" class="!text-blue-500 hover:cursor-pointer"/>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="5" class="px-3 py-4 text-center">Aucune demande.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @if($badgeRequests->hasPages())
        <div class="px-4 py-3 border-t border-gray-800/10">
            {{ $badgeRequests->links('pagination.custom') }}
        </div>
        @endif
    </div>

    <!-- Brouillons -->
    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200">
        <flux:heading size="lg" class="px-4">Demandes en Brouillon</flux:heading>
        <flux:text class="px-4">Demandes en cours de rédaction.</flux:text>
        <div class="mt-4 border-t border-gray-800/10">
            <table class="min-w-full divide-y divide-slate-800/10">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DEMANDEUR</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">CONTACT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">STATUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">AEROPORT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @if($draftBadgeRequests->count() > 0)
                    @foreach($draftBadgeRequests as $badgeRequest)
                    <tr>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $badgeRequest->coworker->firstname }} {{ $badgeRequest->coworker->lastname }}</p>
                            <flux:text>{{ $badgeRequest->coworker->email }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $badgeRequest->coworker->email }}</p>
                            <flux:text>{{ $badgeRequest->coworker->phone }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            <flux:badge icon="pencil-square" color="gray" size="sm">Brouillon</flux:badge>    
                        </td>
                        <td class="px-3 py-2">{{ $badgeRequest->created_at->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">
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
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="editDraft({{ $badgeRequest->id }})" icon="pencil-square" icon:variant="outline" variant="subtle" square="true" tooltip="Modifier le brouillon" color="blue" class="hover:cursor-pointer"/>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="7" class="px-3 py-4 text-center">Aucune demande en brouillon.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @if($draftBadgeRequests->hasPages())
        <div class="px-4 py-3 border-t border-gray-800/10">
            {{ $draftBadgeRequests->links('pagination.custom') }}
        </div>
        @endif
    </div>

    <!-- Modal création demande de badge -->
    <flux:modal :dismissible="false" name="new-badge-request" class="min-w-4xl !max-w-6xl">
        <livewire:badge-requests.create-badge-request-form />
    </flux:modal>
</div>
