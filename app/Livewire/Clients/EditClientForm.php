<?php

namespace App\Livewire\Clients;

use App\Actions\Client\UpdateClientAction;
use App\Models\Client;
use App\Validators\ClientValidator;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditClientForm extends Component
{
    use WithFileUploads;

    public $clientId;

    public $client;

    // Propriétés pour le formulaire
    public $company_name;

    public $trade_name;

    public $siret_number;

    public $address;

    public $zip_code;

    public $city;

    public $kbis_document;

    public $safety_document;

    public $security_document;

    public $badge_limit;

    public $vehicle_pass_limit;

    public $notification_email;

    public $subcontractor_of;

    // Propriétés contacts
    public $safety_referent_1_prenom;

    public $safety_referent_1_nom;

    public $safety_referent_1_email;

    public $safety_referent_1_phone;

    public $safety_referent_2_prenom;

    public $safety_referent_2_nom;

    public $safety_referent_2_email;

    public $safety_referent_2_phone;

    public $safety_referent_3_prenom;

    public $safety_referent_3_nom;

    public $safety_referent_3_email;

    public $safety_referent_3_phone;

    public $security_correspondent_prenom;

    public $security_correspondent_nom;

    public $security_correspondent_email;

    public $security_correspondent_phone;

    public $hr_contact_prenom;

    public $hr_contact_nom;

    public $hr_contact_email;

    public $hr_contact_phone;

    // Propriétés pour la gestion des messages
    public $successMessage = '';

    public $errorMessage = '';

    // Propriétés pour les documents existants
    public $hasExistingKbis = false;

    public $hasExistingSafety = false;

    public $hasExistingSecurity = false;

    public function mount($clientId)
    {
        $this->clientId = $clientId;
        $this->client = Client::findOrFail($clientId);

        // Charger les données du client
        $this->loadClientData();

        // Charger les contacts
        $this->loadContacts();

        // Vérifier les documents existants
        $this->checkExistingDocuments();
    }

    protected function loadClientData(): void
    {
        $this->company_name = $this->client->company_name;
        $this->trade_name = $this->client->trade_name;
        $this->siret_number = $this->client->siret_number;
        $this->address = $this->client->address;
        $this->zip_code = $this->client->zip_code;
        $this->city = $this->client->city;
        $this->subcontractor_of = $this->client->subcontractor_of;
        $this->badge_limit = $this->client->badge_limit;
        $this->vehicle_pass_limit = $this->client->vehicle_pass_limit;
        $this->notification_email = $this->client->notification_email;
    }

    protected function loadContacts(): void
    {
        $contacts = $this->client->contacts;

        // Référents sûreté
        $safetyReferents = $contacts->where('role', 'safety');

        if ($safetyReferents->count() >= 1) {
            $ref1 = $safetyReferents->first();
            $this->safety_referent_1_prenom = $ref1->firstname;
            $this->safety_referent_1_nom = $ref1->lastname;
            $this->safety_referent_1_email = $ref1->email;
            $this->safety_referent_1_phone = $ref1->phone;
        }

        if ($safetyReferents->count() >= 2) {
            $ref2 = $safetyReferents->skip(1)->first();
            $this->safety_referent_2_prenom = $ref2->firstname;
            $this->safety_referent_2_nom = $ref2->lastname;
            $this->safety_referent_2_email = $ref2->email;
            $this->safety_referent_2_phone = $ref2->phone;
        }

        if ($safetyReferents->count() >= 3) {
            $ref3 = $safetyReferents->skip(2)->first();
            $this->safety_referent_3_prenom = $ref3->firstname;
            $this->safety_referent_3_nom = $ref3->lastname;
            $this->safety_referent_3_email = $ref3->email;
            $this->safety_referent_3_phone = $ref3->phone;
        }

        // Correspondant sécurité
        $securityCorrespondent = $contacts->where('role', 'security')->first();
        if ($securityCorrespondent) {
            $this->security_correspondent_prenom = $securityCorrespondent->firstname;
            $this->security_correspondent_nom = $securityCorrespondent->lastname;
            $this->security_correspondent_email = $securityCorrespondent->email;
            $this->security_correspondent_phone = $securityCorrespondent->phone;
        }

        // Contact RH
        $hrContact = $contacts->where('role', 'hr')->first();
        if ($hrContact) {
            $this->hr_contact_prenom = $hrContact->firstname;
            $this->hr_contact_nom = $hrContact->lastname;
            $this->hr_contact_email = $hrContact->email;
            $this->hr_contact_phone = $hrContact->phone;
        }
    }

    protected function checkExistingDocuments(): void
    {
        $this->hasExistingKbis = ! empty($this->client->kbis_document);
        $this->hasExistingSafety = ! empty($this->client->safety_document);
        $this->hasExistingSecurity = ! empty($this->client->security_document);
    }

    public function updateClient()
    {
        try {
            // 1. Récupérer les données du formulaire
            $formData = $this->getFormData();

            // 2. Valider les données
            $validator = ClientValidator::validateUpdate($formData);

            if ($validator->fails()) {
                $this->errorMessage = 'Erreurs de validation détectées.';
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $this->addError($field, $messages[0]);
                }

                return;
            }

            // 3. Mettre à jour le client
            $action = app(UpdateClientAction::class);
            $result = $action->execute($this->client, $formData);

            if ($result->isSuccessful()) {
                $this->successMessage = $result->getMessage();
                $this->dispatch('client-updated');

                // Fermer la modal après un délai
                $this->dispatch('close-modal', ['modal' => 'edit-client-'.$this->clientId]);
            } else {
                $this->errorMessage = $result->getMessage();
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'Une erreur inattendue s\'est produite : '.$e->getMessage();
        }
    }

    /**
     * Get all form data as an array.
     */
    protected function getFormData(): array
    {
        return [
            'company_name' => $this->company_name,
            'trade_name' => $this->trade_name,
            'siret_number' => $this->siret_number,
            'address' => $this->address,
            'zip_code' => $this->zip_code,
            'city' => $this->city,
            'subcontractor_of' => $this->subcontractor_of,
            'kbis_document' => $this->kbis_document,
            'safety_document' => $this->safety_document,
            'security_document' => $this->security_document,
            'badge_limit' => $this->badge_limit,
            'vehicle_pass_limit' => $this->vehicle_pass_limit,
            'notification_email' => $this->notification_email,
            'safety_referent_1_prenom' => $this->safety_referent_1_prenom,
            'safety_referent_1_nom' => $this->safety_referent_1_nom,
            'safety_referent_1_email' => $this->safety_referent_1_email,
            'safety_referent_1_phone' => $this->safety_referent_1_phone,
            'safety_referent_2_prenom' => $this->safety_referent_2_prenom,
            'safety_referent_2_nom' => $this->safety_referent_2_nom,
            'safety_referent_2_email' => $this->safety_referent_2_email,
            'safety_referent_2_phone' => $this->safety_referent_2_phone,
            'safety_referent_3_prenom' => $this->safety_referent_3_prenom,
            'safety_referent_3_nom' => $this->safety_referent_3_nom,
            'safety_referent_3_email' => $this->safety_referent_3_email,
            'safety_referent_3_phone' => $this->safety_referent_3_phone,
            'security_correspondent_prenom' => $this->security_correspondent_prenom,
            'security_correspondent_nom' => $this->security_correspondent_nom,
            'security_correspondent_email' => $this->security_correspondent_email,
            'security_correspondent_phone' => $this->security_correspondent_phone,
            'hr_contact_prenom' => $this->hr_contact_prenom,
            'hr_contact_nom' => $this->hr_contact_nom,
            'hr_contact_email' => $this->hr_contact_email,
            'hr_contact_phone' => $this->hr_contact_phone,
        ];
    }

    /**
     * Clear success message.
     */
    public function clearSuccessMessage(): void
    {
        $this->successMessage = '';
    }

    /**
     * Clear error message.
     */
    public function clearErrorMessage(): void
    {
        $this->errorMessage = '';
    }

    /**
     * Cancel modal and reset form.
     */
    public function cancelModal(): void
    {
        Flux::modal('edit-client-'.$this->clientId)->close();
        Flux::modal('edit-client')->close();
    }

    public function render()
    {
        return view('livewire.clients.edit-client-form');
    }
}
