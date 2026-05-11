<form wire:submit.prevent="createActivityRequest" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">
            {{ $activityRequestId ? 'Modifier le brouillon' : 'Nouvelle demande d\'activité' }}
        </flux:heading>
        <flux:text class="mt-2">
            {{ $activityRequestId ? 'Modifiez les informations du brouillon et soumettez-le.' : 'Saisissez les informations pour la création d\'une nouvelle demande d\'activité.' }}
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
    @if($user->isAdmin() && !$activityRequestId)
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
    @if($user->isAdmin() && $activityRequestId && $client)
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

    <!-- Informations de la précédente demande -->
    @if($previousActivityRequests->count() > 0)
        <div class="border border-gray-800/10 p-4 rounded-lg">
            <flux:heading size="lg" class="mb-4">Renouvellement de demande</flux:heading>
            <flux:field variant="inline">
                <flux:checkbox wire:model.live="renewal" />
                <flux:label>Ma demande est un renouvellement</flux:label>
                <flux:error name="renewal" />
            </flux:field>
            @if($renewal)
            <flux:callout class="mt-4" icon="information-circle" color="blue">
                <flux:callout.heading>Toutes les informations de la demande sélectionnée seront automatiquement copiées vers la nouvelle demande, y compris les documents.</flux:callout.heading>
            </flux:callout>
            <flux:field class="mt-4">
                <flux:label>Demandes précédentes</flux:label>
                <select wire:model.live="selectedPreviousActivityRequest" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Sélectionnez une activité...</option>
                    @foreach($previousActivityRequests as $activityRequest)
                        <option value="{{ $activityRequest->id }}">
                            {{ $activityRequest->description }} - {{ $activityRequest->created_at->format('d/m/Y') }} - {{ $activityRequest->status }}
                        </option>
                    @endforeach
                </select>
                <flux:error name="last_activity_request_id" />
            </flux:field>
            @endif
        </div>
    @endif

    <!-- Informations de la société -->
    @if(!$renewal && $client)
    <flux:separator text="ou" />
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg">Informations sur la société</flux:heading>
        @if(!$user->isAdmin())
        <flux:callout class="mt-4" icon="information-circle" color="blue" inline>
            <flux:callout.heading>Pour modifier les informations de la société, veuillez vous rendre dans la page société.</flux:callout.heading>
            <x-slot name="actions">
                <flux:button href="/companies">Modifier -></flux:button>
            </x-slot>
        </flux:callout>
        @endif
        <div class="grid grid-cols-2 gap-4 mt-2">
            <flux:input readonly variant="filled" value="{{ $client->company_name }}" label="Raison sociale"/>
            <flux:input readonly variant="filled" value="{{ $client->trade_name }}" label="Nom commercial"/>
            <flux:input readonly variant="filled" value="{{ $client->siret_number }}" label="Numéro SIRET"/>          
            <flux:input readonly variant="filled" value="{{ $client->address }}" label="Adresse"/>
            <flux:input readonly variant="filled" value="{{ $client->zip_code }}" label="Code postal"/>
            <flux:input readonly variant="filled" value="{{ $client->city }}" label="Ville"/>
        </div>
    </div>
    @endif

    <!-- Responsable -->
    @if(!$renewal && $client)
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg">Responsable</flux:heading>
        <div class="grid grid-cols-2 gap-4 mt-2">
            <flux:field>
                <flux:label>Prénom<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="manager_firstname" name="manager_firstname" required />
                <flux:error name="manager_firstname" />
            </flux:field>
            <flux:field>
                <flux:label>Nom<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="manager_lastname" name="manager_lastname" required />
                <flux:error name="manager_lastname" />
            </flux:field>
            
            <flux:field>
                <flux:label>Email<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="manager_email" type="email" icon="at-symbol" name="manager_email" required />
                <flux:error name="manager_email" />
            </flux:field>
            
            <flux:field>
                <flux:label>Téléphone<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="manager_phone" icon="phone" name="manager_phone" required />
                <flux:error name="manager_phone" />
            </flux:field>

            <flux:field>
                <flux:label>Fonction du responsable<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="manager_role" name="manager_role" required />
                <flux:error name="manager_role" />
            </flux:field>
            
        </div>
    </div>

    <!-- Informations sur l'activité -->
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg" class="mb-4">Informations sur l'activité</flux:heading>
        
        <!-- Sélection de l'aéroport -->
        <div class="grid grid-cols-2">
            <flux:radio.group wire:model="airport" label="Aéroport">
                <flux:radio value="CDG" label="Roissy Charles de Gaulle" />
                <flux:radio value="ORY" label="Paris Orly" />
                <flux:radio value="LBG" label="Le Bourget" />
            </flux:radio.group>
            <flux:error name="airport" />
        </div>
        
        <div class="grid grid-cols-2 gap-4 mt-4">
            <flux:field>
                <flux:label>Description de l'activité<span class="text-red-500">*</span></flux:label>
                <flux:textarea wire:model="description" name="description" required />
                <flux:error name="description" />
            </flux:field>
            <flux:field>
                <flux:label>Dénomination des clients<span class="text-red-500">*</span></flux:label>
                <flux:textarea wire:model="customer_names" name="customer_names" required />
                <flux:error name="customer_names" />
            </flux:field>
            <flux:field>
                <flux:label>Nombre de personnes<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="person_count" type="number" min="1" max="1000" icon:trailing="identification" name="person_count" required />
                <flux:error name="person_count" />
            </flux:field>
            <flux:field>
                <flux:label>Nombre de véhicules<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="vehicule_count" type="number" min="0" max="1000" icon:trailing="truck" name="vehicule_count" required />
                <flux:error name="vehicule_count" />
            </flux:field>
        </div>
    </div>

    <!-- Documents -->
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg">Documents</flux:heading>

            @if($activityRequestId && ($hasExistingAaoRequest || $hasExistingKbis || $hasExistingPrincipals || $hasExistingSafetyReferent || $hasExistingSecurityReferent || $hasExistingCta))
                <flux:callout class="mt-4" icon="information-circle" color="blue">
                    <flux:callout.heading>Des documents existent déjà pour ce brouillon. Vous pouvez les remplacer en téléchargeant de nouveaux fichiers, sinon les documents existants seront conservés.</flux:callout.heading>
                </flux:callout>
            @endif
            <flux:field class="mt-2">
                <flux:label>
                    Demande AAO
                    @if(!$hasExistingAaoRequest)
                        <span class="text-red-500">*</span>
                    @endif
                </flux:label>
                <flux:input wire:model="aao_request_document" type="file" icon="document-plus" name="aao_request_document" />
                @if($aao_request_document_name)
                    <div class="mt-2 flex items-center gap-2 p-2 bg-green-50 border border-green-200 rounded-md">
                        <flux:icon.check-circle class="size-5 text-green-600" />
                        <flux:text class="text-sm text-green-800">
                            Nouveau fichier sélectionné : <strong>{{ $aao_request_document_name }}</strong>
                        </flux:text>
                    </div>
                @elseif($hasExistingAaoRequest && $existing_aao_request_document_name)
                    <div class="mt-2 flex items-center gap-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                        <flux:icon.check-circle class="size-5 text-blue-600" />
                        <flux:text class="text-sm text-blue-800">
                            Document existant : <strong>{{ $existing_aao_request_document_name }}</strong>
                        </flux:text>
                    </div>
                @endif
                <flux:error name="aao_request_document" />
            </flux:field>
            <flux:field class="mt-2">
                <flux:label>
                    Extrait KBIS
                    @if(!$hasExistingKbis)
                        <span class="text-red-500">*</span>
                    @endif
                </flux:label>
                <flux:input wire:model="kbis_document" type="file" icon="document-plus" name="kbis_document" />
                @if($kbis_document_name)
                    <div class="mt-2 flex items-center gap-2 p-2 bg-green-50 border border-green-200 rounded-md">
                        <flux:icon.check-circle class="size-5 text-green-600" />
                        <flux:text class="text-sm text-green-800">
                            Nouveau fichier sélectionné : <strong>{{ $kbis_document_name }}</strong>
                        </flux:text>
                    </div>
                @elseif($hasExistingKbis && $existing_kbis_document_name)
                    <div class="mt-2 flex items-center gap-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                        <flux:icon.check-circle class="size-5 text-blue-600" />
                        <flux:text class="text-sm text-blue-800">
                            Document existant : <strong>{{ $existing_kbis_document_name }}</strong>
                        </flux:text>
                    </div>
                @endif
                <flux:error name="kbis_document" />
            </flux:field>
            <flux:field class="mt-2">
                <flux:label>
                    Donneurs d'ordre
                    @if(!$hasExistingPrincipals)
                        <span class="text-red-500">*</span>
                    @endif
                </flux:label>
                <flux:input wire:model="principals" type="file" icon="document-plus" name="principals" multiple />
                <flux:text class="text-sm text-gray-500 mt-1">Vous pouvez sélectionner plusieurs fichiers</flux:text>
                @if(count($principals_names) > 0)
                    <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded-md">
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon.check-circle class="size-5 text-green-600" />
                            <flux:text class="text-sm text-green-800 font-medium">
                                {{ count($principals_names) }} nouveau(x) fichier(s) sélectionné(s) :
                            </flux:text>
                        </div>
                        <ul class="list-disc list-inside ml-7 space-y-1">
                            @foreach($principals_names as $fileName)
                                <li class="text-sm text-green-800">
                                    <strong>{{ $fileName }}</strong>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @elseif($hasExistingPrincipals && count($existing_principals_names) > 0)
                    <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon.check-circle class="size-5 text-blue-600" />
                            <flux:text class="text-sm text-blue-800 font-medium">
                                {{ count($existing_principals_names) }} document(s) existant(s) :
                            </flux:text>
                        </div>
                        <ul class="list-disc list-inside ml-7 space-y-1">
                            @foreach($existing_principals_names as $fileName)
                                <li class="text-sm text-blue-800">
                                    <strong>{{ $fileName }}</strong>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <flux:error name="principals" />
                @error('principals.*')
                    <flux:text class="text-sm text-red-600 mt-1">{{ $message }}</flux:text>
                @enderror
            </flux:field>
            <flux:field class="mt-2">
                <flux:label>
                    Référent sureté
                    @if(!$hasExistingSafetyReferent)
                        <span class="text-red-500">*</span>
                    @endif
                </flux:label>
                <flux:input wire:model="safety_referent_document" type="file" icon="document-plus" name="safety_referent_document" />
                @if($safety_referent_document_name)
                    <div class="mt-2 flex items-center gap-2 p-2 bg-green-50 border border-green-200 rounded-md">
                        <flux:icon.check-circle class="size-5 text-green-600" />
                        <flux:text class="text-sm text-green-800">
                            Nouveau fichier sélectionné : <strong>{{ $safety_referent_document_name }}</strong>
                        </flux:text>
                    </div>
                @elseif($hasExistingSafetyReferent && $existing_safety_referent_document_name)
                    <div class="mt-2 flex items-center gap-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                        <flux:icon.check-circle class="size-5 text-blue-600" />
                        <flux:text class="text-sm text-blue-800">
                            Document existant : <strong>{{ $existing_safety_referent_document_name }}</strong>
                        </flux:text>
                    </div>
                @endif
                <flux:error name="safety_referent_document" />
            </flux:field>
            <flux:field class="mt-2">
                <flux:label>
                    Référent sécurité
                    @if(!$hasExistingSecurityReferent)
                        <span class="text-red-500">*</span>
                    @endif
                </flux:label>
                <flux:input wire:model="security_referent_document" type="file" icon="document-plus" name="security_referent_document" />
                @if($security_referent_document_name)
                    <div class="mt-2 flex items-center gap-2 p-2 bg-green-50 border border-green-200 rounded-md">
                        <flux:icon.check-circle class="size-5 text-green-600" />
                        <flux:text class="text-sm text-green-800">
                            Nouveau fichier sélectionné : <strong>{{ $security_referent_document_name }}</strong>
                        </flux:text>
                    </div>
                @elseif($hasExistingSecurityReferent && $existing_security_referent_document_name)
                    <div class="mt-2 flex items-center gap-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                        <flux:icon.check-circle class="size-5 text-blue-600" />
                        <flux:text class="text-sm text-blue-800">
                            Document existant : <strong>{{ $existing_security_referent_document_name }}</strong>
                        </flux:text>
                    </div>
                @endif
                <flux:error name="security_referent_document" />
            </flux:field>
            @if($client && $client->is_airline_company)
                <flux:field class="mt-2">
                    <flux:label>
                        CTA
                        @if(!$hasExistingCta)
                            <span class="text-red-500">*</span>
                        @endif
                    </flux:label>
                    <flux:input wire:model="cta_document" type="file" icon="document-plus" name="cta_document" />
                    <flux:text class="text-sm text-gray-500 mt-1">Requis pour les compagnies aériennes</flux:text>
                    @if($cta_document_name)
                        <div class="mt-2 flex items-center gap-2 p-2 bg-green-50 border border-green-200 rounded-md">
                            <flux:icon.check-circle class="size-5 text-green-600" />
                            <flux:text class="text-sm text-green-800">
                                Nouveau fichier sélectionné : <strong>{{ $cta_document_name }}</strong>
                            </flux:text>
                        </div>
                    @elseif($hasExistingCta && $existing_cta_document_name)
                        <div class="mt-2 flex items-center gap-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                            <flux:icon.check-circle class="size-5 text-blue-600" />
                            <flux:text class="text-sm text-blue-800">
                                Document existant : <strong>{{ $existing_cta_document_name }}</strong>
                            </flux:text>
                        </div>
                    @endif
                    <flux:error name="cta_document" />
                </flux:field>
            @endif
    </div>
    @endif

    <!-- Actions -->
    <div class="flex gap-2 mt-4">
        <flux:button wire:click="closeModal">Annuler</flux:button>
        <flux:spacer />
        <flux:button wire:click="saveDraft" icon="document-arrow-down">
            {{ $activityRequestId ? 'Mettre à jour le brouillon' : 'Enregistrer en brouillon' }}
        </flux:button>
        <flux:button type="submit" variant="primary" :icon="$activityRequestId ? 'check' : 'plus'">
            {{ $activityRequestId ? 'Soumettre la demande' : 'Créer la demande' }}
        </flux:button>
    </div>
</form>