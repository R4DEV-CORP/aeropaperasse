<form wire:submit.prevent="createBadgeRequest" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">
            {{ $badgeRequestId ? 'Modifier le brouillon' : 'Nouvelle demande de badge' }}
        </flux:heading>
        <flux:text class="mt-2">
            {{ $badgeRequestId ? 'Modifiez les informations du brouillon et soumettez-le.' : 'Saisissez les informations pour la création d\'une nouvelle demande de badge.' }}
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

    <!-- Sélection du client (pour les admins uniquement) -->
    @if($user->isAdmin() && !$badgeRequestId)
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

    <!-- Information du client pour l'édition d'un brouillon (admin uniquement) -->
    @if($user->isAdmin() && $badgeRequestId && $client)
        <div class="border border-red-200 p-4 rounded-lg bg-red-50">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Client du brouillon</flux:heading>
                <flux:badge color="red">Admin</flux:badge>
            </div>
            <flux:text class="mt-2">
                <strong>{{ $client->company_name }}</strong> - SIRET: {{ $client->siret_number }}
            </flux:text>
        </div>
    @endif

    <!-- Sélection de la demande d'activité -->
    @if($client && $activityRequests->count() > 0)
        <div class="border border-gray-800/10 p-4 rounded-lg">
            <flux:heading size="lg" class="mb-4">Demande d'activité associée</flux:heading>
            @if($badgeRequestId)
                <flux:callout icon="information-circle" color="blue" class="mb-4">
                    <flux:callout.heading>Vous ne pouvez pas changer la demande d'activité lors de l'édition d'un brouillon.</flux:callout.heading>
                </flux:callout>
            @endif
            <flux:field>
                <flux:label>Demande d'activité<span class="text-red-500">*</span></flux:label>
                <select wire:model.live="selected_activity_request_id" 
                        {{ $badgeRequestId ? 'disabled' : '' }}
                        class="w-full px-3 py-2 border border-gray-300 rounded-md {{ $badgeRequestId ? 'bg-gray-100 cursor-not-allowed' : 'bg-white' }} shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Sélectionnez une demande d'activité...</option>
                    @foreach($activityRequests as $activityRequest)
                        <option value="{{ $activityRequest->id }}">
                            {{ $activityRequest->description }} - {{ $activityRequest->created_at->format('d/m/Y') }} - 
                            <span class="font-semibold">{{ ucfirst($activityRequest->status) }}</span>
                        </option>
                    @endforeach
                </select>
                <flux:description>{{ $badgeRequestId ? 'La demande d\'activité ne peut pas être modifiée.' : 'Sélectionnez une demande d\'activité approuvée ou en attente.' }}</flux:description>
                <flux:error name="selected_activity_request_id" />
            </flux:field>
        </div>
    @elseif($client && $activityRequests->count() === 0)
        <flux:callout class="mt-4" variant="warning" icon="exclamation-triangle" heading="Aucune demande d'activité approuvée ou en attente n'est disponible pour ce client." />
    @endif

    @if($selected_activity_request_id)
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <div class="grid grid-cols-2 gap-4 mt-2">
            <div class="mt-2">
                <flux:heading size="lg">Informations sur la demande d'activité</flux:heading>
                <p class="text-gray-800 font-medium mt-2">Raison sociale</p>
                <flux:text>{{ $activityRequest->client->company_name }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Nom commercial</p>
                <flux:text>{{ $activityRequest->client->trade_name }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">SIRET</p>
                <flux:text>{{ $activityRequest->client->siret_number }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Adresse</p>
                <flux:text>{{ $activityRequest->client->address }}</flux:text>
                <flux:text>{{ $activityRequest->client->zip_code }} {{ $activityRequest->client->city }}</flux:text>
            </div>
            <div class="mt-2">
                <flux:heading size="lg">Responsable</flux:heading>
                <p class="text-gray-800 font-medium mt-2">Nom et prénom</p>
                <flux:text>{{ $activityRequest->manager_firstname }} {{ $activityRequest->manager_lastname }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Fonction</p>
                <flux:text>{{ $activityRequest->manager_role }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Email</p>
                <flux:text>{{ $activityRequest->manager_email }}</flux:text>
                <p class="text-gray-800 font-medium mt-2">Téléphone</p>
                <flux:text>{{ $activityRequest->manager_phone }}</flux:text>
            </div>
        </div>
    </div>
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg">Informations sur l'activité</flux:heading>
        <div class="mt-2">
            <p class="text-gray-800 font-medium mt-2">Description</p>
            <flux:text>{{ $activityRequest->description }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Nombre de personnes</p>
            <flux:text>{{ $activityRequest->person_count }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Nombre de véhicules</p>
            <flux:text>{{ $activityRequest->vehicule_count }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Denomination des clients</p>
            <flux:text>{{ $activityRequest->customer_names }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Aéroport</p>
            @switch($activityRequest->airport)
                @case('CDG')
                    <flux:badge color="cyan" size="sm">CDG</flux:badge>
                    @break
                @case('ORY')
                    <flux:badge color="violet" size="sm">ORY</flux:badge>
                    @break
                @case('LBG')
                    <flux:badge color="lime" size="sm">LBG</flux:badge>
                    @break
            @endswitch
        </div>
    </div>
    @endif

    @if($coworkers && $coworkers->count() > 0 )
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg">Informations sur le collaborateur</flux:heading>
        <flux:callout class="mt-4" icon="information-circle" color="blue" inline>
            <flux:callout.heading>Vous pouvez créer un collaborateur en cliquant sur le bouton à coté.</flux:callout.heading>
            <x-slot name="actions">
                <flux:button icon:trailing="arrow-top-right-on-square" href="/coworkers">Créer un collaborateur</flux:button>
            </x-slot>
        </flux:callout>
        <flux:field class="mt-4">
            <flux:label>Selectionnez un collaborateurs<span class="text-red-500">*</span></flux:label>
            <select wire:model.live="selected_coworker_id" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Sélectionnez un collaborateur...</option>
                @foreach($coworkers as $coworker)
                    <option value="{{ $coworker->id }}">
                        {{ $coworker->firstname }} {{ $coworker->lastname }} - {{ $coworker->email }} - {{ $coworker->phone }}
                    </option>
                @endforeach
            </select>
            <flux:description>Sélectionnez un collaborateur.</flux:description>
            <flux:error name="selected_coworker_id" />
        </flux:field>
        <flux:field variant="inline" class="mt-4">
            <flux:checkbox wire:model="application_authorization" />
            <flux:label>Est-ce une demande d'habilitation ?<span class="ml-1 text-sm">(Si oui, cochez la case)</span></flux:label>
            <flux:error name="application_authorization" />
        </flux:field>
    </div>
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg">Documents</flux:heading>

        @if($badgeRequestId && ($hasExistingSelfiePhoto || $hasExistingIdentificationCard || $hasExistingActivityAuthorization || $hasExistingForDocument || $hasExistingFormationCertificate || $hasExistingInvoice))
            <flux:callout class="mt-4" icon="information-circle" color="blue">
                <flux:callout.heading>Des documents existent déjà pour ce brouillon. Vous pouvez les remplacer en téléchargeant de nouveaux fichiers, sinon les documents existants seront conservés.</flux:callout.heading>
            </flux:callout>
        @endif

        <div class="border border-gray-800/10 p-4 rounded-lg mt-2">
            <flux:field>
                <flux:label>
                    Photo d'identité
                    @if(!$hasExistingSelfiePhoto)
                        <span class="text-red-500">*</span>
                    @else
                        <span class="text-green-600 text-sm ml-2">(Document existant ✓)</span>
                    @endif
                </flux:label>
                <flux:input wire:model="selfie_photo" type="file" icon="document-plus" name="selfie_photo" />
                <flux:error name="selfie_photo" />
            </flux:field>
        </div>
        <div class="border border-gray-800/10 p-4 rounded-lg mt-2">
            <flux:field>
                <flux:label>
                    Pièce d'identité
                    @if(!$hasExistingIdentificationCard)
                        <span class="text-red-500">*</span>
                    @else
                        <span class="text-green-600 text-sm ml-2">(Document existant ✓)</span>
                    @endif
                </flux:label>
                <flux:input wire:model="identification_card" type="file" icon="document-plus" name="identification_card" />
                <flux:error name="identification_card" />
            </flux:field>
        </div>
        <div class="border border-gray-800/10 p-4 rounded-lg mt-2">
            <flux:field>
                <flux:label>
                    Autorisation d'activité
                    @if(!$hasExistingActivityAuthorization)
                        <span class="text-red-500">*</span>
                    @else
                        <span class="text-green-600 text-sm ml-2">(Document existant ✓)</span>
                    @endif
                </flux:label>
                <flux:input wire:model="activity_authorization" type="file" icon="document-plus" name="activity_authorization" />
                <flux:error name="activity_authorization" />
            </flux:field>
        </div>
        <div class="border border-gray-800/10 p-4 rounded-lg mt-2">
            <flux:callout icon="information-circle" color="blue" inline>
                <flux:callout.heading>Veuillez cliquer ici pour télécharger le document à remplir et l'insérer dans le champ ci-dessous.</flux:callout.heading>
                <x-slot name="actions">
                    <flux:button icon="document-arrow-down">Télécharger le modèle</flux:button>
                </x-slot>
            </flux:callout>
            <flux:field>
                <flux:label>
                    Document FOR
                    @if(!$hasExistingForDocument)
                        <span class="text-red-500">*</span>
                    @else
                        <span class="text-green-600 text-sm ml-2">(Document existant ✓)</span>
                    @endif
                </flux:label>
                <flux:input wire:model="for_document" type="file" icon="document-plus" name="for_document" />
                <flux:error name="for_document" />
            </flux:field>
        </div>
        <div class="border border-gray-800/10 p-4 rounded-lg mt-2">
            <flux:callout icon="information-circle" color="blue">
                <flux:callout.heading>Vous devez télécharger un certificat de formation ou attester la formation en cochant la case.</flux:callout.heading>
            </flux:callout>
            <flux:field>
                <flux:label>
                    Certificat de formation
                    @if(!$hasExistingFormationCertificate && !$validate_training)
                        <span class="text-red-500">*</span>
                    @elseif($hasExistingFormationCertificate)
                        <span class="text-green-600 text-sm ml-2">(Document existant ✓)</span>
                    @endif
                </flux:label>
                <flux:input wire:model="formation_certificate_document" type="file" icon="document-plus" name="formation_certificate_document" />
                <flux:error name="formation_certificate_document" />
            </flux:field>
            <flux:separator text="ou" class="my-4"/>
            <flux:field variant="inline">
                <flux:checkbox wire:model="validate_training" />
                <flux:label>J'atteste que le bénéficiaire du badge sera formé en amont de son intervention sur site.</flux:label>
                <flux:error name="validate_training" />
            </flux:field>
        </div>
        <div class="border border-gray-800/10 p-4 rounded-lg mt-2">
            <flux:field>
                <flux:label>
                    Facture
                    @if(!$hasExistingInvoice)
                        <span class="text-red-500">*</span>
                    @else
                        <span class="text-green-600 text-sm ml-2">(Document existant ✓)</span>
                    @endif
                </flux:label>
                <flux:input wire:model="invoice_document" type="file" icon="document-plus" name="invoice_document" />
                <flux:error name="invoice_document" />
            </flux:field>
        </div>
    </div>
    @elseif($coworkers && $coworkers->count() === 0 && $selected_activity_request_id)
    <flux:callout class="mt-4" icon="information-warning" color="warning" inline>
        <flux:callout.heading>Aucun collaborateur n'est disponible.</flux:callout.heading>
        <x-slot name="actions">
            <flux:button href="/coworkers">Créer un collaborateur</flux:button>
        </x-slot>
    </flux:callout>
    @endif

    <!-- Actions -->
    <div class="flex gap-2 mt-4">
        <flux:button wire:click="closeModal">Annuler</flux:button>
        <flux:spacer />
        <flux:button wire:click="saveDraft" icon="document-arrow-down">
            {{ $badgeRequestId ? 'Mettre à jour le brouillon' : 'Enregistrer en brouillon' }}
        </flux:button>
        <flux:button type="submit" variant="primary" :icon="$badgeRequestId ? 'check' : 'plus'">
            {{ $badgeRequestId ? 'Soumettre la demande' : 'Créer la demande' }}
        </flux:button>
    </div>
</form>
