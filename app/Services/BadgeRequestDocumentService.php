<?php

namespace App\Services;

use App\Models\BadgeRequest;
use App\Models\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BadgeRequestDocumentService
{
    /**
     * Stock les documents de la demande de badge dans le dossier du client
     */
    public function storeDocuments(array $documents, Client $client, int $badgeRequestId): array
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
                    $badgeRequestId
                );
            }
        }

        return $storedDocuments;
    }

    /**
     * Stock UN document dans le dossier de la demande de badge du client
     */
    protected function storeDocument(
        UploadedFile $file, 
        string $documentType, 
        string $clientFolderName, 
        int $badgeRequestId
    ): string
    {
        // Générer un nom de fichier 
        $filename = $this->generateFilename($file, $documentType, $clientFolderName);
        
        // Définir le chemin de stockage : storage/public/clients/[nom-client]/documents/badge-requests/[id]/
        $path = "clients/{$clientFolderName}/documents/badge-requests/{$badgeRequestId}";
        
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
     * Génère un nom de fichier : [type-document]-[nom-entreprise]-[timestamp].extension
     */
    protected function generateFilename(UploadedFile $file, string $documentType, string $clientFolderName): string
    {
        $timestamp = now()->timestamp;
        $extension = $file->getClientOriginalExtension();
        
        // Format : [type-document]-[nom-entreprise]-[timestamp].extension
        return "{$documentType}-{$clientFolderName}-{$timestamp}.{$extension}";
    }

    /**
     * Créer un fichier ZIP contenant tous les documents d'une demande de badge
     *
     * @return string|null Le chemin du fichier ZIP créé, ou null si aucun document n'est disponible
     */
    public function createDocumentsZip(BadgeRequest $badgeRequest): ?string
    {
        // Types de documents à inclure dans le ZIP
        $documentTypes = [
            'selfie_photo' => 'photo-selfie',
            'identification_card' => 'carte-identite',
            'activity_authorization' => 'autorisation-activite',
            'for_document' => 'document-for',
            'formation_certificate_document' => 'certificat-formation',
            'invoice_document' => 'facture',
        ];

        // Créer un nom de fichier pour le ZIP
        $zipFileName = 'demande-badge-'.$badgeRequest->id.'-'.now()->timestamp.'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        // Créer le dossier temp s'il n'existe pas
        if (! file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Créer l'archive ZIP
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Log::error('Service - Impossible de créer l\'archive ZIP', ['path' => $zipPath]);

            return null;
        }

        $disk = Storage::disk('public');
        $filesAdded = false;

        // Ajouter chaque document au ZIP s'il existe
        foreach ($documentTypes as $field => $friendlyName) {
            if (! empty($badgeRequest->$field) && $disk->exists($badgeRequest->$field)) {
                $filePath = $disk->path($badgeRequest->$field);
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $zipEntryName = $friendlyName.'.'.$extension;

                $zip->addFile($filePath, $zipEntryName);
                $filesAdded = true;
            }
        }

        $zip->close();

        // Vérifier si des fichiers ont été ajoutés
        if (! $filesAdded) {
            // Supprimer le ZIP vide
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            Log::info('Service - Aucun document disponible pour créer le ZIP', ['badge_request_id' => $badgeRequest->id]);

            return null;
        }

        Log::info('Service - ZIP créé avec succès', ['badge_request_id' => $badgeRequest->id, 'path' => $zipPath]);

        return $zipPath;
    }

    public function getDocumentPath(BadgeRequest $badgeRequest, string $documentType): ?string
    {
        return $badgeRequest->$documentType;
    }
}

