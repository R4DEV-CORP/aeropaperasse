<?php

namespace App\Livewire\ActivityRequests;

use App\Actions\ActivityRequest\SaveActivityRequestAction;
use App\DataTransferObjects\CreateActivityRequestData;
use App\Forms\ActivityRequestFormData;
use App\Forms\ActivityRequestFormValidator;
use App\Models\ActivityRequest;
use App\Models\Client;
use App\Services\ActivityRequestRenewalService;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Mail;
use App\Mail\ActivityRequestCreated;

class CreateActivityRequestForm extends Component
{
    use WithFileUploads;

    public $user;

    public $client;

    public $allClients; // Liste de tous les clients (pour les admins)

    public $selected_client_id; // ID du client sélectionné par l'admin

    // ID de la demande d'activité à éditer (null = mode création)
    public ?int $activityRequestId = null;

    public $previousActivityRequests;

    public $selectedPreviousActivityRequest = null;

    public bool $renewal = false;

    // Propriétés pour le formulaire - Responsable
    public $manager_firstname;

    public $manager_lastname;

    public $manager_email;

    public $manager_phone;

    public $manager_role;

    // Propriétés pour le formulaire - Informations sur l'activité
    public $airport;

    public $description;

    public $customer_names;

    public $person_count;

    public $vehicule_count;

    // Propriétés pour le formulaire - Documents
    public $aao_request_document;

    public $kbis_document;

    public $term_document;

    public $safety_referent_document;

    public $cta_document;

    // Indicateurs de documents existants (pour l'édition)
    public bool $hasExistingAaoRequest = false;

    public bool $hasExistingKbis = false;

    public bool $hasExistingTerm = false;

    public bool $hasExistingSafetyReferent = false;

    public bool $hasExistingCta = false;

    // Propriétés pour la gestion des messages
    public $successMessage = '';

    public $errorMessage = '';

    public function mount(?int $activityRequestId = null)
    {
        $this->user = auth()->user();

        // Si l'utilisateur est admin ou sadmin, charger tous les clients
        if ($this->user->isAdmin()) {
            $this->allClients = Client::orderBy('company_name')->get();
            // Par défaut, ne pas sélectionner de client
            $this->selected_client_id = null;
            $this->client = null;
        } else {
            // Pour les utilisateurs normaux, utiliser leur client
            $this->client = $this->user->client;
        }

        $this->activityRequestId = $activityRequestId;

        // Charger les demandes précédentes si un client est défini
        if ($this->client) {
            $this->previousActivityRequests = $this->client->activityRequests()
                ->where('status', '!=', 'draft')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $this->previousActivityRequests = collect();
        }

        // Si un ID est fourni, charger les données du brouillon
        if ($this->activityRequestId) {
            $this->loadDraft($this->activityRequestId);
        }
    }

