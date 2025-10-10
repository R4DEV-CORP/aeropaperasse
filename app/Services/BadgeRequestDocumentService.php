<?php

namespace App\Services;

use App\Models\BadgeRequest;
use App\Models\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
}

