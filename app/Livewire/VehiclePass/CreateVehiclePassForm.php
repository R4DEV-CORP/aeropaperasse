<?php

namespace App\Livewire\VehiclePass;

use App\Actions\VehiclePass\SaveVehiclePassAction;
use App\DataTransferObjects\CreateVehiclePassData;
use App\Models\ActivityRequest;
use App\Models\Client;
use App\Validators\VehiclePassValidator;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateVehiclePassForm extends Component
{
    use WithFileUploads;

    public $user;

    public $client;

    public $allClients; // Liste de tous les clients (pour les admins)

    public $selected_client_id; // ID du client sélectionné par l'admin

    public $activityRequests; // Liste des demandes d'activités (approved ou pending)

    public $selected_activity_request_id; // ID de la demande d'activité sélectionnée

    public $activityRequest; // Demande d'activité sélectionnée (pour affichage)

    // Propriétés pour le formulaire
    public $plate_number;

    public $car_brand;

    public $airport;

    // Propriétés pour le formulaire - Documents
    public $certificate_of_registration;

    public $company_stamp;

    // Propriétés pour la gestion des messages
    public $successMessage = '';

    public $errorMessage = '';

    public function mount()
    {
        $this->user = auth()->user();

        // Si l'utilisateur est admin ou sadmin, charger tous les clients
        if ($this->user->isAdmin()) {
            $this->allClients = Client::orderBy('company_name')->get();
            // Par défaut, ne pas sélectionner de client
            $this->selected_client_id = null;
            $this->client = null;
            $this->activityRequests = collect();
        } else {
            // Pour les utilisateurs normaux, utiliser leur client
            $this->client = $this->user->client;
            $this->selected_client_id = $this->client->id;
            // Charger les demandes d'activités approved ou pending
            $this->loadActivityRequests();
        }
    }

    /**
     * Charger les demandes d'activités approved ou pending du client
     */
    protected function loadActivityRequests(): void
    {
        if ($this->client) {
            $this->activityRequests = ActivityRequest::where('client_id', $this->client->id)
                ->whereIn('status', ['approved', 'pending'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $this->activityRequests = collect();
        }
    }

    /**
     * Gérer le changement de client sélectionné (pour les admins)
     */
    public function updatedSelectedClientId($value)
    {
        if ($value === '' || $value === null) {
            $this->selected_client_id = null;
            $this->client = null;
            $this->activityRequests = collect();
            $this->selected_activity_request_id = null;
        } else {
            $this->selected_client_id = (int) $value;
            $this->client = \App\Models\Client::find($this->selected_client_id);
            // Charger les demandes d'activités du client sélectionné
            $this->loadActivityRequests();
            // Réinitialiser la sélection de la demande d'activité
            $this->selected_activity_request_id = null;
        }
    }

    /**
     * Gérer le changement de la demande d'activité sélectionnée
     */
    public function updatedSelectedActivityRequestId($value)
    {
        if ($value === '' || $value === null) {
            $this->selected_activity_request_id = null;
            $this->activityRequest = null;
        } else {
            $this->selected_activity_request_id = (int) $value;
            $this->activityRequest = ActivityRequest::find($this->selected_activity_request_id);
        }
    }

    /**
     * Réinitialiser uniquement les champs du formulaire (pas les messages)
     */
    protected function resetFormFields(): void
    {
        $this->reset([
            'plate_number',
            'car_brand',
            'airport',
            'selected_activity_request_id',
        ]);
    }

    /**
     * Créer le laissez-passer véhicule
     */
    public function createVehiclePass()
    {
        $this->processVehiclePass();
    }

    /**
     * Traiter le laissez-passer véhicule
     */
    protected function processVehiclePass(): void
    {
        try {
            // Vérifier qu'un client est sélectionné (pour les admins)
            if ($this->user->isAdmin() && ! $this->client) {
                $this->errorMessage = 'Veuillez sélectionner un client.';

                return;
            }

            // Vérifier qu'une demande d'activité est sélectionnée
            if (! $this->selected_activity_request_id) {
                $this->errorMessage = 'Veuillez sélectionner une demande d\'activité.';

                return;
            }

            // Vérifier le quota de la demande d'activité
            $activityRequest = ActivityRequest::findOrFail($this->selected_activity_request_id);

            if (! $activityRequest->canCreateVehiclePass()) {
                $remaining = $activityRequest->getRemainingVehiclePassQuota();
                $this->errorMessage = "Le quota de laissez-passer véhicules pour cette demande d'activité est atteint. Il reste {$remaining} place(s) disponible(s).";

                return;
            }

            // 1. Préparer les données
            $data = [
                'plate_number' => $this->plate_number,
                'car_brand' => $this->car_brand,
                'airport' => $this->airport,
                'activity_request_id' => $this->selected_activity_request_id,
                'certificate_of_registration' => $this->certificate_of_registration,
                'company_stamp' => $this->company_stamp,
            ];

            // 2. Valider les données
            $validator = VehiclePassValidator::validate($data);

            if ($validator->fails()) {
                $this->handleValidationErrors($validator);

                return;
            }

            // 3. Créer le DTO
            $vehiclePassData = CreateVehiclePassData::fromArray(
                $data,
                $this->client->id,
                $this->user->id
            );

            // 4. Exécuter l'action unifiée
            $action = app(SaveVehiclePassAction::class);
            $result = $action->execute(
                $vehiclePassData,
                $this->client
            );

            // 5. Traiter le résultat
            if ($result->isSuccessful()) {
                $this->successMessage = $result->getMessage();
                $this->dispatch('vehicle-pass-created');
                $this->resetForm();
                $this->closeModal();
            } else {
                $this->errorMessage = $result->getMessage();
            }

        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Gère les erreurs de validation
     */
    protected function handleValidationErrors($validator): void
    {
        $this->errorMessage = 'Erreurs de validation détectées.';

        // Logger les erreurs de validation pour faciliter le débogage
        Log::warning('Erreurs de validation lors de la soumission d\'un laissez-passer véhicule', [
            'user_id' => $this->user->id,
            'client_id' => $this->client?->id,
            'validation_errors' => $validator->errors()->messages(),
        ]);

        foreach ($validator->errors()->messages() as $field => $messages) {
            $this->addError($field, $messages[0]);
        }
    }

    /**
     * Gère les exceptions
     */
    protected function handleException(\Exception $e): void
    {
        Log::error('Erreur lors du traitement du laissez-passer véhicule', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $this->errorMessage = 'Une erreur est survenue lors de la création du laissez-passer véhicule. Veuillez réessayer.';
    }

    /**
     * Réinitialiser le formulaire
     */
    public function resetForm(): void
    {
        $this->reset([
            'plate_number',
            'car_brand',
            'airport',
            'certificate_of_registration',
            'company_stamp',
            'selected_activity_request_id',
            'errorMessage',
        ]);

        // Réinitialiser la sélection de client pour les admins
        if ($this->user->isAdmin()) {
            $this->selected_client_id = null;
            $this->client = null;
            $this->activityRequests = collect();
        }

        $this->activityRequest = null;
    }

    /**
     * Effacer le message de succès
     */
    public function clearSuccessMessage(): void
    {
        $this->successMessage = '';
    }

    /**
     * Effacer le message d'erreur
     */
    public function clearErrorMessage(): void
    {
        $this->errorMessage = '';
    }

    /**
     * Fermer la modal
     */
    public function closeModal(): void
    {
        $this->resetForm();
        Flux::modal('new-vehicle-pass-request')->close();
    }

    public function render()
    {
        return view('livewire.vehicle-pass.create-vehicle-pass-form');
    }
}
