<?php

namespace App\Services;

use App\Models\ActivityRequest;
use App\Models\ActivityRequestAttachment;
use App\Models\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ActivityRequestDocumentService
{
    /**
     * Mapping des types de documents du formulaire vers les types de la base de données
     */
    private const DOCUMENT_TYPE_MAPPING = [
        'aao_request_document' => ActivityRequestAttachment::TYPE_AAO_REQUEST,
        'kbis_document' => ActivityRequestAttachment::TYPE_KBIS,
        'principals' => ActivityRequestAttachment::TYPE_PRINCIPALS,
        'safety_referent_document' => ActivityRequestAttachment::TYPE_SAFETY_REFERENT,
        'security_referent_document' => ActivityRequestAttachment::TYPE_SECURITY_REFERENT,
        'cta_document' => ActivityRequestAttachment::TYPE_CTA,
    ];

    /**
     * Noms affichables pour chaque type de document
     */
    private const DOCUMENT_NAMES = [
        ActivityRequestAttachment::TYPE_AAO_REQUEST => 'Demande AAO',
        ActivityRequestAttachment::TYPE_KBIS => 'Extrait KBIS',
        ActivityRequestAttachment::TYPE_PRINCIPALS => 'Donneurs d\'ordre',
        ActivityRequestAttachment::TYPE_SAFETY_REFERENT => 'Référent sûreté',
        ActivityRequestAttachment::TYPE_SECURITY_REFERENT => 'Référent sécurité',
        ActivityRequestAttachment::TYPE_CTA => 'CTA',
    ];

    /**
     * Stock les documents de la demande d'activité dans le dossier du client
     * Crée les enregistrements ActivityRequestAttachment
     * Si un ActivityRequest est fourni et que de nouveaux fichiers "principals" sont uploadés,
     * supprime les anciens documents "principals" avant d'ajouter les nouveaux
     */
    public function storeDocuments(array $documents, Client $client, int $activityRequestId, ?ActivityRequest $activityRequest = null): void
    {
        try {
            // Créer le nom du dossier client
            $clientFolderName = $this->generateClientFolderName($client->company_name);

            foreach ($documents as $documentType => $file) {
                // Pour les "principals" (donneurs d'ordre), vérifier s'il y a de nouveaux fichiers valides
                $hasValidPrincipalsFiles = false;
                if ($documentType === 'principals' && ! empty($file)) {
                    if (is_array($file)) {
                        // Vérifier qu'il y a au moins un fichier valide dans le tableau
                        foreach ($file as $singleFile) {
                            if ($singleFile instanceof UploadedFile) {
                                $hasValidPrincipalsFiles = true;
                                break;
                            }
                        }
                    } elseif ($file instanceof UploadedFile) {
                        $hasValidPrincipalsFiles = true;
                    }

                    // Supprimer les anciens documents seulement si de nouveaux fichiers valides sont présents
                    if ($hasValidPrincipalsFiles && $activityRequest) {
                        $this->deleteExistingPrincipalsDocuments($activityRequest);
                    }
                }

                // Gérer les multiples fichiers (principals)
                if (is_array($file)) {
                    $index = 1;
                    foreach ($file as $singleFile) {
                        if ($singleFile instanceof UploadedFile) {
                            $this->storeDocumentAsAttachment(
                                $singleFile,
                                $documentType,
                                $clientFolderName,
                                $activityRequestId,
                                $index
                            );
                            $index++;
                        }
                    }
                } elseif ($file instanceof UploadedFile) {
                    $this->storeDocumentAsAttachment(
                        $file,
                        $documentType,
                        $clientFolderName,
                        $activityRequestId
                    );
                }
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du stockage des documents', [
                'error' => $e->getMessage(),
                'activity_request_id' => $activityRequestId,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Stock UN document dans le dossier de la demande d'activité du client
     * et crée l'enregistrement ActivityRequestAttachment
     *
     * @param  int|null  $index  Index pour les fichiers multiples (principals), null pour les fichiers uniques
     */
    protected function storeDocumentAsAttachment(
        UploadedFile $file,
        string $documentType,
        string $clientFolderName,
        int $activityRequestId,
        ?int $index = null
    ): ActivityRequestAttachment {
        // Convertir le type du formulaire vers le type de la base de données
        $attachmentType = self::DOCUMENT_TYPE_MAPPING[$documentType] ?? $documentType;

        // Générer un nom de fichier (avec index si multiple)
        $filename = $this->generateFilename($file, $attachmentType, $clientFolderName, $index);

        // Définir le chemin de stockage : storage/public/clients/[nom-client]/documents/activity-requests/[id]/
        $path = "clients/{$clientFolderName}/documents/activity-requests/{$activityRequestId}";

        // Stocker le fichier
        $storedPath = $file->storeAs($path, $filename, 'public');

        if (! $storedPath) {
            throw new \Exception("Erreur lors du stockage du document {$documentType}");
        }

        // Générer le nom affichable (avec numéro si multiple)
        $displayName = self::DOCUMENT_NAMES[$attachmentType] ?? $documentType;
        if ($index !== null) {
            $displayName .= " #{$index}";
        }

        // Créer l'enregistrement ActivityRequestAttachment
        $attachment = ActivityRequestAttachment::create([
            'activity_request_id' => $activityRequestId,
            'type' => $attachmentType,
            'path' => $storedPath,
            'name' => $displayName,
        ]);

        Log::info('Document stocké et attachment créé', [
            'activity_request_id' => $activityRequestId,
            'type' => $attachmentType,
            'path' => $storedPath,
            'index' => $index,
        ]);

        return $attachment;
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
     * Génère un nom de fichier : [type-document]-[nom-entreprise]-[timestamp]-[index]-[unique].[extension]
     * Pour les fichiers multiples (principals), l'index est ajouté pour garantir l'unicité
     * Un identifiant unique supplémentaire est ajouté pour éviter les collisions
     */
    protected function generateFilename(UploadedFile $file, string $documentType, string $clientFolderName, ?int $index = null): string
    {
        $timestamp = now()->timestamp;
        $extension = $file->getClientOriginalExtension() ?: 'pdf';

        // Ajouter un identifiant unique (microtime + random) pour garantir l'unicité absolue
        $uniqueId = substr(str_replace('.', '', microtime(true)), -8).rand(1000, 9999);

        // Format de base : [type-document]-[nom-entreprise]-[timestamp]-[unique]
        $baseName = "{$documentType}-{$clientFolderName}-{$timestamp}-{$uniqueId}";

        // Ajouter l'index si présent (pour les fichiers multiples - pour la lisibilité)
        if ($index !== null) {
            $baseName .= "-{$index}";
        }

        return "{$baseName}.{$extension}";
    }

    /**
     * Copie les documents d'une ancienne demande vers une nouvelle demande (pour le renouvellement)
     * Crée les nouveaux enregistrements ActivityRequestAttachment
     */
    public function copyDocumentsFromPreviousRequest(int $previousActivityRequestId, Client $client, int $newActivityRequestId): void
    {
        // Récupérer l'ancienne demande avec ses attachments
        $previousRequest = ActivityRequest::with('attachments')->find($previousActivityRequestId);

        if (! $previousRequest) {
            Log::warning('Service - Demande précédente non trouvée', ['previous_id' => $previousActivityRequestId]);

            return;
        }

        // Créer le nom du dossier client
        $clientFolderName = $this->generateClientFolderName($client->company_name);

        // Chemins source et destination
        $destinationBasePath = "clients/{$clientFolderName}/documents/activity-requests/{$newActivityRequestId}";

        // Utiliser le disque public explicitement
        $disk = Storage::disk('public');

        // Créer le répertoire de destination s'il n'existe pas
        if (! $disk->exists($destinationBasePath)) {
            $disk->makeDirectory($destinationBasePath);
            Log::info('Service - Répertoire créé', ['path' => $destinationBasePath]);
        }

        // Compter les principals pour gérer l'index lors de la copie
        $principalsCount = 0;
        $timestamp = now()->timestamp;

        // Copier tous les attachments de l'ancienne demande
        foreach ($previousRequest->attachments as $attachment) {
            $oldFilePath = $attachment->path;

            // Vérifier si le fichier existe
            if (! $disk->exists($oldFilePath)) {
                Log::warning('Service - Fichier source non trouvé', [
                    'attachment_id' => $attachment->id,
                    'path' => $oldFilePath,
                ]);

                continue;
            }

            // Pour les principals multiples, incrémenter le compteur et utiliser l'index
            $index = null;
            if ($attachment->type === ActivityRequestAttachment::TYPE_PRINCIPALS) {
                $principalsCount++;
                $index = $principalsCount;
            }

            // Générer un nouveau nom de fichier
            $extension = pathinfo($oldFilePath, PATHINFO_EXTENSION) ?: 'pdf';
            $baseName = "{$attachment->type}-{$clientFolderName}-{$timestamp}";
            if ($index !== null) {
                $baseName .= "-{$index}";
            }
            $newFilename = "{$baseName}.{$extension}";
            $destinationPath = "{$destinationBasePath}/{$newFilename}";

            // Copier le fichier
            $disk->copy($oldFilePath, $destinationPath);

            // Créer le nouvel enregistrement ActivityRequestAttachment
            ActivityRequestAttachment::create([
                'activity_request_id' => $newActivityRequestId,
                'type' => $attachment->type,
                'path' => $destinationPath,
                'name' => $attachment->name, // Conserver le nom original (qui peut déjà contenir #1, #2, etc.)
            ]);

            Log::info('Service - Document copié avec succès', [
                'type' => $attachment->type,
                'destination' => $destinationPath,
                'index' => $index,
            ]);
        }
    }

    /**
     * Créer un fichier ZIP contenant tous les documents d'une demande d'activité
     *
     * @return string|null Le chemin du fichier ZIP créé, ou null si aucun document n'est disponible
     */
    public function createDocumentsZip(ActivityRequest $activityRequest): ?string
    {
        // Charger les attachments avec eager loading
        $activityRequest->load('attachments');

        // Créer un nom de fichier pour le ZIP
        $zipFileName = 'demande-activite-'.$activityRequest->id.'-'.now()->timestamp.'.zip';
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

        // Mapping des types vers les noms de fichiers dans le ZIP
        $friendlyNames = [
            ActivityRequestAttachment::TYPE_AAO_REQUEST => 'demande-aao',
            ActivityRequestAttachment::TYPE_KBIS => 'extrait-kbis',
            ActivityRequestAttachment::TYPE_PRINCIPALS => 'donneurs-ordre',
            ActivityRequestAttachment::TYPE_SAFETY_REFERENT => 'referent-surete',
            ActivityRequestAttachment::TYPE_SECURITY_REFERENT => 'referent-securite',
            ActivityRequestAttachment::TYPE_CTA => 'cta',
        ];

        // Ajouter chaque document au ZIP s'il existe
        foreach ($activityRequest->attachments as $attachment) {
            if ($disk->exists($attachment->path)) {
                $filePath = $disk->path($attachment->path);
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $baseName = $friendlyNames[$attachment->type] ?? $attachment->type;

                // Pour les principals multiples, ajouter un numéro
                if ($attachment->type === ActivityRequestAttachment::TYPE_PRINCIPALS) {
                    $principalsCount = $activityRequest->attachments
                        ->where('type', ActivityRequestAttachment::TYPE_PRINCIPALS)
                        ->where('id', '<=', $attachment->id)
                        ->count();
                    $zipEntryName = "{$baseName}-{$principalsCount}.{$extension}";
                } else {
                    $zipEntryName = "{$baseName}.{$extension}";
                }

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
            Log::info('Service - Aucun document disponible pour créer le ZIP', ['activity_request_id' => $activityRequest->id]);

            return null;
        }

        Log::info('Service - ZIP créé avec succès', ['activity_request_id' => $activityRequest->id, 'path' => $zipPath]);

        return $zipPath;
    }

    /**
     * Récupère le chemin d'un document par son type
     * Pour les types uniques, retourne le premier attachment trouvé
     * Pour les principals (multiples), retourne le premier (ou tous via getPrincipalsDocuments())
     */
    public function getDocumentPath(ActivityRequest $activityRequest, string $documentType): ?string
    {
        // Convertir le type du formulaire vers le type de la base de données
        $attachmentType = self::DOCUMENT_TYPE_MAPPING[$documentType] ?? $documentType;

        $attachment = $activityRequest->attachments()
            ->ofType($attachmentType)
            ->first();

        return $attachment?->path;
    }

    /**
     * Récupère un attachment par son type
     */
    public function getAttachmentByType(ActivityRequest $activityRequest, string $documentType): ?ActivityRequestAttachment
    {
        $attachmentType = self::DOCUMENT_TYPE_MAPPING[$documentType] ?? $documentType;

        return $activityRequest->attachments()
            ->ofType($attachmentType)
            ->first();
    }

    /**
     * Supprime les anciens documents "principals" (donneurs d'ordre) d'une demande d'activité
     * Supprime à la fois les fichiers physiques et les enregistrements en base de données
     */
    protected function deleteExistingPrincipalsDocuments(ActivityRequest $activityRequest): void
    {
        // Charger les attachments si pas déjà chargés
        if (! $activityRequest->relationLoaded('attachments')) {
            $activityRequest->load('attachments');
        }

        // Récupérer tous les attachments de type "principals"
        $principalsAttachments = $activityRequest->attachments()
            ->where('type', ActivityRequestAttachment::TYPE_PRINCIPALS)
            ->get();

        if ($principalsAttachments->isEmpty()) {
            return;
        }

        $disk = Storage::disk('public');
        $deletedCount = 0;

        // Supprimer chaque fichier et son enregistrement
        foreach ($principalsAttachments as $attachment) {
            // Supprimer le fichier physique s'il existe
            if ($disk->exists($attachment->path)) {
                $disk->delete($attachment->path);
                Log::info('Service - Fichier principal supprimé', [
                    'attachment_id' => $attachment->id,
                    'path' => $attachment->path,
                ]);
            }

            // Supprimer l'enregistrement en base de données
            $attachment->delete();
            $deletedCount++;
        }

        Log::info('Service - Anciens documents principals supprimés', [
            'activity_request_id' => $activityRequest->id,
            'deleted_count' => $deletedCount,
        ]);
    }
}
