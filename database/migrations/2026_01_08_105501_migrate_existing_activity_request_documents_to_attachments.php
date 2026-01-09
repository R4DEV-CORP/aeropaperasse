<?php

use App\Models\ActivityRequest;
use App\Models\ActivityRequestAttachment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Mapping des colonnes existantes vers les types de documents
     */
    private const DOCUMENT_MAPPING = [
        'aao_request_document' => ActivityRequestAttachment::TYPE_AAO_REQUEST,
        'kbis_document' => ActivityRequestAttachment::TYPE_KBIS,
        'term_document' => ActivityRequestAttachment::TYPE_PRINCIPALS,
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
     * Run the migrations.
     */
    public function up(): void
    {
        Log::info('Début de la migration des documents de activity_requests vers activity_request_attachments');

        $disk = Storage::disk('public');
        $migratedCount = 0;
        $errorCount = 0;

        // Récupérer toutes les demandes d'activité
        $activityRequests = ActivityRequest::all();

        foreach ($activityRequests as $activityRequest) {
            foreach (self::DOCUMENT_MAPPING as $columnName => $documentType) {
                $documentPath = $activityRequest->$columnName;

                // Ignorer si le document n'existe pas
                if (empty($documentPath)) {
                    continue;
                }

                // Vérifier si le fichier existe physiquement
                if (! $disk->exists($documentPath)) {
                    Log::warning('Fichier non trouvé lors de la migration', [
                        'activity_request_id' => $activityRequest->id,
                        'column' => $columnName,
                        'path' => $documentPath,
                    ]);
                    $errorCount++;

                    continue;
                }

                // Vérifier si l'attachment n'existe pas déjà
                $existingAttachment = ActivityRequestAttachment::where('activity_request_id', $activityRequest->id)
                    ->where('type', $documentType)
                    ->where('path', $documentPath)
                    ->first();

                if ($existingAttachment) {
                    Log::info('Attachment déjà existant, ignoré', [
                        'activity_request_id' => $activityRequest->id,
                        'type' => $documentType,
                    ]);

                    continue;
                }

                // Créer l'attachment
                try {
                    ActivityRequestAttachment::create([
                        'activity_request_id' => $activityRequest->id,
                        'type' => $documentType,
                        'path' => $documentPath,
                        'name' => self::DOCUMENT_NAMES[$documentType],
                    ]);

                    $migratedCount++;
                    Log::debug('Document migré avec succès', [
                        'activity_request_id' => $activityRequest->id,
                        'type' => $documentType,
                        'path' => $documentPath,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la création de l\'attachment', [
                        'activity_request_id' => $activityRequest->id,
                        'type' => $documentType,
                        'path' => $documentPath,
                        'error' => $e->getMessage(),
                    ]);
                    $errorCount++;
                }
            }
        }

        Log::info('Fin de la migration des documents', [
            'total_migrated' => $migratedCount,
            'total_errors' => $errorCount,
            'total_requests' => $activityRequests->count(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Log::info('Début de la suppression des attachments migrés');

        // Supprimer tous les attachments créés par cette migration
        // Note: Cette opération est irréversible si les colonnes ont été supprimées
        ActivityRequestAttachment::truncate();

        Log::info('Suppression des attachments terminée');
    }
};
