<form wire:submit="createClient" class="space-y-6">
        <div class="border-b border-gray-800/10 pb-4">
            <flux:heading size="xl">Nouvelle société</flux:heading>
            <flux:text class="mt-2">Saisissez les informations de la société.</flux:text>
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
            <div class="grid grid-cols-2 gap-4 mt-2">
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
            <flux:field class="mt-2">
                <flux:label>Adresse<span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="address" icon="map-pin" name="address" required />
                <flux:error name="address" />
            </flux:field>
            <div class="grid grid-cols-2 gap-4 mt-2">
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
            </div>
            <flux:field class="mt-2">
                <flux:label>Sous traitant de</flux:label>
                <flux:description>Listez les entreprises pour laquelle ce client est sous traitant.</flux:description>
                <flux:input wire:model="subcontractor_of" icon="building-office-2" name="subcontractor_of" />
                <flux:error name="subcontractor_of" />
            </flux:field>
        </div>

        <!-- Référents et contact -->
        <div class="border border-gray-800/10 p-4 rounded-lg">
            <flux:heading size="lg">Référents et contact</flux:heading>
            <flux:heading size="xl" class="mt-4">Référent sûreté 1<span class="text-red-500">*</span></flux:heading>
            <div class="grid grid-cols-2 gap-4 mt-2">
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
            </div>
            <div class="grid grid-cols-2 gap-4 mt-2">
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
            <flux:separator class="my-4" />
            <flux:heading size="xl" class="mt-4">Référent sûreté 2</flux:heading>
            <div class="grid grid-cols-2 gap-4 mt-2">
                <flux:field>
                    <flux:label>Prénom</flux:label>
                    <flux:input wire:model="safety_referent_2_prenom" name="safety_referent_2_prenom" />
                    <flux:error name="safety_referent_2_prenom" />
                </flux:field>

                <flux:field>
                    <flux:label>Nom</flux:label>
                    <flux:input name="safety_referent_2_nom" wire:model="safety_referent_2_nom" />
                    <flux:error name="safety_referent_2_nom" />
                </flux:field>
            </div>
            <div class="grid grid-cols-2 gap-4 mt-2">
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
            <flux:separator class="my-4" />
            <flux:heading size="xl" class="mt-4">Référent sûreté 3</flux:heading>
            <div class="grid grid-cols-2 gap-4 mt-2">
                <flux:field>
                    <flux:label>Prénom</flux:label>
                    <flux:input name="safety_referent_3_prenom" wire:model="safety_referent_3_prenom" />
                    <flux:error name="safety_referent_3_prenom" />
                </flux:field>

                <flux:field>
                    <flux:label>Nom</flux:label>
                    <flux:input name="safety_referent_3_nom" wire:model="safety_referent_3_nom" />
                    <flux:error name="safety_referent_3_nom" />
                </flux:field>
            </div>
            <div class="grid grid-cols-2 gap-4 mt-2">
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
            <flux:separator class="my-4" />
            <flux:heading size="xl" class="mt-4">Correspondant sécurité<span class="text-red-500">*</span></flux:heading>
            <div class="grid grid-cols-2 gap-4 mt-2">
                <flux:field>
                    <flux:label>Prénom<span class="text-red-500">*</span></flux:label>
                    <flux:input name="security_correspondent_prenom" wire:model="security_correspondent_prenom" required />
                    <flux:error name="security_correspondent_prenom" />
                </flux:field>
                <flux:field>
                    <flux:label>Nom<span class="text-red-500">*</span></flux:label>
                    <flux:input name="security_correspondent_nom" wire:model="security_correspondent_nom" required />
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
            <flux:separator class="my-4" />
            <flux:heading size="xl" class="mt-4">Contact RH<span class="text-red-500">*</span></flux:heading>
            <div class="grid grid-cols-2 gap-4 mt-2">
                <flux:field>
                    <flux:label>Prénom<span class="text-red-500">*</span></flux:label>
                    <flux:input name="hr_contact_prenom" wire:model="hr_contact_prenom" required />
                    <flux:error name="hr_contact_prenom" />
                </flux:field>
                <flux:field>
                    <flux:label>Nom<span class="text-red-500">*</span></flux:label>
                    <flux:input name="hr_contact_nom" wire:model="hr_contact_nom" required />
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

        <!-- Documents -->
        <div class="border border-gray-800/10 p-4 rounded-lg">
            <flux:heading size="lg">Documents</flux:heading>
            <flux:field class="mt-2">
                <flux:label>KBIS</flux:label>
                <flux:input type="file" icon="document-plus" wire:model="kbis_document" name="kbis_document" required />
                <flux:error name="kbis_document" />
            </flux:field>
            <flux:field class="mt-2">
                <flux:label>Référents sûreté</flux:label>
                <flux:input type="file" icon="document-plus" wire:model="safety_document" name="safety_document" required />
                <flux:error name="safety_document" />
            </flux:field>
            <flux:field class="mt-2">
                <flux:label>Correspondant sécurité</flux:label>
                <flux:input type="file" icon="document-plus" wire:model="security_document" name="security_document" required />
                <flux:error name="security_document" />
            </flux:field>
        </div>

        <!-- Email de notification -->
        <div class="border border-gray-800/10 p-4 rounded-lg">
            <flux:heading size="lg">Email de notification</flux:heading>
            <flux:field class="mt-2">
                <flux:label>Email</flux:label>
                <flux:description>Si renseigné, c'est à cet email que seront envoyées les notifications pour toutes les demandes de cette société. Sinon, ce sera à celui du demandeur.</flux:description>
                <flux:input type="email" icon="at-symbol" wire:model="notification_email" name="notification_email" />
                <flux:error name="notification_email" />
            </flux:field>
        </div>

        <!-- Quota -->
        <div class="border border-gray-800/10 p-4 rounded-lg">
            <flux:heading size="lg">Configuration des Quotas</flux:heading>
            <div class="grid grid-cols-2 gap-4 mt-2">
                <flux:field>
                    <flux:label>Quota de badges<span class="text-red-500">*</span></flux:label>
                    <flux:input type="number" icon:trailing="identification" wire:model="badge_limit" name="badge_limit" min="1" max="1000" required />
                    <flux:error name="badge_limit" />
                </flux:field>
                <flux:field>
                    <flux:label>Quota de véhicules<span class="text-red-500">*</span></flux:label>
                    <flux:input type="number" icon:trailing="truck" wire:model="vehicle_pass_limit" name="vehicle_pass_limit" min="0" max="1000" required />
                    <flux:error name="vehicle_pass_limit" />
                </flux:field>
            </div>
        </div>

        <div class="flex gap-2 mt-4">
            <flux:spacer />
            <flux:button wire:click="cancelModal">Annuler</flux:button>
            <flux:button type="submit" variant="primary" icon="plus">Créer la société</flux:button>
        </div>
    </form>
