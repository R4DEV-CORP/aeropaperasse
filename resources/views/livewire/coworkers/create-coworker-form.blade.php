<form wire:submit.prevent="submit" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">Nouveau collaborateur/utilisateur</flux:heading>
        <flux:text class="mt-2">Saisissez les informations du collaborateur/utilisateur.</flux:text>
    </div>

    @if($errorMessage)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <flux:icon.x-circle class="size-5 text-red-600 mr-2" />
                <flux:text class="text-red-800">{{ $errorMessage }}</flux:text>
            </div>
        </div>  
    @endif

    @if($successMessage)
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <flux:icon.check-circle class="size-5 text-green-600 mr-2" />
                <flux:text class="text-green-800">{{ $successMessage }}</flux:text>
            </div>
        </div>
    @endif

    @if($user->isAdmin())
    <div class="border border-red-200 p-4 rounded-lg bg-red-50">
            <div class="flex items-center justify-between">
                <flux:heading size="lg" class="mb-4">Sélection du client</flux:heading>
                <flux:badge color="rose">Admin</flux:badge>
            </div>
            <flux:field>
                <flux:label>Client<span class="text-red-500">*</span></flux:label>
                <select wire:model.live="selected_client_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Sélectionnez un client...</option>
                    @foreach($allClients as $clientOption)
                        <option value="{{ $clientOption->id }}">
                            {{ $clientOption->company_name }} - {{ $clientOption->siret_number }}
                        </option>
                    @endforeach
                </select>
                <flux:error name="selected_client_id" />
            </flux:field>
        </div>
    @endif

    @if($selected_client_id)
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg" class="mb-4">Informations du collaborateur</flux:heading>
        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Prénom<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="firstname" icon="user" name="firstname" required />
                <flux:error name="firstname" />
            </flux:field>
            <flux:field>
                <flux:label>Nom<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="lastname" icon="user" name="lastname" required />
                <flux:error name="lastname" />
            </flux:field>
            <flux:field>
                <flux:label>Email<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="email" icon="at-symbol" name="email" required />
                <flux:error name="email" />
            </flux:field>
            <flux:field>
                <flux:label>Téléphone<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="phone" icon="phone" name="phone" required />
                <flux:error name="phone" />
            </flux:field>
            <flux:field variant="inline">
                <flux:checkbox wire:model.live="has_leave" />
                <flux:label>Le collaborateur est en départ</flux:label>
                <flux:error name="has_leave" />
            </flux:field>
        </div>
        
        @if($has_leave)
        <div class="mt-4">
            <flux:field>
                <flux:label>Date de départ</flux:label>
                <flux:input wire:model="departure_date" type="date" name="departure_date" />
                <flux:error name="departure_date" />
            </flux:field>
        </div>
        @endif
    </div>
    
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg" class="mb-4">Voulez-vous créer un compte utilisateur pour le collaborateur ?</flux:heading>
        <flux:field variant="inline">
            <flux:checkbox wire:model.live="create_user" />
            <flux:label>Oui</flux:label>
            <flux:error name="create_user" />
        </flux:field>
        
        @if($create_user)
        <div class="grid grid-cols-2 gap-4 mt-4">
            <flux:field>
                <flux:label>Mot de passe<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="password" icon="lock-closed" name="password" type="password" required />
                <flux:error name="password" />
            </flux:field>
            <flux:field>
                <flux:label>Confirmer le mot de passe<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="password_confirmation" icon="lock-closed" name="password_confirmation" type="password" required />
                <flux:error name="password_confirmation" />
            </flux:field>
            <flux:field variant="inline">
                <flux:checkbox wire:model="can_access_formation" />
                <flux:label>L'utilisateur a accès à l'onglet formations.</flux:label>
                <flux:error name="can_access_formation" />
            </flux:field>
            <flux:radio.group wire:model="role" label="Rôle" variant="segmented" size="sm" class="col-span-2">
                <flux:radio value="client" label="Client" />
                <flux:radio value="sclient" label="Sclient" />
                @if($user->isSAdmin())
                <flux:radio value="admin" label="Admin" />
                <flux:radio value="sadmin" label="Sadmin" />
                @endif
            </flux:radio.group>
            <flux:error name="role" />
        </div>
        @endif
    </div>
    
    <div class="flex justify-end space-x-4">
        <flux:button type="button" wire:click="$dispatch('close-modal', { name: 'new-coworker' })" variant="ghost">
            Annuler
        </flux:button>
        <flux:button type="submit" :disabled="$isSubmitting">
            @if($isSubmitting)
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Création en cours...
            @else
                Créer le collaborateur
            @endif
        </flux:button>
    </div>
    @endif
</form>