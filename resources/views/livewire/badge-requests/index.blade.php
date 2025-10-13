<div class="mt-8">
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" />
    @endif
    <div class="grid grid-cols-4 gap-4 mt-4">
        <x-badge-info-card title="En attente REM" value="{{ $statistics['pending_rem'] }}" bg-color="yellow-200" />
        <x-badge-info-card title="En attente ADP" value="{{ $statistics['pending_adp'] }}" bg-color="amber-200" />
        <x-badge-info-card title="Approuvé ADP" value="{{ $statistics['approved_adp'] }}" bg-color="green-200" />
        <x-badge-info-card title="En fabrication" value="{{ $statistics['pending_fabrication'] }}" bg-color="lime-200" />
        <x-badge-info-card title="Rejetté REM" value="{{ $statistics['rejected_rem'] }}" bg-color="red-200" />
        <x-badge-info-card title="Rejetté ADP" value="{{ $statistics['rejected_adp'] }}" bg-color="red-200" />
        <x-badge-info-card title="Prêt à être Remis" value="{{ $statistics['ready_for_delivery'] }}" bg-color="blue-200" />
        <x-badge-info-card title="Demandes totales" value="{{ $statistics['total'] }}" bg-color="violet-200" />
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
    <div class="flex items-center gap-3 mt-4">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher une demande..." />
        <flux:modal.trigger name="new-badge-request">
            <flux:button variant="primary" icon="plus">Nouvelle demande</flux:button>
        </flux:modal.trigger>
    </div>
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
                    <tr wire:loading.remove wire:target="search" wire:key="badge-request-{{ $badgeRequest->id }}">
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
                                    <flux:badge icon="x-circle" color="red" size="sm">Rejeté REM</flux:badge>
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
                                        <flux:button variant="subtle" icon="x-circle" icon:variant="outline" square="true" tooltip="Rejetter (REM)" wire:click="rejectRem({{ $badgeRequest->id }})" class="!text-red-500 hover:cursor-pointer"/>
                                    @endif
                                    @if($badgeRequest->status == 'pending_adp')
                                        <flux:button variant="subtle" icon="arrow-left-circle" icon:variant="outline" square="true" tooltip="Retour en attente REM" wire:click="backToPendingRem({{ $badgeRequest->id }})" class="!text-amber-500 hover:cursor-pointer"/>
                                        <flux:button variant="subtle" icon="check-circle" icon:variant="outline" square="true" tooltip="Approuver (ADP)" wire:click="approveAdp({{ $badgeRequest->id }})" class="!text-green-500 hover:cursor-pointer"/>
                                        <flux:button variant="subtle" icon="x-circle" icon:variant="outline" square="true" tooltip="Rejetter (ADP)" wire:click="rejectAdp({{ $badgeRequest->id }})" class="!text-red-500 hover:cursor-pointer"/>
                                    @endif
                                    @if($badgeRequest->status == 'approved_adp')
                                        <flux:button variant="subtle" icon="arrow-left-circle" icon:variant="outline" square="true" tooltip="Retour en attente ADP" wire:click="backToPendingAdp({{ $badgeRequest->id }})" class="!text-amber-500 hover:cursor-pointer"/>
                                        <flux:button variant="subtle" icon="check-circle" icon:variant="outline" square="true" tooltip="Passer en fabrication" wire:click="fabrication({{ $badgeRequest->id }})" class="!text-green-500 hover:cursor-pointer"/>
                                    @endif
                                    @if($badgeRequest->status == 'pending_fabrication')
                                        <flux:button variant="subtle" icon="arrow-left-circle" icon:variant="outline" square="true" tooltip="Retour en approuvé ADP" wire:click="backToApprovedAdp({{ $badgeRequest->id }})" class="!text-amber-500 hover:cursor-pointer"/>
                                        <flux:button variant="subtle" icon="check-circle" icon:variant="outline" square="true" tooltip="Passer à remettre" wire:click="toDelivery({{ $badgeRequest->id }})" class="!text-green-500 hover:cursor-pointer"/>
                                    @endif
                                @endif
                                <flux:button variant="subtle" icon="document-arrow-down" icon:variant="outline" square="true" tooltip="Télécharger les documents" wire:click="downloadDocuments({{ $badgeRequest->id }})" class="!text-blue-500 hover:cursor-pointer"/>
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
