<?php

namespace App\Actions\BadgeRequest;

use App\Models\BadgeRequest;
use App\Models\Client;
use App\Services\BadgeRequestDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action pour marquer une demande de badge comme remise
 */
class DeliverBadgeRequestAction
{
    public function __construct(
        private BadgeRequestDocumentService $documentService
    ) {}

    /**
     * Exécute la remise d'un badge avec la photo de preuve
     */
    public function execute(
        BadgeRequest $badgeRequest,
        UploadedFile $deliveryPhoto,
        Client $client
    ): BadgeRequestResult {
        try {
            return DB::transaction(function () use ($badgeRequest, $deliveryPhoto, $client) {
                // Stocker la photo de remise
                $photoPath = $this->storeDeliveryPhoto($deliveryPhoto, $client, $badgeRequest->id);

                // Mettre à jour le statut de la demande
                $badgeRequest->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                    'delivery_photo' => $photoPath,
                ]);

                // Log de succès
                Log::info('Badge marqué comme remis', [
                    'badge_request_id' => $badgeRequest->id,
                    'client_id' => $client->id,
                    'company_name' => $client->company_name,
                    'delivery_photo' => $photoPath,
                ]);

                return BadgeRequestResult::success(
                    $badgeRequest->fresh(),
                    'Badge marqué comme remis avec succès',
                    'deliver'
                );
            });
        } catch (\Exception $e) {
            return $this->handleException($e, $badgeRequest, $client);
        }
    }

    /**
     * Stocke la photo de remise dans le dossier du client
     */
    private function storeDeliveryPhoto(
        UploadedFile $file,
        Client $client,
        int $badgeRequestId
    ): string {
        // Générer le nom du dossier client
        $clientFolderName = $this->generateClientFolderName($client->company_name);

        // Générer un nom de fichier
        $filename = $this->generateFilename($file, $clientFolderName);

        // Définir le chemin de stockage : storage/public/clients/[nom-client]/documents/badge-requests/[id]/
        $path = "clients/{$clientFolderName}/documents/badge-requests/{$badgeRequestId}";

        // Stocker le fichier
        $storedPath = $file->storeAs($path, $filename, 'public');

        if (! $storedPath) {
            throw new \Exception('Erreur lors du stockage de la photo de remise');
        }

        return $storedPath;
    }

    /**
     * Générer le nom du dossier client (même logique que BadgeRequestDocumentService)
     */
    private function generateClientFolderName(string $companyName): string
    {
        // Convertir en minuscules
        $folderName = strtolower($companyName);

        // Remplacer les espaces par des tirets
        $folderName = str_replace(' ', '-', $folderName);

        // Supprimer les caractères spéciaux (garder seulement lettres, chiffres et tirets)
        $folderName = preg_replace('/[^a-z0-9\-]/', '', $folderName);

        // Supprimer les tirets multiples consécutifs
        $folderName = preg_replace('/-+/', '-', $folderName);

        // Supprimer les tirets en début et fin
        $folderName = trim($folderName, '-');

        return $folderName;
    }

    /**
     * Génère un nom de fichier pour la photo de remise
     */
    private function generateFilename(UploadedFile $file, string $clientFolderName): string
    {
        $timestamp = now()->timestamp;
        $extension = $file->getClientOriginalExtension();

        // Format : delivery-photo-[nom-entreprise]-[timestamp].extension
        return "delivery-photo-{$clientFolderName}-{$timestamp}.{$extension}";
    }

    /**
     * Gère les exceptions et retourne un résultat d'échec
     */
    private function handleException(
        \Exception $e,
        BadgeRequest $badgeRequest,
        Client $client
    ): BadgeRequestResult {
        Log::error('Erreur lors de la remise du badge', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'badge_request_id' => $badgeRequest->id,
            'client_id' => $client->id,
            'company_name' => $client->company_name,
        ]);

        return BadgeRequestResult::failure(
            'Erreur lors de la remise du badge : '.$e->getMessage(),
            'deliver'
        );
    }
}
