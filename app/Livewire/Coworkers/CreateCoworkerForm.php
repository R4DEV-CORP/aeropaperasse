<?php

namespace App\Livewire\Coworkers;

use App\Actions\Coworker\CreateCoworkerAction;
use App\DataTransferObjects\CreateCoworkerData;
use App\Models\Client;
use App\Validators\CoworkerValidator;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CreateCoworkerForm extends Component
{
    public $user;

    public $client;

    public $allClients;

    public $selected_client_id;

    // Messages
    public $errorMessage;

    public $successMessage;

    // Données du formulaire
    public $create_user = false;

    public $firstname;

    public $lastname;

    public $email;

    public $phone;

    public $can_access_formation = false;

    public $has_leave = false;

    public $departure_date;

    public $password;

    public $password_confirmation;

    public $role = 'client';

    // État du formulaire
    public $isSubmitting = false;

    public function mount()
    {
        $this->user = auth()->user();

        if ($this->user->isAdmin()) {
            $this->loadClients();
        } else {
            $this->client = $this->user->client;
            $this->selected_client_id = $this->client->id;
        }

        // Réinitialiser les messages
        $this->clearMessages();
    }

    public function loadClients()
    {
        $this->allClients = Client::orderBy('company_name')->get();
        $this->selected_client_id = null;
    }

    public function updatedCreateUser()
    {
        // Réinitialiser les champs de mot de passe quand on change l'option
        if (! $this->create_user) {
            $this->password = '';
            $this->password_confirmation = '';
        }
    }

    public function updatedHasLeave()
    {
        // Réinitialiser la date de départ si on désactive le départ
        if (! $this->has_leave) {
            $this->departure_date = null;
        }
    }

    public function submit()
    {
        $this->isSubmitting = true;
        $this->clearMessages();

        try {
            // Validation avec CoworkerValidator uniquement
            $validationData = $this->getValidationData();
            $validator = CoworkerValidator::validateComplete($validationData);

            if ($validator->fails()) {
                // Afficher la première erreur
                $this->errorMessage = $validator->errors()->first();

                return;
            }

            // Créer le DTO
            $data = CreateCoworkerData::fromArray($validationData, $this->user->id);

            // Exécuter l'action
            $action = new CreateCoworkerAction;
            $result = $action->execute($data);

            if ($result->isSuccessful()) {
                $this->successMessage = $result->getMessage();
                $this->resetForm();

                // Fermer la modal
                $this->dispatch('close-modal', name: 'new-coworker');

                // Émettre un événement pour rafraîchir la liste
                $this->dispatch('coworker-created', $result->getData());
            } else {
                $this->errorMessage = $result->getMessage();
            }

        } catch (\Exception $e) {
            Log::error('Erreur dans CreateCoworkerForm::submit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->user->id,
            ]);

            $this->errorMessage = 'Une erreur est survenue lors de la création du collaborateur.';
        } finally {
            $this->isSubmitting = false;
        }
    }

    /**
     * Prépare les données pour la validation
     */
    private function getValidationData(): array
    {
        $data = [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'client_id' => $this->selected_client_id,
            'can_access_formation' => $this->can_access_formation,
            'has_leave' => $this->has_leave,
            'departure_date' => $this->departure_date,
            'create_user' => $this->create_user,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'role' => $this->role,
        ];

        // Si create_user est false, nettoyer les champs password
        if (! $this->create_user) {
            $data['password'] = null;
            $data['password_confirmation'] = null;
        }

        return $data;
    }

    /**
     * Réinitialise le formulaire
     */
    public function resetForm()
    {
        $this->firstname = '';
        $this->lastname = '';
        $this->email = '';
        $this->phone = '';
        $this->can_access_formation = false;
        $this->has_leave = false;
        $this->departure_date = '';
        $this->create_user = false;
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = 'client';

        // Réinitialiser les erreurs de validation
        $this->resetErrorBag();
    }

    /**
     * Efface les messages d'erreur et de succès
     */
    public function clearMessages()
    {
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    public function closeModal()
    {
        $this->resetForm();
        Flux::modal('new-coworker')->close();
    }

    public function render()
    {
        return view('livewire.coworkers.create-coworker-form');
    }
}
