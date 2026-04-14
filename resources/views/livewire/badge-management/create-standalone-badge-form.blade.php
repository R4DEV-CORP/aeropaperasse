<form wire:submit.prevent="createBadge" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <div class="flex justify-between">
            <flux:heading size="xl">
                Créer un badge indépendant
            </flux:heading>
            <flux:badge color="red">Admin</flux:badge>
        </div>
        <flux:text class="mt-2">
            Créer un badge directement pour un collaborateur, sans le lier à une demande de badge.
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

    <div class="border border-gray-800/10 p-4 rounded-lg bg-red-50">
        <flux:heading size="lg" class="mb-3">Client</flux:heading>
        <flux:field>
            <flux:label>Client<span class="text-red-500">*</span></flux:label>
            <select wire:model.live="selected_client_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Sélectionnez un client...</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->company_name }} - {{ $client->siret_number }}</option>
                @endforeach
            </select>
            @error('selected_client_id')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>
    </div>

    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg" class="mb-3">Collaborateur</flux:heading>
        <flux:field>
            <flux:label>Collaborateur<span class="text-red-500">*</span></flux:label>
            <select wire:model.live="selected_coworker_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    @if(!$selected_client_id) disabled @endif>
                <option value="">
                    @if(!$selected_client_id)
                        Sélectionnez d'abord un client...
                    @else
                        Sélectionnez un collaborateur...
                    @endif
                </option>
                @foreach($coworkers as $coworker)
                    <option value="{{ $coworker->id }}">{{ $coworker->lastname }} {{ $coworker->firstname }}</option>
                @endforeach
            </select>
            @if($selected_client_id && $coworkers->isEmpty())
                <flux:text class="text-gray-500 text-sm mt-1">
                    Aucun collaborateur trouvé pour ce client.
                </flux:text>
            @endif
            @error('selected_coworker_id')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>
        <flux:field class="mt-4">
            <flux:label>Numéro de badge</flux:label>
            <flux:input wire:model.live="badge_number" placeholder="Ex : 123456" />
        </flux:field>
        <flux:field class="mt-4">
            <flux:label>Date d'expiration<span class="text-red-500">*</span></flux:label>
            <flux:input wire:model.live="expiry_date" type="date" />
            @error('expiry_date')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>
    </div>

    <div class="flex gap-2 mt-4">
        <flux:button wire:click="closeModal">Annuler</flux:button>
        <flux:spacer />
        <flux:button type="submit" icon="plus" variant="primary" wire:loading.attr="disabled" wire:target="createBadge">Créer le badge</flux:button>
    </div>
</form>
