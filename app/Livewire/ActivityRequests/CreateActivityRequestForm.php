<?php

namespace App\Livewire\ActivityRequests;

use App\Actions\ActivityRequest\CreateActivityRequestAction;
use App\Actions\ActivityRequest\UpdateActivityRequestAction;
use App\DataTransferObjects\CreateActivityRequestData;
use App\Models\ActivityRequest;
use App\Validators\ActivityRequestValidator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Flux\Flux;
use Illuminate\Support\Facades\Log;

class CreateActivityRequestForm extends Component
{
    use WithFileUploads;

    public $user;
    public $client;

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
    public $customer_certificate_document;
    public $prefectural_agreement_document;
    public $iata_contract_document;
    public $cta_document;
    
    // Indicateurs de documents existants (pour l'édition)
    public bool $hasExistingCustomerCertificate = false;
    public bool $hasExistingPrefecturalAgreement = false;
    public bool $hasExistingIataContract = false;
    public bool $hasExistingCta = false;

    // Propriétés pour la gestion des messages
    public $successMessage = '';
    public $errorMessage = '';

    public function mount(?int $activityRequestId = null)
    {
        $this->user = auth()->user();
        $this->client = $this->user->client;
        $this->activityRequestId = $activityRequestId;
        
        $this->previousActivityRequests = $this->client->activityRequests()
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();
        
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
            $activityRequest = ActivityRequest::where('id', $activityRequestId)
                ->where('client_id', $this->client->id)
                ->where('status', 'draft')
                ->firstOrFail();
            
            // ⚠️ IMPORTANT : Définir l'ID pour indiquer qu'on est en mode édition
            $this->activityRequestId = $activityRequestId;
            
            // Charger les données dans les propriétés du formulaire
            $this->manager_firstname = $activityRequest->manager_firstname;
            $this->manager_lastname = $activityRequest->manager_lastname;
            $this->manager_email = $activityRequest->manager_email;
            $this->manager_phone = $activityRequest->manager_phone;
            $this->manager_role = $activityRequest->manager_role;
            $this->airport = $activityRequest->airport;
            $this->description = $activityRequest->description;
            $this->customer_names = $activityRequest->customer_names;
            $this->person_count = $activityRequest->person_count;
            $this->vehicule_count = $activityRequest->vehicule_count;
            $this->renewal = $activityRequest->renewal;
            $this->selectedPreviousActivityRequest = $activityRequest->last_activity_request_id;
            
            // Vérifier la présence de documents existants
            $this->hasExistingCustomerCertificate = !empty($activityRequest->customer_certificate_document);
            $this->hasExistingPrefecturalAgreement = !empty($activityRequest->prefectural_agreement_document);
            $this->hasExistingIataContract = !empty($activityRequest->iata_contract_document);
            $this->hasExistingCta = !empty($activityRequest->cta_document);
            
            // Note : Les documents ne sont pas rechargés car Livewire ne peut pas 
            // pré-remplir les champs de fichiers pour des raisons de sécurité
            
            Log::info('Brouillon chargé avec succès', [
                'activity_request_id' => $activityRequestId,
                'client_id' => $this->client->id,
                'has_documents' => $this->hasExistingCustomerCertificate || $this->hasExistingPrefecturalAgreement || $this->hasExistingIataContract || $this->hasExistingCta,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du brouillon', [
                'error' => $e->getMessage(),
                'activity_request_id' => $activityRequestId,
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
        // Charger le brouillon
        $this->loadDraft($activityRequestId);
        
        // Ouvrir la modale
        Flux::modal('new-activity-request')->show();
    }

    /**
     * Gérer le changement d'état du renouvellement
     */
    public function updatedRenewal($value)
    {
        // Si on décoche le renouvellement, réinitialiser la sélection
        if (!$value) {
            $this->selectedPreviousActivityRequest = null;
            $this->resetFormFields();
        }
    }
    
    /**
     * Gérer le changement de la demande sélectionnée
     */
    public function updatedSelectedPreviousActivityRequest($value)
    {
        // Nettoyer la valeur si c'est une chaîne vide
        if ($value === '' || $value === null) {
            $this->selectedPreviousActivityRequest = null;
        } else {
            // Forcer la conversion en integer
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
        $this->processActivityRequest(false);
    }

    /**
     * Enregistrer en brouillon
     */
    public function saveDraft()
    {
        $this->processActivityRequest(true);
    }

    /**
     * Traiter la demande d'activité (brouillon ou complète)
     */
    protected function processActivityRequest(bool $isDraft)
    {
        try {
            // 1. Récupérer les données du formulaire
            $formData = $this->getFormData();
            
            // 2. Valider les données selon le type (brouillon ou complet) et le mode (création ou édition)
            if ($this->activityRequestId && !$isDraft) {
                // Mode édition avec soumission complète : utiliser validateUpdate
                $existingDocs = [
                    'customer_certificate_document' => $this->hasExistingCustomerCertificate,
                    'prefectural_agreement_document' => $this->hasExistingPrefecturalAgreement,
                    'iata_contract_document' => $this->hasExistingIataContract,
                    'cta_document' => $this->hasExistingCta,
                ];
                $validator = ActivityRequestValidator::validateUpdate($formData, $this->renewal, $existingDocs);
            } elseif ($isDraft) {
                // Mode brouillon (création ou édition)
                $validator = ActivityRequestValidator::validateDraft($formData, $this->renewal);
            } else {
                // Mode création complète
                $validator = ActivityRequestValidator::validateComplete($formData, $this->renewal);
            }
            
            if ($validator->fails()) {
                $this->errorMessage = 'Erreurs de validation détectées.';
                Log::warning('Validation échouée', [
                    'errors' => $validator->errors()->toArray(),
                    'is_draft' => $isDraft,
                    'renewal' => $this->renewal,
                    'is_update' => !is_null($this->activityRequestId),
                ]);
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $this->addError($field, $messages[0]);
                }
                return;
            }
            
            // 3. Créer le DTO avec les données validées
            $activityRequestData = CreateActivityRequestData::fromArray(
                $formData, 
                $this->client->id, 
                $this->user->id,
                $isDraft
            );
            
            // 4. Exécuter l'action appropriée (création ou mise à jour)
            if ($this->activityRequestId) {
                // Mode édition : mise à jour
                $action = app(UpdateActivityRequestAction::class);
                $result = $action->execute($activityRequestData, $this->client, $this->activityRequestId);
            } else {
                // Mode création
                $action = app(CreateActivityRequestAction::class);
                $result = $action->execute($activityRequestData, $this->client);
            }
            
            if ($result->isSuccessful()) {
                $this->successMessage = $result->getMessage();
                $this->dispatch('activity-request-created');
                $this->resetForm();
                $this->closeModal();
            } else {
                $this->errorMessage = $result->getMessage();
            }
            
        } catch (\Exception $e) {
            // Logger l'erreur complète pour le debug
            Log::error('Erreur lors du traitement de la demande d\'activité', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->user->id,
                'client_id' => $this->client->id,
                'is_draft' => $isDraft,
                'is_update' => !is_null($this->activityRequestId),
            ]);
            
            // Afficher un message générique à l'utilisateur
            $isUpdate = !is_null($this->activityRequestId);
            
            if ($isUpdate) {
                $message = $isDraft 
                    ? 'Une erreur est survenue lors de la mise à jour du brouillon. Veuillez réessayer.'
                    : 'Une erreur est survenue lors de la mise à jour de la demande d\'activité. Veuillez réessayer.';
            } else {
                $message = $isDraft 
                    ? 'Une erreur est survenue lors de l\'enregistrement du brouillon. Veuillez réessayer.'
                    : 'Une erreur est survenue lors de la création de la demande d\'activité. Veuillez réessayer.';
            }
            
            $this->errorMessage = $message;
        }
    }

    /**
     * Récupérer toutes les données du formulaire
     */
    protected function getFormData(): array
    {
        // Conversion explicite : si chaîne vide, convertir en null
        $lastActivityRequestId = $this->selectedPreviousActivityRequest;
        if ($lastActivityRequestId === '' || $lastActivityRequestId === null) {
            $lastActivityRequestId = null;
        } else {
            $lastActivityRequestId = (int) $lastActivityRequestId;
        }
        
        return [
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
            'customer_certificate_document' => $this->customer_certificate_document,
            'prefectural_agreement_document' => $this->prefectural_agreement_document,
            'iata_contract_document' => $this->iata_contract_document,
            'cta_document' => $this->cta_document,
            'renewal' => $this->renewal,
            'last_activity_request_id' => $lastActivityRequestId,
        ];
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
            'customer_certificate_document',
            'prefectural_agreement_document',
            'iata_contract_document',
            'cta_document',
            'errorMessage',
            'renewal',
            'selectedPreviousActivityRequest',
            'hasExistingCustomerCertificate',
            'hasExistingPrefecturalAgreement',
            'hasExistingIataContract',
            'hasExistingCta',
        ]);
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
