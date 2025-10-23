<?php

namespace App\Services;

use App\Models\Coworker;
use App\Models\Training;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CertificateTrainingDocumentService
{
    /**
     * Upload un certificat de formation pour un coworker-training spécifique
     */
    public function uploadCertificate(int $trainingId, UploadedFile $file): string
    {
        // Récupérer les informations de la relation coworker-training
        $coworkerTraining = DB::table('coworker_trainings')
            ->join('coworkers', 'coworker_trainings.coworker_id', '=', 'coworkers.id')
            ->join('trainings', 'coworker_trainings.training_id', '=', 'trainings.id')
            ->join('clients', 'coworkers.client_id', '=', 'clients.id')
            ->select(
                'coworker_trainings.id',
                'coworkers.firstname',
                'coworkers.lastname',
                'trainings.title as training_title',
                'clients.company_name'
            )
            ->where('coworker_trainings.id', $trainingId)
            ->first();

        if (!$coworkerTraining) {
            throw new \Exception("Association coworker-training non trouvée avec l'ID: {$trainingId}");
        }

        // Générer le nom du dossier client
        $clientFolderName = $this->generateClientFolderName($coworkerTraining->company_name);
        
        // Générer le nom du fichier
        $filename = $this->generateFilename(
            $file,
            $coworkerTraining->firstname,
            $coworkerTraining->lastname,
            $coworkerTraining->training_title
        );
        
        // Définir le chemin de stockage : storage/app/public/clients/[nom-client]/documents/trainings/
        $path = "clients/{$clientFolderName}/documents/trainings";
        
        // Stocker le fichier
        $storedPath = $file->storeAs($path, $filename, 'public');
        
        if (!$storedPath) {
            throw new \Exception("Erreur lors du stockage du certificat de formation");
        }

        // Mettre à jour le chemin du certificat dans la table coworker_trainings
        DB::table('coworker_trainings')
            ->where('id', $trainingId)
            ->update(['certificate_path' => $storedPath]);

        Log::info('Certificat de formation uploadé avec succès', [
            'training_id' => $trainingId,
            'path' => $storedPath,
            'coworker' => "{$coworkerTraining->firstname} {$coworkerTraining->lastname}",
            'training' => $coworkerTraining->training_title
        ]);

        return $storedPath;
    }

    /**
     * Générer le nom du dossier client (même logique que ActivityRequestDocumentService)
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
     * Génère un nom de fichier : [prenom-coworker]-[nom-coworker]-[nom-training]-[timestamp].[extension]
     */
    protected function generateFilename(
        UploadedFile $file, 
        string $coworkerFirstname, 
        string $coworkerLastname, 
        string $trainingTitle
    ): string {
        $timestamp = now()->timestamp;
        
        // Formater les noms selon les spécifications
        $formattedFirstname = $this->formatName($coworkerFirstname);
        $formattedLastname = $this->formatName($coworkerLastname);
        $formattedTrainingTitle = $this->formatTrainingTitle($trainingTitle);
        
        // Récupérer l'extension du fichier
        $extension = $file->getClientOriginalExtension();
        
        // Format : [prenom-coworker]-[nom-coworker]-[nom-training]-[timestamp].[extension]
        return "{$formattedFirstname}-{$formattedLastname}-{$formattedTrainingTitle}-{$timestamp}.{$extension}";
    }

    /**
     * Formater le prénom/nom du coworker (minuscules, espaces remplacés par des tirets)
     */
    protected function formatName(string $name): string
    {
        // Convertir en minuscules
        $formatted = strtolower($name);
        
        // Remplacer les espaces par des tirets
        $formatted = str_replace(' ', '-', $formatted);
        
        // Supprimer les caractères spéciaux (garder seulement lettres, chiffres et tirets)
        $formatted = preg_replace('/[^a-z0-9\-]/', '', $formatted);
        
        // Supprimer les tirets multiples consécutifs
        $formatted = preg_replace('/-+/', '-', $formatted);
        
        // Supprimer les tirets en début et fin
        $formatted = trim($formatted, '-');
        
        return $formatted;
    }

    /**
     * Formater le nom de la formation (minuscules, espaces et points remplacés par des tirets)
     */
    protected function formatTrainingTitle(string $title): string
    {
        // Convertir en minuscules
        $formatted = strtolower($title);
        
        // Remplacer les espaces et les points par des tirets
        $formatted = str_replace([' ', '.'], '-', $formatted);
        
        // Supprimer les caractères spéciaux (garder seulement lettres, chiffres et tirets)
        $formatted = preg_replace('/[^a-z0-9\-]/', '', $formatted);
        
        // Supprimer les tirets multiples consécutifs
        $formatted = preg_replace('/-+/', '-', $formatted);
        
        // Supprimer les tirets en début et fin
        $formatted = trim($formatted, '-');
        
        return $formatted;
    }

    /**
     * Récupérer le chemin d'un certificat de formation
     */
    public function getCertificatePath(int $trainingId): ?string
    {
        $coworkerTraining = DB::table('coworker_trainings')
            ->where('id', $trainingId)
            ->value('certificate_path');

        return $coworkerTraining;
    }
}
