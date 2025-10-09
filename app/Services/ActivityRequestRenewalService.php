<?php

namespace App\Services;

use App\Models\ActivityRequest;
use Illuminate\Support\Facades\Log;

/**
 * Service dédié à la gestion de la logique de renouvellement des demandes d'activité
 */
class ActivityRequestRenewalService
{
    /**
     * Récupère les données d'une demande précédente pour un renouvellement
     * 
     * @param int $previousActivityRequestId
     * @return array|null Retourne les données de la demande ou null si non trouvée
     */
    public function getPreviousRequestData(int $previousActivityRequestId): ?array
    {
        $previousRequest = ActivityRequest::find($previousActivityRequestId);
        
        if (!$previousRequest) {
            Log::warning('Demande d\'activité précédente non trouvée pour le renouvellement', [
                'previous_id' => $previousActivityRequestId,
            ]);
            return null;
        }
        
        // Retourner toutes les données nécessaires pour le renouvellement
        return [
            'manager_firstname' => $previousRequest->manager_firstname,
            'manager_lastname' => $previousRequest->manager_lastname,
            'manager_email' => $previousRequest->manager_email,
            'manager_phone' => $previousRequest->manager_phone,
            'manager_role' => $previousRequest->manager_role,
            'airport' => $previousRequest->airport,
            'description' => $previousRequest->description,
            'customer_names' => $previousRequest->customer_names,
            'person_count' => $previousRequest->person_count,
            'vehicule_count' => $previousRequest->vehicule_count,
        ];
    }
    
    /**
     * Vérifie si une demande précédente existe et appartient au client
     * 
     * @param int $previousActivityRequestId
     * @param int $clientId
     * @return bool
     */
    public function validatePreviousRequest(int $previousActivityRequestId, int $clientId): bool
    {
        $previousRequest = ActivityRequest::find($previousActivityRequestId);
        
        if (!$previousRequest) {
            return false;
        }
        
        if ($previousRequest->client_id !== $clientId) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Prépare les données pour une nouvelle demande basée sur un renouvellement
     * 
     * @param int $previousActivityRequestId
     * @param int $clientId
     * @param int $userId
     * @param bool $isDraft
     * @return array
     */
    public function prepareRenewalData(
        int $previousActivityRequestId,
        int $clientId,
        int $userId,
        bool $isDraft
    ): array {
        $previousData = $this->getPreviousRequestData($previousActivityRequestId);
        
        if (!$previousData) {
            throw new \Exception('Impossible de récupérer les données de la demande précédente');
        }
        
        // Ajouter les métadonnées nécessaires
        $previousData['client_id'] = $clientId;
        $previousData['created_by'] = $userId;
        $previousData['renewal'] = true;
        $previousData['last_activity_request_id'] = $previousActivityRequestId;
        
        // Définir le statut
        if ($isDraft) {
            $previousData['status'] = 'draft';
            $previousData['draft_at'] = now();
        } else {
            $previousData['status'] = 'pending';
            $previousData['pending_at'] = now();
        }
        
        return $previousData;
    }
}

