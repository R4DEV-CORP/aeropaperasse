<?php

namespace App\Actions\ActivityRequest;

use App\DataTransferObjects\CreateActivityRequestData;
use App\Models\ActivityRequest;
use App\Models\Client;
use App\Services\ActivityRequestDocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateActivityRequestAction
{
    /*
    * ⚠️ IMPORTANT ⚠️
    * Il y a une deuxième classe à la fin du fichier qui sert à retourner les résultats de l'action
    */

    public function __construct(
        private ActivityRequestDocumentService $documentService
    ) {}

    /**
     * Execute la création d'une demande d'activité
     */
    public function execute(CreateActivityRequestData $data, Client $client): CreateActivityRequestResult
    {
        try {
            return DB::transaction(function () use ($data, $client) {
                Log::info('Action CreateActivityRequest - Début', [
                    'renewal' => $data->renewal,
                    'last_activity_request_id' => $data->last_activity_request_id,
                    'has_documents' => $data->hasDocuments(),
                    'is_draft' => $data->is_draft,
                ]);
                
                // 1. Créer la demande d'activité avec les données de base
                $activityRequestData = $data->getActivityRequestData();
                Log::info('Données pour création', ['data' => $activityRequestData]);
                
                $activityRequest = ActivityRequest::create($activityRequestData);
                Log::info('Demande créée avec ID', ['id' => $activityRequest->id]);

                // 2. Gérer les documents
                if ($data->renewal && $data->last_activity_request_id) {
                    Log::info('Copie des documents de la demande précédente', [
                        'previous_id' => $data->last_activity_request_id,
                        'new_id' => $activityRequest->id,
                    ]);
                    
                    // Si c'est un renouvellement, copier les documents de l'ancienne demande
                    $copiedDocuments = $this->documentService->copyDocumentsFromPreviousRequest(
                        $data->last_activity_request_id,
                        $client,
                        $activityRequest->id
                    );
                    
                    Log::info('Documents copiés', ['count' => count($copiedDocuments), 'documents' => array_keys($copiedDocuments)]);
                    
                    // Mettre à jour la demande avec les chemins des documents copiés
                    if (!empty($copiedDocuments)) {
                        $activityRequest->update($copiedDocuments);
                        Log::info('Demande mise à jour avec les documents copiés');
                    }
                } elseif ($data->hasDocuments()) {
                    Log::info('Enregistrement de nouveaux documents', ['count' => count($data->getDocuments())]);
                    
                    // Sinon, stocker les nouveaux documents s'il y en a
                    $storedDocuments = $this->documentService->storeDocuments(
                        $data->getDocuments(), 
                        $client, 
                        $activityRequest->id
                    );
                    
                    // 3. Mettre à jour la demande avec les chemins des documents
                    $activityRequest->update($storedDocuments);
                    Log::info('Demande mise à jour avec les nouveaux documents');
                }

                // 4. Log de la création
                Log::info('Demande d\'activité créée avec succès', [
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
                    ? 'Brouillon enregistré avec succès' 
                    : 'Demande d\'activité créée avec succès';

                return new CreateActivityRequestResult(
                    success: true,
                    activityRequest: $activityRequest,
                    message: $message
                );
            });
        } catch (\Exception $e) {
            // Log de l'erreur
            Log::error('Erreur lors de la création de la demande d\'activité', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'client_id' => $client->id,
                'is_draft' => $data->is_draft,
            ]);

            $message = $data->is_draft 
                ? 'Erreur lors de l\'enregistrement du brouillon : ' . $e->getMessage()
                : 'Erreur lors de la création de la demande d\'activité : ' . $e->getMessage();

            return new CreateActivityRequestResult(
                success: false,
                activityRequest: null,
                message: $message
            );
        }
    }
}

/**
 * Classe de résultat pour la création d'une demande d'activité
 */
class CreateActivityRequestResult
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
