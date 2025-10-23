<form wire:submit="submit" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">Marquer comme ayant quitté l'entreprise</flux:heading>
        <flux:text class="mt-2">Indiquez la date de départ de {{ $coworker->firstname }} {{ $coworker->lastname }}.</flux:text>
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
                <flux:label>Téléphone</flux:label>
                <flux:input value="{{ $coworker->phone }}" disabled />
            </flux:field>
        </div>
    </div>
    
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg" class="mb-4">Date de départ</flux:heading>
        <div class="space-y-4">
            <flux:field>
                <flux:label>Date de départ<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="departure_date" type="date" icon="calendar" name="departure_date" required />
                <flux:error name="departure_date" />
                <flux:description>La date de départ ne peut pas être dans le futur.</flux:description>
            </flux:field>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                <div class="flex items-center">
                    <flux:icon.exclamation-triangle class="size-5 text-orange-600 mr-2" />
                    <flux:text class="text-orange-800 text-sm">
                        Cette action marquera définitivement le collaborateur comme ayant quitté l'entreprise.
                    </flux:text>
                </div>
            </div>
        </div>
    </div>
    
    <div class="flex justify-end space-x-4">
        <flux:button type="button" variant="ghost" wire:click="$dispatch('close-modal', { name: 'has-leave-{{ $coworkerId }}' })">
            Annuler
        </flux:button>
        <flux:button type="submit" :disabled="$isSubmitting">
            @if($isSubmitting)
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Mise à jour en cours...
            @else
                Confirmer le départ
            @endif
        </flux:button>
    </div>
</form>