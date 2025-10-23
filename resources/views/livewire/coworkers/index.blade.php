<div class="mt-8">
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" />
    @endif
    <div class="flex items-center gap-3 mt-4">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher un collaborateur ou un utilisateur..." />
        <flux:modal.trigger name="new-coworker">
            <flux:button variant="primary" icon="user-plus">Nouveau collaborateur/utilisateur</flux:button>
        </flux:modal.trigger>
    </div>
    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200">
        <flux:heading size="lg" class="px-4">Collaborateurs & Utilisateurs</flux:heading>
        <div class="mt-4 border-t border-gray-800/10">
            <table class="min-w-full divide-y divide-slate-800/10">
                <thead class="bg-gray-50">
                    <tr>
                        @if(auth()->user()->isAdmin())
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">SOCIETE</th>
                        @endif
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">NOM & PRENOM</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ROLE</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">CONTACT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">STATUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @foreach($coworkers as $coworker)
                    <tr wire:loading.remove wire:target="search" wire:key="coworker-{{ $coworker->id }}">
                        @if(auth()->user()->isAdmin())
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $coworker->client->company_name }}</p>
                            <flux:text>{{ $coworker->client->trade_name }}</flux:text>
                        </td>
                        @endif
                        <td class="px-3 py-2 flex items-center gap-2">
                            <p>{{ $coworker->firstname }} {{ $coworker->lastname }}</p>
                            @if($coworker->user_id)
                                @if($coworker->user->can_access_formation)
                                <flux:icon.academic-cap variant="micro" color="blue" />
                                @endif
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            @if($coworker->user_id)
                                @switch($coworker->user->role)
                                    @case('admin')
                                        <flux:badge variant="pill" icon="user" color="red" size="sm">Admin</flux:badge>
                                        @break
                                    @case('sadmin')
                                        <flux:badge variant="pill" icon="user" color="red" size="sm">Sadmin</flux:badge>
                                        @break
                                    @case('sclient')
                                        <flux:badge variant="pill" icon="user" color="teal" size="sm">SClient</flux:badge>
                                        @break
                                    @case('client')
                                        <flux:badge variant="pill" icon="user" color="teal" size="sm">Client</flux:badge>
                                        @break
                                @endswitch
                            @else
                                <flux:badge variant="pill" icon="user-group" color="purple" size="sm">Collaborateur</flux:badge>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $coworker->email }}</p>
                            <flux:text>{{ $coworker->phone }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                        @if($coworker->has_leave)
                            <flux:badge icon="x-circle" color="red" size="sm">A quitté le {{ $coworker->departure_date->format('d/m/Y') }}</flux:badge>
                        @else
                            <flux:badge icon="check-circle" color="green" size="sm">Actif</flux:badge>
                        @endif
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center"> 
                                <flux:button variant="subtle" icon="eye" icon:variant="outline" square="true" tooltip="Voir" class="hover:cursor-pointer"/>
                                <flux:modal.trigger :name="'edit-coworker-'.$coworker->id">
                                    <flux:button variant="subtle" icon="pencil-square" icon:variant="outline" square="true" tooltip="Modifier" class="hover:cursor-pointer !text-blue-500"/>
                                </flux:modal.trigger>
                                <flux:modal :name="'edit-coworker-'.$coworker->id" class="min-w-4xl !max-w-6xl" wire:key="edit-coworker-modal-{{ $coworker->id }}">
                                    <livewire:coworkers.edit-coworker-form :coworkerId="$coworker->id" :key="'edit-form-'.$coworker->id" />
                                </flux:modal>
                                @if($coworker->user_id)
                                    <flux:modal.trigger :name="'reset-password-'.$coworker->id">
                                        <flux:button variant="subtle" icon="key" icon:variant="outline" square="true" tooltip="Réinitialiser le mot de passe" class="hover:cursor-pointer !text-yellow-500"/>
                                    </flux:modal.trigger>
                                    <flux:modal :name="'reset-password-'.$coworker->id" class="min-w-2xl !max-w-4xl" wire:key="reset-password-modal-{{ $coworker->id }}">
                                        <livewire:coworkers.new-password-form :coworkerId="$coworker->id" :key="'reset-password-form-'.$coworker->id" />
                                    </flux:modal>
                                @else
                                    <flux:modal.trigger :name="'make-user-'.$coworker->id">
                                        <flux:button variant="subtle" icon="user-plus" icon:variant="outline" square="true" tooltip="Créer un compte" class="hover:cursor-pointer !text-green-500"/>
                                    </flux:modal.trigger>
                                    <flux:modal :name="'make-user-'.$coworker->id" class="min-w-2xl !max-w-4xl" wire:key="make-user-modal-{{ $coworker->id }}">
                                        <livewire:coworkers.make-coworker-as-user-form :coworkerId="$coworker->id" :key="'make-user-form-'.$coworker->id" />
                                    </flux:modal>
                                @endif
                                @if(!$coworker->has_leave)
                                    <flux:modal.trigger :name="'has-leave-'.$coworker->id">
                                        <flux:button variant="subtle" icon="arrow-right-start-on-rectangle" icon:variant="outline" square="true" tooltip="A quitté l'entreprise" class="hover:cursor-pointer !text-orange-500"/>
                                    </flux:modal.trigger>
                                    <flux:modal :name="'has-leave-'.$coworker->id" class="min-w-2xl !max-w-4xl" wire:key="has-leave-modal-{{ $coworker->id }}">
                                        <livewire:coworkers.coworker-has-leave-form :coworkerId="$coworker->id" :key="'has-leave-form-'.$coworker->id" />
                                    </flux:modal>
                                @endif
                                <flux:button variant="subtle" icon="trash" icon:variant="outline" square="true" tooltip="Supprimer le collaborateur" class="hover:cursor-pointer !text-red-500"/>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>    
            </table>
        </div>
    </div>
    <!-- Modal création collaborateur/utilisateur -->
    <flux:modal :dismissible="false" name="new-coworker" class="min-w-4xl !max-w-6xl">
        <livewire:coworkers.create-coworker-form />
    </flux:modal>
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('close-modal', (event) => {
        const modalName = event.name;
        if (modalName) {
            // Fermer la modal spécifique
            const modal = document.querySelector(`[data-modal="${modalName}"]`);
            if (modal) {
                modal.close();
            }
        }
    });
});
</script>
