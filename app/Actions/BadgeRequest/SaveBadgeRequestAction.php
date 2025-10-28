<?php

namespace App\Actions\BadgeRequest;

use App\DataTransferObjects\CreateBadgeRequestData;
use App\Models\BadgeRequest;
use App\Models\Client;
use App\Services\BadgeRequestDocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action unifiée pour créer ou mettre à jour une demande de badge
 */
class SaveBadgeRequestAction
{
    public function __construct(
        private BadgeRequestDocumentService $documentService
    ) {}

    /**
     * Exécute la sauvegarde (création ou mise à jour) d'une demande de badge
     *
     * @param  int|null  $badgeRequestId  Si fourni, mise à jour; sinon création
     */
    public function execute(
        CreateBadgeRequestData $data,
        Client $client,
        ?int $badgeRequestId = null
    ): BadgeRequestResult {
        $isUpdate = ! is_null($badgeRequestId);
        $operation = $this->determineOperation($isUpdate, $data->is_draft);

        try {
            return DB::transaction(function () use ($data, $client, $badgeRequestId, $isUpdate, $operation) {

                // Créer ou récupérer la demande de badge
                $badgeRequest = $isUpdate
                    ? $this->getExistingRequest($badgeRequestId, $client)
                    : $this->createNewRequest($data);

                // Mettre à jour les données de base
                if ($isUpdate) {
                    $badgeRequest->update($data->getBadgeRequestData());
                }

                // Gérer les documents
                $this->handleDocuments($data, $client, $badgeRequest);

                // Log de succès
                $this->logOperationSuccess($operation, $badgeRequest, $client, $data);

                $message = $this->getSuccessMessage($operation);

                return BadgeRequestResult::success($badgeRequest, $message, $operation);
            });
        } catch (\Exception $e) {
            return $this->handleException($e, $operation, $client, $data, $badgeRequestId);
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
    private function getExistingRequest(int $badgeRequestId, Client $client): BadgeRequest
    {
        $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);

        // Vérifier que la demande appartient au même client via l'activity_request
        if ($badgeRequest->activityRequest->client_id !== $client->id) {
            throw new \Exception('Cette demande n\'appartient pas à ce client');
        }

        return $badgeRequest;
    }

    /**
     * Crée une nouvelle demande de badge
     */
    private function createNewRequest(CreateBadgeRequestData $data): BadgeRequest
    {
        $badgeRequestData = $data->getBadgeRequestData();

        $badgeRequest = BadgeRequest::create($badgeRequestData);

        return $badgeRequest;
    }

    /**
     * Gère les documents (stockage de nouveaux)
     */
    private function handleDocuments(
        CreateBadgeRequestData $data,
        Client $client,
        BadgeRequest $badgeRequest
    ): void {
        if ($data->hasDocuments()) {
            $this->handleNewDocuments($data, $client, $badgeRequest);
        }
    }

    /**
     * Gère les nouveaux documents uploadés
     */
    private function handleNewDocuments(
        CreateBadgeRequestData $data,
        Client $client,
        BadgeRequest $badgeRequest
    ): void {
        $storedDocuments = $this->documentService->storeDocuments(
            $data->getDocuments(),
            $client,
            $badgeRequest->id
        );

        $badgeRequest->update($storedDocuments);
    }

    /**
     * Retourne le message de succès approprié
     */
    private function getSuccessMessage(string $operation): string
    {
        return match ($operation) {
            'create' => 'Demande de badge créée avec succès',
            'update' => 'Demande de badge mise à jour et soumise avec succès',
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
        BadgeRequest $badgeRequest,
        Client $client,
        CreateBadgeRequestData $data
    ): void {
        Log::info("Demande de badge traitée avec succès [{$operation}]", [
            'badge_request_id' => $badgeRequest->id,
            'client_id' => $client->id,
            'company_name' => $client->company_name,
            'status' => $badgeRequest->status,
            'is_draft' => $data->is_draft,
            'activity_request_id' => $data->activity_request_id,
            'coworker_id' => $data->coworker_id,
        ]);
    }

    /**
     * Gère les exceptions et retourne un résultat d'échec
     */
    private function handleException(
        \Exception $e,
        string $operation,
        Client $client,
        CreateBadgeRequestData $data,
        ?int $badgeRequestId
    ): BadgeRequestResult {
        Log::error("Erreur lors de l'opération [{$operation}]", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'Créateur de la demande' => auth()->user()->name.' (ID: '.auth()->user()->id.')',
            'badge_request_id' => $badgeRequestId,
            'client_id' => $client->id,
            'is_draft' => $data->is_draft,
        ]);

        $message = $this->getErrorMessage($operation, $e);

        return BadgeRequestResult::failure($message, $operation);
    }

    /**
     * Retourne le message d'erreur approprié
     */
    private function getErrorMessage(string $operation, \Exception $e): string
    {
        $baseMessage = match ($operation) {
            'create' => 'Erreur lors de la création de la demande de badge',
            'update' => 'Erreur lors de la mise à jour de la demande de badge',
            'create_draft' => 'Erreur lors de l\'enregistrement du brouillon',
            'update_draft' => 'Erreur lors de la mise à jour du brouillon',
            default => 'Erreur lors de l\'opération',
        };

        return $baseMessage.' : '.$e->getMessage();
    }
}
