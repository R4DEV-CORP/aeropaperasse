<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ClientDocumentService
{
    /**
     * Stock les documents dans le dossier client
     */
    public function storeDocuments(array $documents, string $companyName): array
    {
        $storedDocuments = [];

        // Créer le nom du dossier client
        $clientFolderName = $this->generateClientFolderName($companyName);

        foreach ($documents as $documentType => $file) {
            if ($file instanceof UploadedFile) {
                $storedDocuments[$documentType] = $this->storeDocument($file, $documentType, $clientFolderName);
            }
        }

        return $storedDocuments;
    }

    /**
     * Stock UN document dans le dossier client
     */
    protected function storeDocument(UploadedFile $file, string $documentType, string $clientFolderName): string
    {
        // Générer un nom de fichier
        $filename = $this->generateFilename($file, $documentType, $clientFolderName);

        // Définir le chemin de stockage : storage/public/clients/[nom-client]/documents/
        $path = "clients/{$clientFolderName}/documents";

        // Stocker le fichier
        $storedPath = $file->storeAs($path, $filename, 'public');

        if (! $storedPath) {
            throw new \Exception("Erreur lors du stockage du document {$documentType}");
        }

        return $storedPath;
    }

    /**
     * Générer le nom du dossier client
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
     * Genere un nom de fichier : [type-document]-[nom-entreprise]-[timestamp].pdf
     */
    protected function generateFilename(UploadedFile $file, string $documentType, string $clientFolderName): string
    {
        $timestamp = now()->timestamp;

        // Format : [type-document]-[nom-entreprise]-[timestamp].pdf
        return "{$documentType}-{$clientFolderName}-{$timestamp}.pdf";
    }
}
