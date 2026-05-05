<?php

namespace App\Livewire\BadgeRequests;

use App\Actions\BadgeRequest\SaveBadgeRequestAction;
use App\Actions\Coworker\CreateCoworkerAction;
use App\DataTransferObjects\CreateBadgeRequestData;
use App\DataTransferObjects\CreateCoworkerData;
use App\Forms\BadgeRequestFormData;
use App\Forms\BadgeRequestFormValidator;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Mail\BadgeRequest\BadgeRequestCreated;
use App\Models\ActivityRequest;
use App\Models\BadgeRequest;
use App\Models\Client;
use App\Models\Coworker;
use App\Validators\CoworkerValidator;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateBadgeRequestForm extends Component
{
    use InteractsWithToasts;
    use WithFileUploads;

    public $user;

    public $client;

    public $allClients; // Liste de tous les clients (pour les admins)

    public $selected_client_id; // ID du client sélectionné par l'admin

    public $activityRequests; // Liste des demandes d'activités (approved ou pending)

    public $selected_activity_request_id; // ID de la demande d'activité sélectionnée

    public $activityRequest; // Demande d'activité sélectionnée (pour affichage)

    // ID de la demande de badge à éditer (null = mode création)
    public ?int $badgeRequestId = null;

    // Propriétés pour la gestion des messages
    public $successMessage = '';

    public $errorMessage = '';

    public $coworkers; // Liste des collaborateurs

    public $selected_coworker_id; // ID du collaborateur sélectionné

    // Propriétés pour le formulaire - Documents
    public $selfie_photo;

    public $identification_card;

    public $activity_authorization;

    public $for_document;

    public $formation_certificate_document;

    public $invoice_document;

    // Propriétés pour le formulaire - Flags
    public $application_authorization = false;

    public $validate_training = false;

    // Indicateurs de documents existants (pour l'édition)
    public bool $hasExistingSelfiePhoto = false;

    public bool $hasExistingIdentificationCard = false;

    public bool $hasExistingActivityAuthorization = false;

    public bool $hasExistingForDocument = false;

    public bool $hasExistingFormationCertificate = false;

    public bool $hasExistingInvoice = false;

    // Propriétés pour la création rapide d'un collaborateur
    public bool $showCreateCoworkerForm = false;

    public $new_coworker_firstname;

    public $new_coworker_lastname;

    public $new_coworker_email;

    public $new_coworker_phone;

    public function mount(?int $badgeRequestId = null)
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

            // Charger les demandes d'activités approved ou pending
            $this->loadActivityRequests();
        }

        $this->badgeRequestId = $badgeRequestId;

        // Si un ID est fourni, charger les données du brouillon
        if ($this->badgeRequestId) {
            $this->loadDraft($this->badgeRequestId);
        }
    }

    public function downloadDocument(string $airport)
    {
        $fileName = "template-for-{$airport}.xlsx";
        $filePath = "templates/{$fileName}";

        // Vérifier si le fichier existe
        if (! Storage::disk('public')->exists($filePath)) {
            $this->errorMessage = "Le template pour {$airport} n'existe pas.";

            return;
        }

        $fullPath = Storage::disk('public')->path($filePath);

        return response()->download($fullPath, $fileName);
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
     * Charger les collaborateurs du client
     */
    public function loadCoworkers(): void
    {
        if ($this->client) {
            $this->coworkers = Coworker::where('client_id', $this->client->id)->get();
        } else {
            $this->coworkers = collect();
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
            $this->coworkers = collect();
        } else {
            $this->selected_client_id = (int) $value;
            $this->client = Client::find($this->selected_client_id);

            // Charger les demandes d'activités du client sélectionné
            $this->loadActivityRequests();

            // Réinitialiser la sélection de la demande d'activité
            $this->selected_activity_request_id = null;
            $this->coworkers = collect();
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
            $this->coworkers = collect();

            return;
        }

        // S'assurer que le client est défini
        if (! $this->client) {
            $this->selected_activity_request_id = null;
            $this->activityRequest = null;

            return;
        }

        $activityRequestId = (int) $value;

        // Réinitialiser d'abord pour éviter les problèmes de cache
        $this->activityRequest = null;
        $this->selected_activity_request_id = $activityRequestId;

        // Recharger l'ActivityRequest avec ses relations et s'assurer qu'elle appartient bien au bon client
        // Utiliser findOrFail pour garantir qu'on a bien l'ActivityRequest demandée
        try {
            $activityRequest = ActivityRequest::with('client')
                ->where('id', $activityRequestId)
                ->where('client_id', $this->client->id)
                ->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->errorMessage = 'La demande d\'activité sélectionnée n\'existe pas ou n\'appartient pas à ce client.';
            $this->selected_activity_request_id = null;
            $this->activityRequest = null;
            $this->coworkers = collect();

            return;
        }

        // Vérification de sécurité : s'assurer que l'ID correspond bien
        if ($activityRequest->id !== $activityRequestId) {
            Log::error('Incohérence détectée : ActivityRequest ID ne correspond pas', [
                'requested_id' => $activityRequestId,
                'found_id' => $activityRequest->id,
            ]);
            $this->errorMessage = 'Erreur lors du chargement de la demande d\'activité.';
            $this->selected_activity_request_id = null;
            $this->activityRequest = null;
            $this->coworkers = collect();

            return;
        }

        // Assigner l'ActivityRequest après toutes les vérifications
        // Forcer le rechargement complet pour éviter les problèmes de cache
        $this->activityRequest = ActivityRequest::with('client')
            ->where('id', $activityRequestId)
            ->where('client_id', $this->client->id)
            ->firstOrFail();

        // Log pour le débogage (à retirer après vérification)
        Log::debug('ActivityRequest chargée', [
            'selected_id' => $this->selected_activity_request_id,
            'activity_request_id' => $this->activityRequest->id,
            'person_count' => $this->activityRequest->person_count,
            'airport' => $this->activityRequest->airport,
        ]);

        $this->loadCoworkers();

        // Effacer les erreurs si tout s'est bien passé
        $this->errorMessage = '';
    }

    /**
     * Charger les données d'un brouillon existant
     */
    protected function loadDraft(int $badgeRequestId): void
    {
        try {
            // Pour les admins, on ne filtre pas par client_id car ils peuvent éditer n'importe quel brouillon
            $query = BadgeRequest::where('id', $badgeRequestId)
                ->where('status', 'draft');

            // Pour les utilisateurs normaux, on filtre via l'activity_request
            if (! $this->user->isAdmin()) {
                $query->whereHas('activityRequest', function ($q) {
                    $q->where('client_id', $this->client->id);
                });
            }

            $badgeRequest = $query->firstOrFail();

            // Pour les admins, charger le client du brouillon
            if ($this->user->isAdmin()) {
                $this->client = $badgeRequest->activityRequest->client;
                $this->selected_client_id = $this->client->id;

                // Charger les demandes d'activités du client
                $this->loadActivityRequests();
            }

            // ⚠️ IMPORTANT : Conserver l'ID du brouillon pour les mises à jour ultérieures
            $this->badgeRequestId = $badgeRequestId;

            // Utiliser le Form Object pour charger les données
            $formData = new BadgeRequestFormData;
            $formData->fillFromBadgeRequest($badgeRequest);

            // Mapper les données vers les propriétés du composant
            $this->fillFromFormData($formData);

            // Charger la demande d'activité et les collaborateurs
            $this->selected_activity_request_id = $badgeRequest->activity_request_id;
            $this->activityRequest = $badgeRequest->activityRequest;
            $this->loadCoworkers();

            // Charger le coworker sélectionné
            $this->selected_coworker_id = $badgeRequest->coworker_id;

            // Récupérer les indicateurs de documents existants
            $documentsFlags = $formData->getExistingDocumentsFlags($badgeRequest);
            $this->hasExistingSelfiePhoto = $documentsFlags['hasExistingSelfiePhoto'];
            $this->hasExistingIdentificationCard = $documentsFlags['hasExistingIdentificationCard'];
            $this->hasExistingActivityAuthorization = $documentsFlags['hasExistingActivityAuthorization'];
            $this->hasExistingForDocument = $documentsFlags['hasExistingForDocument'];
            $this->hasExistingFormationCertificate = $documentsFlags['hasExistingFormationCertificate'];
            $this->hasExistingInvoice = $documentsFlags['hasExistingInvoice'];

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du brouillon', [
                'error' => $e->getMessage(),
                'badge_request_id' => $badgeRequestId,
            ]);

            $this->errorMessage = 'Erreur lors du chargement du brouillon.';
        }
    }

    /**
     * Écouter l'événement d'édition de brouillon
     */
    #[On('edit-draft')]
    public function handleEditDraft(int $badgeRequestId): void
    {
        $this->loadDraft($badgeRequestId);
        Flux::modal('new-badge-request')->show();
    }

    /**
     * Créer la demande de badge (soumission complète)
     */
    public function createBadgeRequest()
    {
        $this->processBadgeRequest(isDraft: false);
    }

    /**
     * Enregistrer en brouillon
     */
    public function saveDraft()
    {
        $this->processBadgeRequest(isDraft: true);
    }

    /**
     * Traiter la demande de badge (brouillon ou complète)
     */
    protected function processBadgeRequest(bool $isDraft): void
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

            // Vérifier le quota de la demande d'activité (pour les demandes non-brouillons)
            if (! $isDraft) {
                $activityRequest = ActivityRequest::findOrFail($this->selected_activity_request_id);

                if (! $activityRequest->canCreateBadgeRequest()) {
                    $remaining = $activityRequest->getRemainingBadgeQuota();
                    $this->errorMessage = "Le quota de badges pour cette demande d'activité est atteint. Il reste {$remaining} place(s) disponible(s).";

                    return;
                }
            }

            // 1. Créer le Form Object à partir des données du composant
            $formData = $this->createFormData();

            // Vérifier qu'il n'existe pas déjà une demande de badge pour ce collaborateur
            // (uniquement pour les nouvelles créations, pas pour les brouillons ni les mises à jour)
            $isUpdate = ! is_null($this->badgeRequestId);
            if (! $isDraft && ! $isUpdate && $formData->coworker_id) {
                $existingBadgeRequest = BadgeRequest::where('coworker_id', $formData->coworker_id)
                    ->whereNotIn('status', ['rejected_rem', 'rejected_adp'])
                    ->first();

                if ($existingBadgeRequest) {
                    $this->errorMessage = 'Il existe déjà une demande de badge en cours pour ce collaborateur.';

                    return;
                }
            }

            // 2. Valider les données selon le contexte
            $existingDocs = $this->getExistingDocumentsArray();

            $validator = BadgeRequestFormValidator::validate(
                $formData,
                $isDraft,
                $isUpdate,
                $existingDocs
            );

            if ($validator->fails()) {
                $this->handleValidationErrors($validator, $isDraft, $isUpdate);

                return;
            }

            // 3. Créer le DTO
            $badgeRequestData = CreateBadgeRequestData::fromFormData(
                $formData,
                $this->client->id,
                $this->user->id,
                $isDraft
            );

            // 4. Exécuter l'action
            $action = app(SaveBadgeRequestAction::class);
            $result = $action->execute(
                $badgeRequestData,
                $this->client,
                $this->badgeRequestId
            );

            // 5. Traiter le résultat
            if ($result->isSuccessful()) {
                $this->successMessage = $result->getMessage();
                $this->toast(
                    $result->getMessage(),
                    'success',
                    $isDraft ? 'Brouillon enregistré' : 'Demande de badge créée',
                );
                $this->dispatch('badge-request-created');
                $this->closeModal();

                // Envoyer une notification par email
                $email = $this->user->email;
                if ($this->client->notification_email) {
                    $email = $this->client->notification_email;
                }

                Mail::to($email)->send(new BadgeRequestCreated($result->getBadgeRequest()));
            } else {
                $this->errorMessage = $result->getMessage();
                $this->toast($result->getMessage(), 'danger', 'Erreur');
            }

            $this->resetForm();

        } catch (\Exception $e) {
            $this->handleException($e, $isDraft, ! is_null($this->badgeRequestId));
        }
    }

    /**
     * Crée un FormData à partir des propriétés du composant
     */
    protected function createFormData(): BadgeRequestFormData
    {
        return BadgeRequestFormData::fromArray([
            'activity_request_id' => $this->selected_activity_request_id,
            'coworker_id' => $this->selected_coworker_id,
            'selfie_photo' => $this->selfie_photo,
            'identification_card' => $this->identification_card,
            'activity_authorization' => $this->activity_authorization,
            'for_document' => $this->for_document,
            'formation_certificate_document' => $this->formation_certificate_document,
            'invoice_document' => $this->invoice_document,
            'application_authorization' => $this->application_authorization,
            'validate_training' => $this->validate_training,
        ]);
    }

    /**
     * Remplit les propriétés du composant depuis un FormData
     */
    protected function fillFromFormData(BadgeRequestFormData $formData): void
    {
        $this->application_authorization = $formData->application_authorization;
        $this->validate_training = $formData->validate_training;
    }

    /**
     * Retourne les documents existants sous forme de tableau
     */
    protected function getExistingDocumentsArray(): array
    {
        return [
            'selfie_photo' => $this->hasExistingSelfiePhoto,
            'identification_card' => $this->hasExistingIdentificationCard,
            'activity_authorization' => $this->hasExistingActivityAuthorization,
            'for_document' => $this->hasExistingForDocument,
            'formation_certificate_document' => $this->hasExistingFormationCertificate,
            'invoice_document' => $this->hasExistingInvoice,
        ];
    }

    /**
     * Gère les erreurs de validation
     */
    protected function handleValidationErrors($validator, bool $isDraft, bool $isUpdate): void
    {
        $this->errorMessage = 'Erreurs de validation détectées.';

        // Logger les erreurs de validation pour faciliter le débogage
        Log::warning('Erreurs de validation lors de la soumission d\'une demande de badge', [
            'user_id' => $this->user->id,
            'client_id' => $this->client?->id,
            'badge_request_id' => $this->badgeRequestId,
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
        Log::error('Erreur lors du traitement de la demande de badge', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
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
                : 'Une erreur est survenue lors de la mise à jour de la demande de badge. Veuillez réessayer.';
        }

        return $isDraft
            ? 'Une erreur est survenue lors de l\'enregistrement du brouillon. Veuillez réessayer.'
            : 'Une erreur est survenue lors de la création de la demande de badge. Veuillez réessayer.';
    }

    /**
     * Réinitialiser le formulaire
     */
    public function resetForm(): void
    {
        $this->reset([
            'badgeRequestId',
            'selected_activity_request_id',
            'selected_coworker_id',
            'selfie_photo',
            'identification_card',
            'activity_authorization',
            'for_document',
            'formation_certificate_document',
            'invoice_document',
            'application_authorization',
            'validate_training',
            'errorMessage',
            'hasExistingSelfiePhoto',
            'hasExistingIdentificationCard',
            'hasExistingActivityAuthorization',
            'hasExistingForDocument',
            'hasExistingFormationCertificate',
            'hasExistingInvoice',
            'showCreateCoworkerForm',
            'new_coworker_firstname',
            'new_coworker_lastname',
            'new_coworker_email',
            'new_coworker_phone',
        ]);

        // Réinitialiser la sélection de client pour les admins
        if ($this->user->isAdmin()) {
            $this->selected_client_id = null;
            $this->client = null;
            $this->activityRequests = collect();
        }

        $this->activityRequest = null;
        $this->coworkers = collect();
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
        Flux::modal('new-badge-request')->close();
    }

    /**
     * Afficher/masquer le formulaire de création de collaborateur
     */
    public function toggleCreateCoworkerForm(): void
    {
        $this->showCreateCoworkerForm = ! $this->showCreateCoworkerForm;
        if (! $this->showCreateCoworkerForm) {
            $this->resetNewCoworkerForm();
        }
    }

    /**
     * Créer un nouveau collaborateur rapidement
     */
    public function createNewCoworker(): void
    {
        $this->clearErrorMessage();

        // Vérifier qu'un client est sélectionné
        if (! $this->client) {
            $this->errorMessage = 'Veuillez d\'abord sélectionner un client.';

            return;
        }

        // Validation
        $validationData = [
            'firstname' => $this->new_coworker_firstname,
            'lastname' => $this->new_coworker_lastname,
            'email' => $this->new_coworker_email,
            'phone' => $this->new_coworker_phone,
            'client_id' => $this->client->id,
            'has_leave' => false,
            'departure_date' => null,
            'create_user' => false,
        ];

        $validator = CoworkerValidator::validateCoworkerOnly($validationData);

        if ($validator->fails()) {
            $this->errorMessage = $validator->errors()->first();
            foreach ($validator->errors()->messages() as $field => $messages) {
                $this->addError("new_coworker_{$field}", $messages[0]);
            }

            return;
        }

        try {
            // Créer le DTO
            $data = CreateCoworkerData::fromArray(
                array_merge($validationData, ['created_by' => $this->user->id]),
                $this->user->id
            );

            // Exécuter l'action
            $action = new CreateCoworkerAction;
            $result = $action->execute($data);

            if ($result->isSuccessful()) {
                // Recharger la liste des collaborateurs
                $this->loadCoworkers();

                // Sélectionner automatiquement le nouveau collaborateur
                $this->selected_coworker_id = $result->coworker->id;

                // Réinitialiser le formulaire de création
                $this->resetNewCoworkerForm();
                $this->showCreateCoworkerForm = false;

                // Effacer les erreurs
                $this->clearErrorMessage();
            } else {
                $this->errorMessage = $result->getMessage();
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création rapide du collaborateur', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->user->id,
                'client_id' => $this->client->id,
            ]);

            $this->errorMessage = 'Une erreur est survenue lors de la création du collaborateur.';
        }
    }

    /**
     * Réinitialiser le formulaire de création de collaborateur
     */
    protected function resetNewCoworkerForm(): void
    {
        $this->new_coworker_firstname = '';
        $this->new_coworker_lastname = '';
        $this->new_coworker_email = '';
        $this->new_coworker_phone = '';
        $this->resetErrorBag([
            'new_coworker_firstname',
            'new_coworker_lastname',
            'new_coworker_email',
            'new_coworker_phone',
        ]);
    }

    /**
     * Computed property pour obtenir l'ActivityRequest actuellement sélectionnée
     * Recharge toujours depuis la base de données pour garantir la cohérence
     */
    public function getSelectedActivityRequestProperty(): ?ActivityRequest
    {
        if (! $this->selected_activity_request_id || ! $this->client) {
            return null;
        }

        // Toujours recharger depuis la base de données pour garantir la cohérence
        // et éviter les problèmes de cache Livewire
        $activityRequest = ActivityRequest::with('client')
            ->where('id', $this->selected_activity_request_id)
            ->where('client_id', $this->client->id)
            ->first();

        // Synchroniser la propriété avec la computed property
        if ($activityRequest && $activityRequest->id === $this->selected_activity_request_id) {
            $this->activityRequest = $activityRequest;
        } else {
            $this->activityRequest = null;
        }

        return $activityRequest;
    }

    public function render()
    {
        return view('livewire.badge-requests.create-badge-request-form');
    }
}
