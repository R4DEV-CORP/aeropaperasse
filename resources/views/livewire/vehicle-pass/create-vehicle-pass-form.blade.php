<form wire:submit.prevent="createVehiclePass" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">Nouvelle demande de laissez-passer</flux:heading>
        <flux:text class="mt-2">Saisissez les informations pour la création d'une nouvelle demande de laissez-passer.</flux:text>
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

    <!-- Sélection du client (pour les admins uniquement) -->
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
            </flux:field>
        </div>
    @endif

    @if($client)
        <!-- Sélection de la demande d'activité -->
        <div class="border border-gray-800/10 p-4 rounded-lg">
            <flux:heading size="lg" class="mb-4">Demande d'activité</flux:heading>
            <flux:field>
                <flux:label>Demande d'activité<span class="text-red-500">*</span></flux:label>
                <select wire:model.live="selected_activity_request_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                    <option value="">Sélectionnez une demande d'activité...</option>
                    @foreach($activityRequests as $activityRequestOption)
                        <option value="{{ $activityRequestOption->id }}">
                            {{ $activityRequestOption->description }} 
                            ({{ $activityRequestOption->airport }} - 
                            {{ $activityRequestOption->person_count }} personnes, 
                            {{ $activityRequestOption->vehicule_count }} véhicules)
                        </option>
                    @endforeach
                </select>
                <flux:error name="selected_activity_request_id" />
            </flux:field>
            @if($activityRequest)
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <flux:text class="font-medium">Quota disponible :</flux:text>
                    <flux:text>
                        {{ $activityRequest->getActiveVehiclePassesCount() }}/{{ $activityRequest->vehicule_count }} laissez-passer véhicules utilisés
                        ({{ $activityRequest->getRemainingVehiclePassQuota() }} place(s) restante(s))
                    </flux:text>
                </div>
            @endif
        </div>
    @endif

    @if($client && $selected_activity_request_id)
        <!-- informations sur le véhicule -->
        <div class="border border-gray-800/10 p-4 rounded-lg">
            <flux:heading size="lg" class="mb-4">Informations sur le véhicule</flux:heading>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Immatriculation<span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="plate_number" icon="hashtag" name="plate_number" required />
                </flux:field>
                <flux:field>
                    <flux:label>Marque<span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="car_brand" name="car_brand" required />
                </flux:field>
            </div>
            <flux:radio.group wire:model="airport" label="Aéroport">
                <flux:radio value="CDG" label="CDG" />
                <flux:radio value="ORY" label="ORY" />
                <flux:radio value="LBG" label="LBG" />
            </flux:radio.group>
        </div>

        <!-- Documents -->
        <div class="border border-gray-800/10 p-4 rounded-lg">
            <flux:heading size="lg" class="mb-4">Documents</flux:heading>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Carte grise<span class="text-red-500">*</span></flux:label>
                    <flux:input type="file" wire:model="certificate_of_registration" icon="document-arrow-up" name="certificate_of_registration" required />
                </flux:field>
                <flux:field>
                    <flux:label>Tampon de l'entreprise<span class="text-red-500">*</span></flux:label>
                    <flux:input type="file" wire:model="company_stamp" icon="document-arrow-up" name="company_stamp" required />
                </flux:field>
            </div>
        </div>
    @endif

    <!-- Action -->
    <div class="flex gap-2 mt-4">
        <flux:button wire:click="closeModal">Annuler</flux:button>
        <flux:spacer />
        <flux:button type="submit" variant="primary" icon="plus">Créer la demande</flux:button>
    </div>
</form>
