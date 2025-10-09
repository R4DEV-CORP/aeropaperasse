<?php

namespace App\Services;

use App\Models\ActivityRequest;
use App\Models\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ActivityRequestDocumentService
{
    /**
     * Stock les documents de la demande d'activité dans le dossier du client
     */
    public function storeDocuments(array $documents, Client $client, int $activityRequestId): array
    {
        $storedDocuments = [];

        // Créer le nom du dossier client
        $clientFolderName = $this->generateClientFolderName($client->company_name);
        
        foreach ($documents as $documentType => $file) {
            if ($file instanceof UploadedFile) {
                $storedDocuments[$documentType] = $this->storeDocument(
                    $file, 
                    $documentType, 
                    $clientFolderName, 
                    $activityRequestId
                );
            }
        }

        return $storedDocuments;
    }

    /**
     * Stock UN document dans le dossier de la demande d'activité du client
     */
    protected function storeDocument(
        UploadedFile $file, 
        string $documentType, 
        string $clientFolderName, 
        int $activityRequestId
    ): string
    {
        // Générer un nom de fichier 
        $filename = $this->generateFilename($file, $documentType, $clientFolderName);
        
        // Définir le chemin de stockage : storage/public/clients/[nom-client]/documents/activity-requests/[id]/
        $path = "clients/{$clientFolderName}/documents/activity-requests/{$activityRequestId}";
        
        // Stocker le fichier
        $storedPath = $file->storeAs($path, $filename, 'public');
        
        if (!$storedPath) {
            throw new \Exception("Erreur lors du stockage du document {$documentType}");
        }

        return $storedPath;
    }

    /**
     * Générer le nom du dossier client (même logique que ClientDocumentService)
     */
    protected function generateClientFolderName(string $companyName): string
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
     * Génère un nom de fichier : [type-document]-[nom-entreprise]-[timestamp].pdf
     */
    protected function generateFilename(UploadedFile $file, string $documentType, string $clientFolderName): string
    {
        $timestamp = now()->timestamp;
        
        // Format : [type-document]-[nom-entreprise]-[timestamp].pdf
        return "{$documentType}-{$clientFolderName}-{$timestamp}.pdf";
    }

    /**
     * Copie les documents d'une ancienne demande vers une nouvelle demande (pour le renouvellement)
     */
    public function copyDocumentsFromPreviousRequest(int $previousActivityRequestId, Client $client, int $newActivityRequestId): array
    {
        $copiedDocuments = [];
        
        // Récupérer l'ancienne demande
        $previousRequest = ActivityRequest::find($previousActivityRequestId);
        
        if (!$previousRequest) {
            Log::warning('Service - Demande précédente non trouvée', ['previous_id' => $previousActivityRequestId]);
            return $copiedDocuments;
        }

        // Créer le nom du dossier client
        $clientFolderName = $this->generateClientFolderName($client->company_name);
        
        // Chemins source et destination
        $destinationBasePath = "clients/{$clientFolderName}/documents/activity-requests/{$newActivityRequestId}";
        
        // Types de documents à copier
        $documentTypes = [
            'customer_certificate_document',
            'prefectural_agreement_document',
            'iata_contract_document',
            'cta_document'
        ];
        
        // Utiliser le disque public explicitement
        $disk = Storage::disk('public');
        
        foreach ($documentTypes as $documentType) {
            // Vérifier si le document existe dans l'ancienne demande
            if (!empty($previousRequest->$documentType)) {
                // Le chemin du fichier source (déjà stocké dans la BDD)
                $oldFilePath = $previousRequest->$documentType;
                
                // Générer un nouveau nom de fichier avec un nouveau timestamp
                $timestamp = now()->timestamp;
                $newFilename = "{$documentType}-{$clientFolderName}-{$timestamp}.pdf";
                
                $destinationPath = "{$destinationBasePath}/{$newFilename}";
                
                // Copier le fichier s'il existe
                if ($disk->exists($oldFilePath)) {
                    // Créer le répertoire de destination s'il n'existe pas
                    if (!$disk->exists($destinationBasePath)) {
                        $disk->makeDirectory($destinationBasePath);
                    }
                    
                    // Copier le fichier
                    $disk->copy($oldFilePath, $destinationPath);
                    
                    // Enregistrer le nouveau chemin
                    $copiedDocuments[$documentType] = $destinationPath;
                } else {
                    Log::warning('Document Service - Fichier source non trouvé', [
                        'type' => $documentType,
                        'path' => $oldFilePath,
                    ]);
                }
            } else {
                Log::info('Service - Pas de document dans ancienne demande', ['type' => $documentType]);
            }
        }
        
        return $copiedDocuments;
    }
}
