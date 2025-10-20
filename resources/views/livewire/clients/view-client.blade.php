<div>
    @if(auth()->user()->isAdmin())
    <div class="flex items-center gap-2 py-2">
        <flux:icon.arrow-long-left class="size-4"/>
        <flux:link href="/clients" variant="ghost" wire:navigate inline>Retour à la liste des sociétés</flux:link>
    </div>
    @endif
    <flux:heading size="xl">{{ $client->company_name }}</flux:heading>
    <flux:text class="mt-2">Consultez et modifiez les informations de cette société.</flux:text>
    <div class="grid grid-cols-2 gap-2 border border-gray-800/10 p-4 rounded-lg bg-white mt-4">
        <flux:heading size="lg" class="col-span-2">Informations sur la société</flux:heading>
        <div>
            <div>
                <p class="text-gray-800 mt-2">Raison sociale</p>
                <flux:text>{{ $client->company_name }}</flux:text>
            </div>
            <div>
                <p class="text-gray-800 mt-2">Nom commercial</p>
                <flux:text>{{ $client->trade_name }}</flux:text>
            </div>
            <div>
                <p class="text-gray-800 mt-2">SIRET</p>
                <flux:text>{{ $client->siret_number }}</flux:text>
            </div>
        </div>
        <div>
            <div>
                <p class="text-gray-800 mt-2">Adresse</p>
                <flux:text>{{ $client->address }}</flux:text>
            </div>
            <div>
                <p class="text-gray-800 mt-2">Code postal</p>
                <flux:text>{{ $client->zip_code }}</flux:text>
            </div>
            <div>
                <p class="text-gray-800 mt-2">Ville</p>
                <flux:text>{{ $client->city }}</flux:text>
            </div>
        </div>
    </div>

    <!-- Contacts -->
    <div class="grid grid-cols-3 gap-2 border border-gray-800/10 p-4 rounded-lg bg-white mt-4">
        <flux:heading size="lg" class="col-span-3">Contacts</flux:heading>
        @foreach($client->contacts as $contact)
            <div>
                <div>
                    <p class="text-gray-800 mt-2">{{ $contact->getRoleLabelAttribute() }}</p>
                    <flux:text>{{ $contact->firstname }} {{ $contact->lastname }}</flux:text>
                </div>
                <div>
                    <p class="text-gray-800 mt-2">Email</p>
                    <flux:text>{{ $contact->email }}</flux:text>
                </div>
                <div>
                    <p class="text-gray-800 mt-2">Téléphone</p>
                    <flux:text>{{ $contact->phone }}</flux:text>
                </div>
            </div>
        @endforeach
    </div>
</div>

