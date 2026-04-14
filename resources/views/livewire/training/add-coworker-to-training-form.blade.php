<form wire:submit.prevent="submit" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">Attribution d'une formation</flux:heading>
        <flux:text class="mt-2">Choisissez le collaborateur et la formation.</flux:text>
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
            <flux:heading size="lg">Collaborateur</flux:heading>
            <flux:field class="mt-4">
                <flux:label>Sélectionnez un collaborateur<span class="text-red-500">*</span></flux:label>
                @if(count($coworkers) > 0)
                    <select wire:model.live="selected_coworker_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sélectionnez un collaborateur...</option>
                        @foreach($coworkers as $coworker)
                            <option value="{{ $coworker->id }}">
                                {{ $coworker->firstname }} {{ $coworker->lastname }} - {{ $coworker->email }} - {{ $coworker->phone }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500">
                        Aucun collaborateur trouvé pour cette entreprise.
                    </div>
                    <flux:description>Aucun collaborateur disponible pour cette entreprise.</flux:description>
                @endif
                <flux:error name="selected_coworker_id" />
            </flux:field>
        </div>
        @if($selected_coworker_id)
            <div class="border border-gray-800/10 p-4 rounded-lg">
                <flux:heading size="lg">Selectionnez la formation<span class="text-red-500">*</span></flux:heading>
                <flux:radio.group wire:model="selected_training_id" label="Formation">
                    <div class="grid grid-cols-3 gap-4">
                        @foreach($trainings as $training)
                            <flux:radio label="{{ $training->title }}" value="{{ $training->id }}" />
                        @endforeach
                    </div>
                </flux:radio.group>
                <flux:field class="mt-4">
                    <flux:label>Date de début<span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="start_date" type="date" name="start_date" />
                    <flux:error name="start_date" />
                </flux:field>
                <flux:radio.group wire:model="validity_years" label="Durée de la formation">
                    <flux:radio value="2" label="2 ans" />
                    <flux:radio value="3" label="3 ans" />
                    <flux:radio value="5" label="5 ans" />
                    <flux:radio value="lifetime" label="À vie" />
                </flux:radio.group>
            </div>
        @endif
    @endif
     <!-- Actions -->
     <div class="flex gap-2 mt-4">
        <flux:button wire:click="closeModal">Annuler</flux:button>
        <flux:spacer />
        <flux:button type="submit" variant="primary" icon="plus">
            Attribuer la formation
        </flux:button>
    </div>
</form>