    /**
     * Charger les données d'un brouillon existant
     */
    protected function loadDraft(int $activityRequestId): void
    {
        try {
            // Pour les admins, on ne filtre pas par client_id car ils peuvent éditer n'importe quel brouillon
            $query = ActivityRequest::where('id', $activityRequestId)
                ->where('status', 'draft');

            // Pour les utilisateurs normaux, on filtre par leur client
            if (! $this->user->isAdmin()) {
                $query->where('client_id', $this->client->id);
            }

            $activityRequest = $query->firstOrFail();

            // Pour les admins, charger le client du brouillon
            if ($this->user->isAdmin()) {
                $this->client = $activityRequest->client;
                $this->selected_client_id = $this->client->id;

                // Charger les demandes précédentes du client
                $this->previousActivityRequests = $this->client->activityRequests()
                    ->where('status', '!=', 'draft')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            // ⚠️ IMPORTANT : Conserver l'ID du brouillon pour les mises à jour ultérieures
            $this->activityRequestId = $activityRequestId;

            // Utiliser le Form Object pour charger les données
            $formData = new ActivityRequestFormData;
            $formData->fillFromActivityRequest($activityRequest);

            // Mapper les données vers les propriétés du composant
            $this->fillFromFormData($formData);

            // Récupérer les indicateurs de documents existants
            $documentsFlags = $formData->getExistingDocumentsFlags($activityRequest);
            $this->hasExistingAaoRequest = $documentsFlags['hasExistingAaoRequest'];
            $this->hasExistingKbis = $documentsFlags['hasExistingKbis'];
            $this->hasExistingTerm = $documentsFlags['hasExistingTerm'];
            $this->hasExistingSafetyReferent = $documentsFlags['hasExistingSafetyReferent'];
            $this->hasExistingCta = $documentsFlags['hasExistingCta'];

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du brouillon', [
                'error' => $e->getMessage(),
                'activity_request_id' => $activityRequestId,
                'user_id' => $this->user->id,
                'user_email' => $this->user->email,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->errorMessage = 'Erreur lors du chargement du brouillon.';
        }
    }

    /**
     * Écouter l'événement d'édition de brouillon
     */
    #[On('edit-draft')]
    public function handleEditDraft(int $activityRequestId): void
    {
        $this->loadDraft($activityRequestId);
        Flux::modal('new-activity-request')->show();
    }

    /**
     * Gérer le changement de client sélectionné (pour les admins)
     */
    public function updatedSelectedClientId($value)
    {
        if ($value === '' || $value === null) {
            $this->selected_client_id = null;
            $this->client = null;
            $this->previousActivityRequests = collect();
        } else {
            $this->selected_client_id = (int) $value;
            $this->client = \App\Models\Client::find($this->selected_client_id);

            // Charger les demandes précédentes du client sélectionné
            if ($this->client) {
                $this->previousActivityRequests = $this->client->activityRequests()
                    ->where('status', '!=', 'draft')
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $this->previousActivityRequests = collect();
            }
        }
    }

    /**
     * Gérer le changement d'état du renouvellement
     */
    public function updatedRenewal($value)
    {
        if (! $value) {
            $this->selectedPreviousActivityRequest = null;
            $this->resetFormFields();
        }
    }

    /**
     * Gérer le changement de la demande sélectionnée
     */
    public function updatedSelectedPreviousActivityRequest($value)
    {
        if ($value === '' || $value === null) {
            $this->selectedPreviousActivityRequest = null;
        } else {
            $this->selectedPreviousActivityRequest = (int) $value;
        }
    }

    /**
     * Réinitialiser uniquement les champs du formulaire (pas les messages)
     */
    protected function resetFormFields(): void
    {
        $this->reset([
            'manager_firstname',
            'manager_lastname',
            'manager_email',
            'manager_phone',
            'manager_role',
            'airport',
            'description',
            'customer_names',
            'person_count',
            'vehicule_count',
        ]);
    }

    /**
     * Créer la demande d'activité (soumission complète)
     */
    public function createActivityRequest()
    {
        $this->processActivityRequest(isDraft: false);
    }

    /**
     * Enregistrer en brouillon
     */
    public function saveDraft()
    {
        $this->processActivityRequest(isDraft: true);
    }

    /**
     * Traiter la demande d'activité (brouillon ou complète)
     * Logique simplifiée grâce aux nouveaux services
     */
    protected function processActivityRequest(bool $isDraft): void
    {
        try {
            // Vérifier qu'un client est sélectionné (pour les admins)
            if ($this->user->isAdmin() && ! $this->client) {
                $this->errorMessage = 'Veuillez sélectionner un client.';

                return;
            }

            // 1. Créer le Form Object à partir des données du composant
            $formData = $this->createFormData();

            // 2. Valider les données selon le contexte
            $isUpdate = ! is_null($this->activityRequestId);
            $existingDocs = $this->getExistingDocumentsArray();

            $validator = ActivityRequestFormValidator::validate(
                $formData,
                $isDraft,
                $isUpdate,
                $existingDocs
            );

            if ($validator->fails()) {
                $this->handleValidationErrors($validator, $isDraft, $isUpdate);

                return;
            }

            // 3. Gérer le renouvellement si nécessaire
            if ($formData->isRenewal()) {
                $formData = $this->handleRenewal($formData);
            }

            // 4. Créer le DTO
            $activityRequestData = CreateActivityRequestData::fromFormData(
                $formData,
                $this->client->id,
                $this->user->id,
                $isDraft
            );

            // 5. Exécuter l'action unifiée
            $action = app(SaveActivityRequestAction::class);
            $result = $action->execute(
                $activityRequestData,
                $this->client,
                $this->activityRequestId
            );

            // 6. Traiter le résultat
            if ($result->isSuccessful()) {

                $this->successMessage = $result->getMessage();
                
                // Envoyer une notification par email
                $email = $this->user->email;

                if ($this->client->notification_email) {
                    $email = $this->client->notification_email;
                }

                Mail::to($email)->send(new ActivityRequestCreated($result->getActivityRequest()));

            } else {
                $this->errorMessage = $result->getMessage();
            }

            $this->dispatch('activity-request-created');
            $this->closeModal();

        } catch (\Exception $e) {
            $this->handleException($e, $isDraft, ! is_null($this->activityRequestId));
        }
    }

    /**
     * Crée un FormData à partir des propriétés du composant
     */
    protected function createFormData(): ActivityRequestFormData
    {
        return ActivityRequestFormData::fromArray([
            'manager_firstname' => $this->manager_firstname,
            'manager_lastname' => $this->manager_lastname,
            'manager_email' => $this->manager_email,
            'manager_phone' => $this->manager_phone,
            'manager_role' => $this->manager_role,
            'airport' => $this->airport,
            'description' => $this->description,
            'customer_names' => $this->customer_names,
            'person_count' => $this->person_count,
            'vehicule_count' => $this->vehicule_count,
            'aao_request_document' => $this->aao_request_document,
            'kbis_document' => $this->kbis_document,
            'term_document' => $this->term_document,
            'safety_referent_document' => $this->safety_referent_document,
            'cta_document' => $this->cta_document,
            'renewal' => $this->renewal,
            'last_activity_request_id' => $this->selectedPreviousActivityRequest,
        ]);
    }

    /**
     * Remplit les propriétés du composant depuis un FormData
     */
    protected function fillFromFormData(ActivityRequestFormData $formData): void
    {
        $this->manager_firstname = $formData->manager_firstname;
        $this->manager_lastname = $formData->manager_lastname;
        $this->manager_email = $formData->manager_email;
        $this->manager_phone = $formData->manager_phone;
        $this->manager_role = $formData->manager_role;
        $this->airport = $formData->airport;
        $this->description = $formData->description;
        $this->customer_names = $formData->customer_names;
        $this->person_count = $formData->person_count;
        $this->vehicule_count = $formData->vehicule_count;
        $this->renewal = $formData->renewal;
        $this->selectedPreviousActivityRequest = $formData->last_activity_request_id;
    }

    /**
     * Gère le renouvellement en utilisant le service dédié
     */
    protected function handleRenewal(ActivityRequestFormData $formData): ActivityRequestFormData
    {
        $renewalService = app(ActivityRequestRenewalService::class);

        // Valider que la demande précédente appartient au client
        if (! $renewalService->validatePreviousRequest($formData->last_activity_request_id, $this->client->id)) {
            throw new \Exception('La demande précédente sélectionnée n\'est pas valide');
        }

        // Récupérer les données de la demande précédente
        $previousData = $renewalService->getPreviousRequestData($formData->last_activity_request_id);

        if (! $previousData) {
            throw new \Exception('Impossible de récupérer les données de la demande précédente');
        }

        // Créer un nouveau FormData avec les données de renouvellement
        $renewalFormData = ActivityRequestFormData::fromArray(array_merge(
            $previousData,
            [
                'renewal' => true,
                'last_activity_request_id' => $formData->last_activity_request_id,
            ]
        ));

        return $renewalFormData;
    }

    /**
     * Retourne les documents existants sous forme de tableau
     */
    protected function getExistingDocumentsArray(): array
    {
        return [
            'aao_request_document' => $this->hasExistingAaoRequest,
            'kbis_document' => $this->hasExistingKbis,
            'term_document' => $this->hasExistingTerm,
            'safety_referent_document' => $this->hasExistingSafetyReferent,
            'cta_document' => $this->hasExistingCta,
        ];
    }

    /**
     * Gère les erreurs de validation
     */
    protected function handleValidationErrors($validator, bool $isDraft, bool $isUpdate): void
    {
        $this->errorMessage = 'Erreurs de validation détectées.';

        // Logger les erreurs de validation pour faciliter le débogage
        Log::warning('Erreurs de validation lors de la soumission d\'une demande d\'activité', [
            'user_id' => $this->user->id,
            'client_id' => $this->client?->id,
            'activity_request_id' => $this->activityRequestId,
            'is_draft' => $isDraft,
            'is_update' => $isUpdate,
            'validation_errors' => $validator->errors()->messages(),
        ]);

        foreach ($validator->errors()->messages() as $field => $messages) {
            $this->addError($field, $messages[0]);
        }
    }

    /**
     * Gère les exceptions
     */
    protected function handleException(\Exception $e, bool $isDraft, bool $isUpdate): void
    {
        Log::error('Erreur lors du traitement de la demande d\'activité', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => $this->user->id,
            'is_draft' => $isDraft,
            'is_update' => $isUpdate,
        ]);

        $message = $this->getErrorMessage($isDraft, $isUpdate);
        $this->errorMessage = $message;
    }

