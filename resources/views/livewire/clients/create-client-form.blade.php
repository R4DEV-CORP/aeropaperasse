<form wire:submit="createClient" class="space-y-6">
    <div>
        <div class="flex items-center gap-2">
            <flux:icon.building-office class="size-6"/>
            <flux:heading size="lg">Créer une nouvelle société</flux:heading>
        </div>
        <flux:text class="mt-2">Saisissez les informations de la société.</flux:text>
    </div>

    @if($errorMessage)
        <flux:callout class="mt-4" variant="danger" icon="x-circle" heading="{{ $errorMessage }}" />
    @endif

    @if($successMessage)
        <flux:callout class="mt-4" variant="success" icon="check-circle" heading="{{ $successMessage }}" />
    @endif

    <!-- Informations sur la société -->
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <div class="grid grid-cols-3 gap-4">
            <flux:field>
                <flux:label>Raison sociale<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="company_name" icon="building-office" name="company_name" required />
                <flux:error name="company_name" />
            </flux:field>
            <flux:field>
                <flux:label>Nom commercial<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="trade_name" icon="building-office" name="trade_name" required />
                <flux:error name="trade_name" />
            </flux:field>
            <flux:field>
                <flux:label>Numéro SIRET<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="siret_number" icon="hashtag" name="siret_number" required />
                <flux:error name="siret_number" />
            </flux:field>
        </div>

        <div class="grid grid-cols-4 gap-4">
            <flux:field class="col-span-2">
                <flux:label>Adresse<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="address" icon="map-pin" name="address" required />
                <flux:error name="address" />
            </flux:field>
            <flux:field>
                <flux:label>Code postal<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="zip_code" name="zip_code" required />
                <flux:error name="zip_code" />
            </flux:field>
            <flux:field>
                <flux:label>Ville<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="city" name="city" required />
                <flux:error name="city" />
            </flux:field>  
            <flux:field class="col-span-2">
                <flux:label>Sous traitant de</flux:label>
                <flux:description>Listez les entreprises pour laquelle ce client est sous traitant.</flux:description>
                <flux:input wire:model="subcontractor_of" icon="building-office-2" name="subcontractor_of" />
                <flux:error name="subcontractor_of" />
            </flux:field>
            <div class="flex items-end col-span-2">
                <flux:field variant="inline" class="mb-2">
                    <flux:checkbox wire:model="is_airline_company" />
                    <flux:label>Cette société est une société aérienne</flux:label>
                    <flux:error name="is_airline_company" />
                </flux:field>
            </div>
        </div>
    </div>

    <!-- Référents et contact -->
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <div>
            <div class="flex items-center gap-2">
                <flux:icon.user-group class="size-4"/>
                <flux:heading size="lg">Référents et contact</flux:heading>
            </div>
            <flux:text class="mt-2">Saisissez tous les contacts et référents de la société.</flux:text>
        </div>
        <!-- Référents sûreté -->
        <div class="grid grid-cols-3 gap-4 mt-4">
            <div class="space-y-2 border border-gray-800/10 p-2 rounded-lg">
                <flux:heading size="lg">Référent sûreté 1<span class="text-red-500 text-base">*</span></flux:heading>
                <flux:field>
                    <flux:label>Prénom<span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="safety_referent_1_prenom" name="safety_referent_1_prenom" required />
                    <flux:error name="safety_referent_1_prenom" />
                </flux:field>
                <flux:field>
                    <flux:label>Nom<span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="safety_referent_1_nom" name="safety_referent_1_nom" required />
                    <flux:error name="safety_referent_1_nom" />
                </flux:field>
                <flux:field>
                    <flux:label>Email<span class="text-red-500">*</span></flux:label>
                    <flux:input type="email" icon="at-symbol" wire:model="safety_referent_1_email" name="safety_referent_1_email" required />
                    <flux:error name="safety_referent_1_email" />
                </flux:field>
                <flux:field>
                    <flux:label>Phone<span class="text-red-500">*</span></flux:label>
                    <flux:input icon="phone" wire:model="safety_referent_1_phone" name="safety_referent_1_phone" required />
                    <flux:error name="safety_referent_1_phone" />
                </flux:field>
            </div>
            <div class="space-y-2 border border-gray-800/10 p-2 rounded-lg">
                <flux:heading size="lg">Référent sûreté 2</flux:heading>
                <flux:field>
                    <flux:label>Prénom</flux:label>
                    <flux:input wire:model="safety_referent_2_prenom" name="safety_referent_2_prenom" />
                    <flux:error name="safety_referent_2_prenom" />
                </flux:field>
                <flux:field>
                    <flux:label>Nom</flux:label>
                    <flux:input wire:model="safety_referent_2_nom" name="safety_referent_2_nom" />
                    <flux:error name="safety_referent_2_nom" />
                </flux:field>
                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input type="email" icon="at-symbol" wire:model="safety_referent_2_email" name="safety_referent_2_email" />
                    <flux:error name="safety_referent_2_email" />
                </flux:field>
                <flux:field>
                    <flux:label>Phone</flux:label>
                    <flux:input icon="phone" wire:model="safety_referent_2_phone" name="safety_referent_2_phone" />
                    <flux:error name="safety_referent_2_phone" />
                </flux:field>
            </div>
            <div class="space-y-2 border border-gray-800/10 p-2 rounded-lg">
                <flux:heading size="lg">Référent sûreté 3</flux:heading>
                <flux:field>
                    <flux:label>Prénom</flux:label>
                    <flux:input wire:model="safety_referent_3_prenom" name="safety_referent_3_prenom" />
                    <flux:error name="safety_referent_3_prenom" />
                </flux:field>
                <flux:field>
                    <flux:label>Nom</flux:label>
                    <flux:input wire:model="safety_referent_3_nom" name="safety_referent_3_nom" />
                    <flux:error name="safety_referent_3_nom" />
                </flux:field>
                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input type="email" icon="at-symbol" wire:model="safety_referent_3_email" name="safety_referent_3_email" />
                    <flux:error name="safety_referent_3_email" />
                </flux:field>
                <flux:field>
                    <flux:label>Phone</flux:label>
                    <flux:input icon="phone" wire:model="safety_referent_3_phone" name="safety_referent_3_phone" />
                    <flux:error name="safety_referent_3_phone" />
                </flux:field>
            </div>
        </div>
        <!-- Correspondant sécurité et RH-->
        <div class="grid grid-cols-3 gap-4 mt-4">
            <div class="space-y-2 border border-gray-800/10 p-2 rounded-lg">
                <flux:heading size="lg">Correspondant sécurité<span class="text-red-500 text-base">*</span></flux:heading>
                <flux:field>
                    <flux:label>Prénom<span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="security_correspondent_prenom" name="security_correspondent_prenom" required />
                    <flux:error name="security_correspondent_prenom" />
                </flux:field>
                <flux:field>
                    <flux:label>Nom<span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="security_correspondent_nom" name="security_correspondent_nom" required />
                    <flux:error name="security_correspondent_nom" />
                </flux:field>
                <flux:field>
                    <flux:label>Email<span class="text-red-500">*</span></flux:label>
                    <flux:input type="email" icon="at-symbol" wire:model="security_correspondent_email" name="security_correspondent_email" required />
                    <flux:error name="security_correspondent_email" />
                </flux:field>
                <flux:field>
                    <flux:label>Phone<span class="text-red-500">*</span></flux:label>
                    <flux:input icon="phone" wire:model="security_correspondent_phone" name="security_correspondent_phone" required />
                    <flux:error name="security_correspondent_phone" />
                </flux:field>
            </div>
            <div class="space-y-2 border border-gray-800/10 p-2 rounded-lg">
                <flux:heading size="lg">Contact RH<span class="text-red-500 text-base">*</span></flux:heading>
                <flux:field>
                    <flux:label>Prénom<span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="hr_contact_prenom" name="hr_contact_prenom" required />
                    <flux:error name="hr_contact_prenom" />
                </flux:field>
                <flux:field>
                    <flux:label>Nom<span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="hr_contact_nom" name="hr_contact_nom" required />
                    <flux:error name="hr_contact_nom" />
                </flux:field>
                <flux:field>
                    <flux:label>Email<span class="text-red-500">*</span></flux:label>
                    <flux:input type="email" icon="at-symbol" wire:model="hr_contact_email" name="hr_contact_email" required />
                    <flux:error name="hr_contact_email" />
                </flux:field>
                <flux:field>
                    <flux:label>Phone<span class="text-red-500">*</span></flux:label>
                    <flux:input icon="phone" wire:model="hr_contact_phone" name="hr_contact_phone" required />
                    <flux:error name="hr_contact_phone" />
                </flux:field>
            </div>
        </div>
    </div>

    <!-- Document -->
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <div>
            <div class="flex items-center gap-2">
                <flux:icon.document class="size-4"/>
                <flux:heading size="lg">Documents</flux:heading>
            </div>
            <flux:text class="mt-2">Importez les documents obligatoires.</flux:text>
        </div>
        <div class="grid grid-cols-3 gap-4 mt-4">
            <flux:field>
                <flux:label>KBIS<span class="text-red-500">*</span></flux:label>
                <flux:input type="file" icon="document-plus" wire:model="kbis_document" name="kbis_document" required />
                <flux:error name="kbis_document" />
            </flux:field>
            <flux:field>
                <flux:label>Référents sûreté<span class="text-red-500">*</span></flux:label>
                <flux:input type="file" icon="document-plus" wire:model="safety_document" name="safety_document" required />
                <flux:error name="safety_document" />
            </flux:field>
            <flux:field>
                <flux:label>Correspondant sécurité<span class="text-red-500">*</span></flux:label>
                <flux:input type="file" icon="document-plus" wire:model="security_document" name="security_document" required />
                <flux:error name="security_document" />
            </flux:field>
        </div>
    </div>

    <!-- Informations complémentaires -->
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <div>
            <div class="flex items-center gap-2">
                <flux:icon.information-circle class="size-4"/>
                <flux:heading size="lg">Informations complémentaires</flux:heading>
            </div>
            <flux:text class="mt-2">Saisissez les informations complémentaires de la société.</flux:text>
        </div>
        <div class="grid grid-cols-3 gap-4 mt-4">
            <flux:field>
                <flux:label>Email de notification</flux:label>
                <flux:input type="email" icon="at-symbol" wire:model="notification_email" name="notification_email" />
                <flux:description>Si renseigné, c'est à cet email que seront envoyées les notifications pour toutes les demandes de cette société. Sinon, ce sera à celui du demandeur.</flux:description>
                <flux:error name="notification_email" />
            </flux:field>
            <flux:field>
                <flux:label>Quota de badges</flux:label>
                <flux:input type="number" icon:trailing="identification" wire:model="badge_limit" name="badge_limit" min="1" max="1000" required />
                <flux:error name="badge_limit" />
            </flux:field>
            <flux:field>
                <flux:label>Quota de véhicules</flux:label>
                <flux:input type="number" icon:trailing="truck" wire:model="vehicle_pass_limit" name="vehicle_pass_limit" min="0" max="1000" required />
                <flux:error name="vehicle_pass_limit" />
            </flux:field>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-2 mt-4">
        <flux:button wire:click="cancelModal">Annuler</flux:button>
        <flux:spacer />
        <flux:button type="submit" variant="primary" icon="plus">Créer la société</flux:button>
    </div>
</form>
