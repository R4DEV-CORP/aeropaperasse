<div class="mt-8">
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" />
    @endif

    <div class="grid grid-cols-5 gap-4 mt-4">
        <x-badge-info-card title="Total" value="{{ $stats['total'] }}" bg-color="violet-200" />
        <x-badge-info-card title="Actifs" value="{{ $stats['active'] }}" bg-color="green-200" />
        <x-badge-info-card title="Expirés" value="{{ $stats['expired'] }}" bg-color="red-200" />
        <x-badge-info-card title="Restitués" value="{{ $stats['returned'] }}" bg-color="blue-200" />
        <x-badge-info-card title="Non Restitués" value="{{ $stats['not_returned'] }}" bg-color="yellow-200" />
    </div>

    <div class="flex items-center gap-3 mt-4">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher par nom, prénom du collaborateur ou nom de l'entreprise" />
        @if(auth()->user()->isAdmin())
            <flux:dropdown>
                <flux:button variant="primary" icon="plus" icon:trailing="chevron-down">Ajouter un badge</flux:button>
                <flux:menu>
                    <flux:modal.trigger name="add-badge">
                        <flux:menu.item icon="document-text">Lier à une demande de badge</flux:menu.item>
                    </flux:modal.trigger>
                    <flux:modal.trigger name="add-standalone-badge">
                        <flux:menu.item icon="plus-circle">Créer indépendamment</flux:menu.item>
                    </flux:modal.trigger>
                </flux:menu>
            </flux:dropdown>
        @endif
    </div>

    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200">
        <flux:heading size="lg" class="px-4">Liste des badges</flux:heading>
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
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">N° BADGE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DETENTEUR</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">CONTACT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">STATUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE DE CREATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE D'EXPIRATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @if($badges->count() > 0)
                    @foreach($badges as $badge)
                    <tr wire:loading.remove wire:target="search,refreshBadges" wire:key="badge-{{ $badge->id }}">
                        <td class="px-3 py-2">
                            @if($badge->badge_number)
                                <flux:badge color="zinc" size="sm">{{ $badge->badge_number }}</flux:badge>
                            @else
                                <flux:text class="text-gray-400">—</flux:text>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $badge->getEffectiveCoworker()?->firstname }} {{ $badge->getEffectiveCoworker()?->lastname }}</p>
                            <flux:text>{{ $badge->getEffectiveClient()?->company_name }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $badge->getEffectiveCoworker()?->email }}</p>
                            <flux:text>{{ $badge->getEffectiveCoworker()?->phone }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            @switch($badge->status)
                                @case('active')
                                    <flux:badge icon="check-circle" color="green" size="sm">Actif</flux:badge>
                                    @break
                                @case('expired')
                                    <flux:badge icon="x-circle" color="red" size="sm">Expiré</flux:badge>
                                    @break
                                @case('returned')
                                    <flux:badge icon="check-circle" color="blue" size="sm">Restitué</flux:badge>
                                    @break
                                @case('not_returned')
                                    <flux:badge icon="x-circle" color="yellow" size="sm">Non Restitué</flux:badge>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-3 py-2">{{ $badge->created_at->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">{{ $badge->expiry_date->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">
                            <div class="flex items-center">
                                <flux:modal.trigger :name="'view-badge-'.$badge->id">
                                    <flux:button icon="eye" icon:variant="outline" variant="subtle" square="true" tooltip="Voir" color="blue" class="hover:cursor-pointer"/>
                                </flux:modal.trigger>
                                <!-- Modal visualisation badge -->
                                <flux:modal :name="'view-badge-'.$badge->id" class="min-w-4xl !max-w-6xl">
                                    <livewire:badge-management.view-badge :badge="$badge" wire:key="badge-modal-view-{{ $badge->id }}" />
                                </flux:modal>
                                @if(($badge->status == 'active' || $badge->status == 'expired' || $badge->status == 'not_returned') && !auth()->user()->isClient())
                                    <flux:modal.trigger :name="'return-badge-'.$badge->id">
                                        <flux:button variant="subtle" icon="inbox-arrow-down" icon:variant="outline" square="true" tooltip="Marquer comme restitué" class="!text-blue-500 hover:cursor-pointer"/>
                                    </flux:modal.trigger>
                                    <!-- Modal return badge -->
                                        <flux:modal :name="'return-badge-'.$badge->id" class="min-w-4xl !max-w-6xl border" wire:key="badge-modal-return-{{ $badge->id }}">
                                            <livewire:badge-management.return-badge-form :badge="$badge" wire:key="badge-modal-return-{{ $badge->id }}" />
                                        </flux:modal>
                                @endif
                                @if(auth()->user()->isAdmin())
                                    <flux:modal.trigger :name="'edit-badge-number-'.$badge->id">
                                        <flux:button variant="subtle" icon="pencil" icon:variant="outline" square="true" tooltip="Modifier le numéro de badge" class="!text-blue-500 hover:cursor-pointer"/>
                                    </flux:modal.trigger>
                                    <!-- Modal edition badge_number -->
                                    <flux:modal :name="'edit-badge-number-'.$badge->id" class="min-w-4xl !max-w-6xl border">
                                        <livewire:badge-management.edit-badge-number-form :badge="$badge" wire:key="badge-modal-edit-number-{{ $badge->id }}" />
                                    </flux:modal>
                                    <flux:modal.trigger :name="'edit-badge-expiry-date-'.$badge->id">
                                        <flux:button variant="subtle" icon="calendar" icon:variant="outline" square="true" tooltip="Modifier la date d'expiration" class="!text-blue-500 hover:cursor-pointer"/>
                                    </flux:modal.trigger>
                                    <!-- Modal edition expiry_date -->
                                    <flux:modal :name="'edit-badge-expiry-date-'.$badge->id" class="min-w-4xl !max-w-6xl border !bg-red-50">
                                        <livewire:badge-management.edit-badge-expiry-date-form :badge="$badge" wire:key="badge-modal-edit-expiry-date-{{ $badge->id }}" />
                                    </flux:modal>
                                    @if($badge->status == 'expired')
                                        <flux:button variant="subtle" icon="shield-exclamation" icon:variant="outline" square="true" tooltip="Marquer non restitué" wire:click="notReturnedBadge({{ $badge->id }})" class="!text-yellow-500 hover:cursor-pointer"/>
                                    @endif
                                @endif
                            </div
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td class="px-3 py-2" colspan="7">
                            @if(!empty($search))
                                <flux:text class="text-gray-500">Aucun badge trouvé pour "{{ $search }}"</flux:text>
                            @else
                                <flux:text class="text-gray-500">Aucun badge trouvé</flux:text>
                            @endif
                        </td>
                    </tr>
                    @endif
                    
                    <!-- Indicateur de chargement -->
                    <tr wire:loading wire:target="search,refreshBadges">
                        <td class="px-3 py-2" colspan="7">
                            <div class="flex items-center justify-center py-4">
                                <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <flux:text class="text-gray-500">Recherche en cours...</flux:text>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>   
        </div>
        
        <!-- Pagination -->
        @if($badges->hasPages())
        <div class="mt-4 px-4">
            {{ $badges->links() }}
        </div>
        @endif
    </div>

    <!-- Modal création de badge lié à une demande -->
    <flux:modal :dismissible="false" name="add-badge" class="min-w-4xl !max-w-6xl border">
        <livewire:badge-management.create-badge-form />
    </flux:modal>

    <!-- Modal création de badge indépendant -->
    <flux:modal :dismissible="false" name="add-standalone-badge" class="min-w-4xl !max-w-6xl border">
        <livewire:badge-management.create-standalone-badge-form />
    </flux:modal>
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('badge-created', () => {
        // Fermer le modal après création
        document.dispatchEvent(new CustomEvent('close-modal', { detail: { name: 'add-badge' } }));
        
        // Afficher une notification de succès
        setTimeout(() => {
            document.dispatchEvent(new CustomEvent('notify', { 
                detail: { 
                    message: 'Badge créé avec succès !', 
                    type: 'success' 
                } 
            }));
        }, 500);
    });

    Livewire.on('badge-expiry-date-updated', (badgeId) => {
        // Afficher une notification de succès
        setTimeout(() => {
            document.dispatchEvent(new CustomEvent('notify', { 
                detail: { 
                    message: 'Date d\'expiration modifiée avec succès !', 
                    type: 'success' 
                } 
            }));
        }, 500);
    });
});
</script>
