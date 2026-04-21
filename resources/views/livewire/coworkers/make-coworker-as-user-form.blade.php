<form wire:submit="submit" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">Créer un compte utilisateur</flux:heading>
        <flux:text class="mt-2">Créez un compte utilisateur pour {{ $coworker->firstname }} {{ $coworker->lastname }}.</flux:text>
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

    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg" class="mb-4">Informations du collaborateur</flux:heading>
        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Prénom</flux:label>
                <flux:input value="{{ $coworker->firstname }}" disabled />
            </flux:field>
            <flux:field>
                <flux:label>Nom</flux:label>
                <flux:input value="{{ $coworker->lastname }}" disabled />
            </flux:field>
            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input value="{{ $coworker->email ?? '—' }}" disabled />
            </flux:field>
            <flux:field>
                <flux:label>Téléphone</flux:label>
                <flux:input value="{{ $coworker->phone ?? '—' }}" disabled />
            </flux:field>
        </div>
    </div>
    
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg" class="mb-4">Paramètres du compte utilisateur</flux:heading>
        <div class="space-y-4">
            <flux:field>
                <flux:label>Email du compte<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="email" type="email" icon="at-symbol" name="email" required />
                <flux:error name="email" />
            </flux:field>
            <flux:field>
                <flux:label>Mot de passe<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="password" type="password" icon="key" name="password" required />
                <flux:error name="password" />
            </flux:field>
            <flux:field>
                <flux:label>Confirmation du mot de passe<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="password_confirmation" type="password" icon="key" name="password_confirmation" required />
                <flux:error name="password_confirmation" />
            </flux:field>
            <flux:field variant="inline">
                <flux:checkbox wire:model="can_access_formation" />
                <flux:label>L'utilisateur a accès aux formations</flux:label>
                <flux:error name="can_access_formation" />
            </flux:field>
            <flux:radio.group wire:model="role" label="Rôle" variant="segmented" size="sm">
                <flux:radio value="client" label="Client" />
                <flux:radio value="sclient" label="Sclient" />
                @if($user->isAdmin())
                <flux:radio value="admin" label="Admin" />
                <flux:radio value="sadmin" label="Sadmin" />
                @endif
            </flux:radio.group>
            <flux:error name="role" />
        </div>
    </div>
    
    <div class="flex justify-end space-x-4">
        <flux:button type="button" variant="ghost" wire:click="$dispatch('close-modal', { name: 'make-user-{{ $coworkerId }}' })">
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
                Créer le compte utilisateur
            @endif
        </flux:button>
    </div>
</form>