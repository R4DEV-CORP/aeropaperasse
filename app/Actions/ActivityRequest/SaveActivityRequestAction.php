<?php

namespace App\Actions\ActivityRequest;

use App\DataTransferObjects\CreateActivityRequestData;
use App\Models\ActivityRequest;
use App\Models\Client;
use App\Services\ActivityRequestDocumentService;
use App\Services\ActivityRequestRenewalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action unifiée pour créer ou mettre à jour une demande d'activité
 * Remplace CreateActivityRequestAction et UpdateActivityRequestAction
 */
class SaveActivityRequestAction
{
    public function __construct(
        private ActivityRequestDocumentService $documentService,
        private ActivityRequestRenewalService $renewalService
    ) {}

    /**
     * Exécute la sauvegarde (création ou mise à jour) d'une demande d'activité
     *
     * @param  int|null  $activityRequestId  Si fourni, mise à jour; sinon création
     */
    public function execute(
        CreateActivityRequestData $data,
        Client $client,
        ?int $activityRequestId = null
    ): ActivityRequestResult {
        $isUpdate = ! is_null($activityRequestId);
        $operation = $this->determineOperation($isUpdate, $data->is_draft);

        try {
            return DB::transaction(function () use ($data, $client, $activityRequestId, $isUpdate, $operation) {

                // Créer ou récupérer la demande d'activité
                $activityRequest = $isUpdate
                    ? $this->getExistingRequest($activityRequestId, $client)
                    : $this->createNewRequest($data);

                // Mettre à jour les données de base
                if ($isUpdate) {
                    $activityRequest->update($data->getActivityRequestData());
                }

                // Gérer les documents
                $this->handleDocuments($data, $client, $activityRequest);

                // Log de succès
                $this->logOperationSuccess($operation, $activityRequest, $client, $data);

                $message = $this->getSuccessMessage($operation);

                return ActivityRequestResult::success($activityRequest, $message, $operation);
            });
        } catch (\Exception $e) {
            return $this->handleException($e, $operation, $client, $data, $activityRequestId);
        }
    }

    /**
     * Détermine le type d'opération effectuée
     */
    private function determineOperation(bool $isUpdate, bool $isDraft): string
    {
        if ($isDraft) {
            return $isUpdate ? 'update_draft' : 'create_draft';
        }

        return $isUpdate ? 'update' : 'create';
    }

    /**
     * Récupère une demande existante et valide les permissions
     */
    private function getExistingRequest(int $activityRequestId, Client $client): ActivityRequest
    {
        $activityRequest = ActivityRequest::findOrFail($activityRequestId);

        if ($activityRequest->client_id !== $client->id) {
            throw new \Exception('Cette demande n\'appartient pas à ce client');
        }

        return $activityRequest;
    }

    /**
     * Crée une nouvelle demande d'activité
     */
    private function createNewRequest(CreateActivityRequestData $data): ActivityRequest
    {
        $activityRequestData = $data->getActivityRequestData();

        $activityRequest = ActivityRequest::create($activityRequestData);

        return $activityRequest;
    }

    /**
     * Gère les documents (copie pour renouvellement ou stockage de nouveaux)
     */
    private function handleDocuments(
        CreateActivityRequestData $data,
        Client $client,
        ActivityRequest $activityRequest
    ): void {
        if ($data->renewal && $data->last_activity_request_id) {
            $this->handleRenewalDocuments($data, $client, $activityRequest);
        } elseif ($data->hasDocuments()) {
            $this->handleNewDocuments($data, $client, $activityRequest);
        }
    }

    /**
     * Gère les documents pour un renouvellement
     */
    private function handleRenewalDocuments(
        CreateActivityRequestData $data,
        Client $client,
        ActivityRequest $activityRequest
    ): void {
        $this->documentService->copyDocumentsFromPreviousRequest(
            $data->last_activity_request_id,
            $client,
            $activityRequest->id
        );
    }

    /**
     * Gère les nouveaux documents uploadés
     */
    private function handleNewDocuments(
        CreateActivityRequestData $data,
        Client $client,
        ActivityRequest $activityRequest
    ): void {
        $this->documentService->storeDocuments(
            $data->getDocuments(),
            $client,
            $activityRequest->id,
            $activityRequest // Passer l'ActivityRequest pour permettre la suppression des anciens documents
        );
    }

    /**
     * Retourne le message de succès approprié
     */
    private function getSuccessMessage(string $operation): string
    {
        return match ($operation) {
            'create' => 'Demande d\'activité créée avec succès',
            'update' => 'Demande d\'activité mise à jour et soumise avec succès',
            'create_draft' => 'Brouillon enregistré avec succès',
            'update_draft' => 'Brouillon mis à jour avec succès',
            default => 'Opération effectuée avec succès',
        };
    }

    /**
     * Log le succès de l'opération
     */
    private function logOperationSuccess(
        string $operation,
        ActivityRequest $activityRequest,
        Client $client,
        CreateActivityRequestData $data
    ): void {
        Log::info("Demande d'activité traitée avec succès [{$operation}]", [
            'activity_request_id' => $activityRequest->id,
            'client_id' => $client->id,
            'company_name' => $client->company_name,
            'status' => $activityRequest->status,
            'is_draft' => $data->is_draft,
            'is_renewal' => $data->renewal,
            'previous_request_id' => $data->last_activity_request_id,
        ]);
    }

    /**
     * Gère les exceptions et retourne un résultat d'échec
     */
    private function handleException(
        \Exception $e,
        string $operation,
        Client $client,
        CreateActivityRequestData $data,
        ?int $activityRequestId
    ): ActivityRequestResult {
        Log::error("Erreur lors de l'opération [{$operation}]", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->user()->id,
            'activity_request_id' => $activityRequestId,
            'client_id' => $client->id,
            'is_draft' => $data->is_draft,
        ]);

        $message = $this->getErrorMessage($operation, $e);

        return ActivityRequestResult::failure($message, $operation);
    }

    /**
     * Retourne le message d'erreur approprié
     */
    private function getErrorMessage(string $operation, \Exception $e): string
    {
        $baseMessage = match ($operation) {
            'create' => 'Erreur lors de la création de la demande d\'activité',
            'update' => 'Erreur lors de la mise à jour de la demande d\'activité',
            'create_draft' => 'Erreur lors de l\'enregistrement du brouillon',
            'update_draft' => 'Erreur lors de la mise à jour du brouillon',
            default => 'Erreur lors de l\'opération',
        };

        return $baseMessage.' : '.$e->getMessage();
    }
}
