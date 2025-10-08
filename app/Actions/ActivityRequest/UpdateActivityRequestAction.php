<?php

namespace App\Actions\ActivityRequest;

use App\DataTransferObjects\CreateActivityRequestData;
use App\Models\ActivityRequest;
use App\Models\Client;
use App\Services\ActivityRequestDocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateActivityRequestAction
{
    /*
    * ⚠️ IMPORTANT ⚠️
    * Il y a une deuxième classe à la fin du fichier qui sert à retourner les résultats de l'action
    */

    public function __construct(
        private ActivityRequestDocumentService $documentService
    ) {}

    /**
     * Execute la mise à jour d'une demande d'activité (brouillon)
     */
    public function execute(CreateActivityRequestData $data, Client $client, int $activityRequestId): UpdateActivityRequestResult
    {
        try {
            return DB::transaction(function () use ($data, $client, $activityRequestId) {
                Log::info('Action UpdateActivityRequest - Début', [
                    'activity_request_id' => $activityRequestId,
                    'renewal' => $data->renewal,
                    'last_activity_request_id' => $data->last_activity_request_id,
                    'has_documents' => $data->hasDocuments(),
                    'is_draft' => $data->is_draft,
                    'client_id' => $client->id,
                    'creator' => $data->created_by,
                ]);
                
                // 1. Récupérer la demande d'activité existante
                $activityRequest = ActivityRequest::findOrFail($activityRequestId);
                
                // Vérifier que c'est bien un brouillon du bon client
                if ($activityRequest->client_id !== $client->id) {
                    throw new \Exception('Cette demande n\'appartient pas à ce client');
                }

                // 2. Mettre à jour la demande d'activité avec les données de base
                $activityRequestData = $data->getActivityRequestData();
                $activityRequest->update($activityRequestData);

                // 3. Gérer les documents
                if ($data->renewal && $data->last_activity_request_id) {
                    // Si c'est un renouvellement, copier les documents de l'ancienne demande
                    $copiedDocuments = $this->documentService->copyDocumentsFromPreviousRequest(
                        $data->last_activity_request_id,
                        $client,
                        $activityRequest->id
                    );
                    
                    // Mettre à jour la demande avec les chemins des documents copiés
                    if (!empty($copiedDocuments)) {
                        $activityRequest->update($copiedDocuments);
                    }
                } elseif ($data->hasDocuments()) {
                    // Sinon, stocker les nouveaux documents s'il y en a
                    $storedDocuments = $this->documentService->storeDocuments(
                        $data->getDocuments(), 
                        $client, 
                        $activityRequest->id
                    );
                    
                    // Mettre à jour la demande avec les chemins des documents
                    $activityRequest->update($storedDocuments);
                }

                // 4. Log de la mise à jour
                Log::info('Demande d\'activité mise à jour avec succès', [
                    'activity_request_id' => $activityRequest->id,
                    'client_id' => $client->id,
                    'company_name' => $client->company_name,
                    'status' => $activityRequest->status,
                    'is_draft' => $data->is_draft,
                    'is_renewal' => $data->renewal,
                    'previous_request_id' => $data->last_activity_request_id,
                    'documents_count' => $data->hasDocuments() ? count($data->getDocuments()) : 0,
                ]);

                $message = $data->is_draft 
                    ? 'Brouillon mis à jour avec succès' 
                    : 'Demande d\'activité mise à jour et soumise avec succès';

                return new UpdateActivityRequestResult(
                    success: true,
                    activityRequest: $activityRequest,
                    message: $message
                );
            });
        } catch (\Exception $e) {
            // Log de l'erreur
            Log::error('Erreur lors de la mise à jour de la demande d\'activité', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'activity_request_id' => $activityRequestId,
                'client_id' => $client->id,
                'is_draft' => $data->is_draft,
            ]);

            $message = $data->is_draft 
                ? 'Erreur lors de la mise à jour du brouillon : ' . $e->getMessage()
                : 'Erreur lors de la mise à jour de la demande d\'activité : ' . $e->getMessage();

            return new UpdateActivityRequestResult(
                success: false,
                activityRequest: null,
                message: $message
            );
        }
    }
}

/**
 * Classe de résultat pour la mise à jour d'une demande d'activité
 */
class UpdateActivityRequestResult
{
    public function __construct(
        public bool $success,
        public ?ActivityRequest $activityRequest,
        public string $message
    ) {}

    /**
     * Check si l'action a été réussie
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Retourne le message de l'action
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Retourne des informations supplémentaires pour la réponse
     */
    public function getData(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'activity_request_id' => $this->activityRequest?->id,
            'status' => $this->activityRequest?->status,
        ];
    }
}
