<?php

namespace App\Livewire\Clients;

use App\Actions\Client\CreateClientAction;
use App\DataTransferObjects\CreateClientData;
use App\Validators\ClientValidator;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateClientForm extends Component
{
    use WithFileUploads;

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

    public function createClient()
    {
        try {
            // 1. Récupérer les données du formulaire
            $formData = $this->getFormData();

            // 2. Valider les données
            $validator = ClientValidator::validate($formData);

            if ($validator->fails()) {
                $this->errorMessage = 'Erreurs de validation détectées.';
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $this->addError($field, $messages[0]);
                }

                return;
            }

            // 3. Créer le DTO avec les données validées
            $clientData = CreateClientData::fromArray($formData);

            // 4. Exécuter l'action de création
            $action = app(CreateClientAction::class);
            $result = $action->execute($clientData);

            if ($result->isSuccessful()) {
                $this->successMessage = $result->getMessage();
                $this->dispatch('client-created');
                $this->resetForm();
                $this->cancelModal();
            } else {
                $this->errorMessage = $result->getMessage();
            }

        } catch (\Exception $e) {
            // Gérer les erreurs inattendues
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
     * Reset the form to its initial state.
     */
    public function resetForm(): void
    {
        $this->reset([
            'company_name',
            'trade_name',
            'siret_number',
            'address',
            'zip_code',
            'city',
            'subcontractor_of',
            'kbis_document',
            'safety_document',
            'security_document',
            'badge_limit',
            'vehicle_pass_limit',
            'notification_email',
            'safety_referent_1_prenom',
            'safety_referent_1_nom',
            'safety_referent_1_email',
            'safety_referent_1_phone',
            'safety_referent_2_prenom',
            'safety_referent_2_nom',
            'safety_referent_2_email',
            'safety_referent_2_phone',
            'safety_referent_3_prenom',
            'safety_referent_3_nom',
            'safety_referent_3_email',
            'safety_referent_3_phone',
            'security_correspondent_prenom',
            'security_correspondent_nom',
            'security_correspondent_email',
            'security_correspondent_phone',
            'hr_contact_prenom',
            'hr_contact_nom',
            'hr_contact_email',
            'hr_contact_phone',
            'errorMessage',
        ]);
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
        $this->resetForm();
        Flux::modal('new-client')->close();
    }

    public function render()
    {
        return view('livewire.clients.create-client-form');
    }
}