    /**
     * Retourne le message d'erreur approprié
     */
    protected function getErrorMessage(bool $isDraft, bool $isUpdate): string
    {
        if ($isUpdate) {
            return $isDraft
                ? 'Une erreur est survenue lors de la mise à jour du brouillon. Veuillez réessayer.'
                : 'Une erreur est survenue lors de la mise à jour de la demande d\'activité. Veuillez réessayer.';
        }

        return $isDraft
            ? 'Une erreur est survenue lors de l\'enregistrement du brouillon. Veuillez réessayer.'
            : 'Une erreur est survenue lors de la création de la demande d\'activité. Veuillez réessayer.';
    }

    /**
     * Réinitialiser le formulaire
     */
    public function resetForm(): void
    {
        $this->reset([
            'activityRequestId',
            'manager_firstname',
            'manager_lastname',
            'manager_email',
            'manager_phone',
            'manager_role',
            'airport',
            'description',
            'customer_names',
            'person_count',
            'vehicule_count',
            'aao_request_document',
            'kbis_document',
            'term_document',
            'safety_referent_document',
            'cta_document',
            'errorMessage',
            'renewal',
            'selectedPreviousActivityRequest',
            'hasExistingAaoRequest',
            'hasExistingKbis',
            'hasExistingTerm',
            'hasExistingSafetyReferent',
            'hasExistingCta',
        ]);

        // Réinitialiser la sélection de client pour les admins
        if ($this->user->isAdmin()) {
            $this->selected_client_id = null;
            $this->client = null;
            $this->previousActivityRequests = collect();
        }
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
        Flux::modal('new-activity-request')->close();
    }

    public function render()
    {
        return view('livewire.activity-requests.create-activity-request-form');
    }
}
