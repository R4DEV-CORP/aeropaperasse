<div class="mt-8">
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" />
    @endif
    <div class="grid grid-cols-4 gap-4 mt-4">
        <x-badge-info-card title="Demandes totales" value="{{ $statistics['total'] }}" bg-color="blue-200" />
        <x-badge-info-card title="En attente" value="{{ $statistics['pending'] }}" bg-color="yellow-200" />
        <x-badge-info-card title="Approuvées" value="{{ $statistics['approved'] }}" bg-color="green-200" />
        <x-badge-info-card title="Rejetées" value="{{ $statistics['rejected'] }}" bg-color="red-200" />
    </div>
    <div class="flex items-center gap-3 mt-4">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher une demande..." />
        <flux:modal.trigger name="new-activity-request">
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
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">SOCIETE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">RESPONSABLE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">STATUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DESCRIPTION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE DE CREATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">AEROPORT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @if($activityRequests->count() > 0)
                    @foreach($activityRequests as $activityRequest)
                    <tr wire:loading.remove wire:target="search" wire:key="activity-request-{{ $activityRequest->id }}">
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $activityRequest->client->company_name }}</p>
                            <flux:text>{{ $activityRequest->client->trade_name }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $activityRequest->manager_firstname }} {{ $activityRequest->manager_lastname }}</p>
                            <flux:text>{{ $activityRequest->manager_email }}</flux:text>
                            <flux:text>{{ $activityRequest->manager_phone }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            @switch($activityRequest->status)
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
                        <td class="px-3 py-2">
                            <flux:text>{{ $activityRequest->description }}</flux:text>
                        </td>
                        <td class="px-3 py-2">{{ $activityRequest->created_at->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">
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
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center">   
                                <flux:modal.trigger :name="'view-activity-request-'.$activityRequest->id">
                                    <flux:button icon="eye" icon:variant="outline" variant="subtle" square="true" tooltip="Voir" color="blue" class="hover:cursor-pointer"/>
                                </flux:modal.trigger>
                                <!-- Modal visualisation demande -->
                                <flux:modal :name="'view-activity-request-'.$activityRequest->id" class="min-w-4xl !max-w-6xl">
                                    <livewire:activity-requests.view-activity-request :activityRequest="$activityRequest" wire:key="activity-request-modal-view-{{ $activityRequest->id }}"/>
                                </flux:modal>
                                @if($activityRequest->status == 'pending' && auth()->user()->isAdmin())
                                    <flux:button variant="subtle" icon="check-circle" icon:variant="outline" square="true" tooltip="Approuver" wire:click="approve({{ $activityRequest->id }})" class="!text-green-500 hover:cursor-pointer"/>
                                    <flux:button variant="subtle" icon="x-circle" icon:variant="outline" square="true" tooltip="Rejeter" wire:click="reject({{ $activityRequest->id }})" class="!text-red-500 hover:cursor-pointer"/>
                                @endif
                                <flux:button variant="subtle" icon="document-arrow-down" icon:variant="outline" square="true" tooltip="Télécharger les documents" wire:click="downloadDocuments({{ $activityRequest->id }})" class="!text-blue-500 hover:cursor-pointer"/>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="7" class="px-3 py-4 text-center">Aucune demande.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @if($activityRequests->hasPages())
        <div class="px-4 py-3 border-t border-gray-800/10">
            {{ $activityRequests->links('pagination.custom') }}
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
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">SOCIETE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">RESPONSABLE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">STATUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DESCRIPTION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE DE CREATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">AEROPORT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @if($draftActivityRequests->count() > 0)
                    @foreach($draftActivityRequests as $activityRequest)
                    <tr>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $activityRequest->client->company_name }}</p>
                            <flux:text>{{ $activityRequest->client->trade_name }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $activityRequest->manager_firstname }} {{ $activityRequest->manager_lastname }}</p>
                            <flux:text>{{ $activityRequest->manager_email }}</flux:text>
                            <flux:text>{{ $activityRequest->manager_phone }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            <flux:badge icon="pencil-square" color="gray" size="sm">Brouillon</flux:badge>    
                        </td>
                        <td class="px-3 py-2">
                            <flux:text>{{ $activityRequest->description }}</flux:text>
                        </td>
                        <td class="px-3 py-2">{{ $activityRequest->created_at->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">
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
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="editDraft({{ $activityRequest->id }})" icon="pencil-square" icon:variant="outline" variant="subtle" square="true" tooltip="Modifier le brouillon" color="blue" class="hover:cursor-pointer"/>
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
        @if($draftActivityRequests->hasPages())
        <div class="px-4 py-3 border-t border-gray-800/10">
            {{ $draftActivityRequests->links('pagination.custom') }}
        </div>
        @endif
    </div>

    <!-- Modal création société -->
    <flux:modal name="new-activity-request" :dismissible="false" class="min-w-4xl !max-w-6xl">
        <livewire:activity-requests.create-activity-request-form />
    </flux:modal>
</div>
