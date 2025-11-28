<form wire:submit.prevent="createBadge" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <div class="flex justify-between">
            <flux:heading size="xl">
                Créer un badge
            </flux:heading>
        </div>
        <flux:text class="mt-2">
            Créer un badge pour l'attribuer à une demande de badge.
        </flux:text>
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

    @if(auth()->user()->isAdmin())
        <div class="border border-gray-800/10 p-4 rounded-lg bg-red-50">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Sélection du client</flux:heading>
                <flux:badge color="red">Admin</flux:badge>
            </div>
            <flux:field>
                <flux:label>Client<span class="text-red-500">*</span></flux:label>
                <select wire:model.live="selected_client_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Sélectionnez un client...</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->company_name }} - {{ $client->siret_number }}</option>
                    @endforeach
                </select>
            </flux:field>
        </div>
    @endif

    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:field>
            <flux:label>Demande de badge<span class="text-red-500">*</span></flux:label>
            <select wire:model.live="selected_badge_request_id" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    @if(!$selected_client_id) disabled @endif>
                <option value="">
                    @if(!$selected_client_id)
                        Sélectionnez d'abord un client...
                    @else
                        Sélectionnez une demande de badge...
                    @endif
                </option>
                @foreach($badgeRequests as $badgeRequest)
                    <option value="{{ $badgeRequest->id }}">
                        {{ $badgeRequest->coworker->firstname }} {{ $badgeRequest->coworker->lastname }} - {{ $badgeRequest->activityRequest->description }}
                    </option>
                @endforeach
            </select>
            @if($selected_client_id && $badgeRequests->isEmpty())
                <flux:text class="text-gray-500 text-sm mt-1">
                    Aucune demande de badge prête pour ce client.
                </flux:text>
            @endif
        </flux:field>
        <flux:field class="mt-4">
            <flux:label>Date d'expiration<span class="text-red-500">*</span></flux:label>
            <flux:input wire:model.live="expiry_date" type="date" />
        </flux:field>
    </div>
            
            
    <!-- Actions -->
    <div class="flex gap-2 mt-4">
        <flux:button wire:click="closeModal">Annuler</flux:button>
            <flux:spacer />
        <flux:button type="submit" icon="plus" variant="primary" wire:loading.attr="disabled" wire:target="createBadge">Créer le badge</flux:button>
    </div>
</form>
