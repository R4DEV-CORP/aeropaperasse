<form wire:submit="createActivityRequest" class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">Nouvelle demande d'activité</flux:heading>
        <flux:text class="mt-2">Saisissez les informations pour la création d'une nouvelle demande d'activité.</flux:text>
    </div>

     <!-- Messages de succès et d'erreur -->
    @if($successMessage)
         <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <flux:icon.check-circle class="size-5 text-green-600 mr-2" />
                <flux:text class="text-green-800">{{ $successMessage }}</flux:text>
            </div>
        </div>
    @endif

    @if($errorMessage)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <flux:icon.x-circle class="size-5 text-red-600 mr-2" />
                <flux:text class="text-red-800">{{ $errorMessage }}</flux:text>
            </div>
        </div>
    @endif

    <!-- Informations de la société -->
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg">Informations sur la société</flux:heading>
        <flux:callout class="mt-4" icon="information-circle" color="blue" inline>
            <flux:callout.heading>Pour modifier les informations de la société, veuillez vous rendre dans la page société.</flux:callout.heading>
            <x-slot name="actions">
                <flux:button href="/clients">Modifier -></flux:button>
            </x-slot>
        </flux:callout>
        <div class="grid grid-cols-2 gap-4 mt-2">
            <flux:input readonly variant="filled" value="{{ $client->company_name }}" label="Raison sociale"/>
            <flux:input readonly variant="filled" value="{{ $client->trade_name }}" label="Nom commercial"/>
            <flux:input readonly variant="filled" value="{{ $client->siret_number }}" label="Numéro SIRET"/>          
            <flux:input readonly variant="filled" value="{{ $client->address }}" label="Adresse"/>
            <flux:input readonly variant="filled" value="{{ $client->zip_code }}" label="Code postal"/>
            <flux:input readonly variant="filled" value="{{ $client->city }}" label="Ville"/>
        </div>
    </div>

    <!-- Responsable -->
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg">Responsable</flux:heading>
        <div class="grid grid-cols-2 gap-4 mt-2">
            <flux:field>
                <flux:label>Prénom<span class="text-red-500">*</span></flux:label>
                <flux:input name="manager_firstname" required />
                <flux:error name="manager_firstname" />
            </flux:field>
            <flux:field>
                <flux:label>Nom<span class="text-red-500">*</span></flux:label>
                <flux:input name="manager_lastname" required />
                <flux:error name="manager_lastname" />
            </flux:field>
            
            <flux:field>
                <flux:label>Email<span class="text-red-500">*</span></flux:label>
                <flux:input type="email" icon="at-symbol" name="manager_email" required />
                <flux:error name="manager_email" />
            </flux:field>
            
            <flux:field>
                <flux:label>Téléphone<span class="text-red-500">*</span></flux:label>
                <flux:input icon="phone" name="manager_phone" required />
                <flux:error name="manager_phone" />
            </flux:field>

            <flux:field>
                <flux:label>Fonction du responsable<span class="text-red-500">*</span></flux:label>
                <flux:input name="manager_role" required />
                <flux:error name="manager_role" />
            </flux:field>
            
        </div>
    </div>


</form>