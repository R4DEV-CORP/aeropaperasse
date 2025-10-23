<form wire:submit="submit" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">Réinitialiser le mot de passe</flux:heading>
        <flux:text class="mt-2">Définissez un nouveau mot de passe pour {{ $coworker->firstname }} {{ $coworker->lastname }}.</flux:text>
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
                <flux:input value="{{ $coworker->email }}" disabled />
            </flux:field>
            <flux:field>
                <flux:label>Rôle actuel</flux:label>
                <flux:input value="{{ ucfirst($userAccount->role) }}" disabled />
            </flux:field>
        </div>
    </div>
    
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg" class="mb-4">Nouveau mot de passe</flux:heading>
        <div class="space-y-4">
            <flux:field>
                <flux:label>Nouveau mot de passe<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="password" type="password" icon="key" name="password" required />
                <flux:error name="password" />
            </flux:field>
            <flux:field>
                <flux:label>Confirmation du nouveau mot de passe<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="password_confirmation" type="password" icon="key" name="password_confirmation" required />
                <flux:error name="password_confirmation" />
            </flux:field>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-center">
                    <flux:icon.information-circle class="size-5 text-blue-600 mr-2" />
                    <flux:text class="text-blue-800 text-sm">
                        L'utilisateur devra changer son mot de passe lors de sa prochaine connexion.
                    </flux:text>
                </div>
            </div>
        </div>
    </div>
    
    <div class="flex justify-end space-x-4">
        <flux:button type="button" variant="ghost" wire:click="$dispatch('close-modal', { name: 'reset-password-{{ $coworkerId }}' })">
            Annuler
        </flux:button>
        <flux:button type="submit" :disabled="$isSubmitting">
            @if($isSubmitting)
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Réinitialisation en cours...
            @else
                Réinitialiser le mot de passe
            @endif
        </flux:button>
    </div>
</form>