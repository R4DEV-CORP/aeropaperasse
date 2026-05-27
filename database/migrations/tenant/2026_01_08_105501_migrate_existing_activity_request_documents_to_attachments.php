<?php

use App\Models\ActivityRequest;
use App\Models\ActivityRequestAttachment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
        Log::info('=== DÉBUT DE LA MIGRATION DES DOCUMENTS ===');
        Log::info('Migration des documents de activity_requests vers activity_request_attachments');

        // Vérifier que la table activity_request_attachments existe
        if (! Schema::hasTable('activity_request_attachments')) {
            Log::error('La table activity_request_attachments n\'existe pas. Veuillez exécuter la migration de création d\'abord.');
            throw new RuntimeException('La table activity_request_attachments n\'existe pas.');
        }

        // Vérifier que la table activity_requests existe et contient les colonnes attendues
        if (! Schema::hasTable('activity_requests')) {
            Log::error('La table activity_requests n\'existe pas.');
            throw new RuntimeException('La table activity_requests n\'existe pas.');
        }

        $disk = Storage::disk('public');
        $migratedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $missingFileCount = 0;

        // Utiliser des chunks pour éviter les problèmes de mémoire avec de grandes quantités de données
        ActivityRequest::chunk(100, function ($activityRequests) use ($disk, &$migratedCount, &$skippedCount, &$errorCount, &$missingFileCount) {
            foreach ($activityRequests as $activityRequest) {
                foreach (self::DOCUMENT_MAPPING as $columnName => $documentType) {
                    // Vérifier que la colonne existe dans la table
                    if (! Schema::hasColumn('activity_requests', $columnName)) {
                        Log::debug("Colonne {$columnName} n'existe pas, ignorée", [
                            'activity_request_id' => $activityRequest->id,
                        ]);

                        continue;
                    }

                    $documentPath = $activityRequest->$columnName;

                    // Ignorer si le document n'existe pas
                    if (empty($documentPath)) {
                        continue;
                    }

                    // Vérifier si l'attachment n'existe pas déjà (idempotence)
                    $existingAttachment = ActivityRequestAttachment::where('activity_request_id', $activityRequest->id)
                        ->where('type', $documentType)
                        ->where('path', $documentPath)
                        ->first();

                    if ($existingAttachment) {
                        $skippedCount++;

                        continue;
                    }

                    // Vérifier si le fichier existe physiquement
                    if (! $disk->exists($documentPath)) {
                        Log::warning('Fichier non trouvé lors de la migration', [
                            'activity_request_id' => $activityRequest->id,
                            'column' => $columnName,
                            'path' => $documentPath,
                        ]);
                        $missingFileCount++;
                        // On continue quand même pour créer l'enregistrement même si le fichier est manquant
                        // Cela permet de préserver la référence au document
                    }

                    // Créer l'attachment dans une transaction
                    try {
                        DB::transaction(function () use ($activityRequest, $documentType, $documentPath, &$migratedCount) {
                            ActivityRequestAttachment::create([
                                'activity_request_id' => $activityRequest->id,
                                'type' => $documentType,
                                'path' => $documentPath,
                                'name' => self::DOCUMENT_NAMES[$documentType],
                            ]);

                            $migratedCount++;
                        });
                    } catch (Exception $e) {
                        Log::error('Erreur lors de la création de l\'attachment', [
                            'activity_request_id' => $activityRequest->id,
                            'type' => $documentType,
                            'path' => $documentPath,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $errorCount++;
                    }
                }
            }
        });

        $summary = [
            'total_migrated' => $migratedCount,
            'total_skipped' => $skippedCount,
            'total_errors' => $errorCount,
            'missing_files' => $missingFileCount,
        ];

        Log::info('=== FIN DE LA MIGRATION DES DOCUMENTS ===', $summary);

        // Afficher un résumé dans la console si possible
        if (app()->runningInConsole()) {
            echo "\n";
            echo "=== RÉSUMÉ DE LA MIGRATION ===\n";
            echo "Documents migrés : {$migratedCount}\n";
            echo "Documents ignorés (déjà existants) : {$skippedCount}\n";
            echo "Erreurs : {$errorCount}\n";
            echo "Fichiers manquants : {$missingFileCount}\n";
            echo "\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Log::info('=== DÉBUT DE LA SUPPRESSION DES ATTACHMENTS ===');

        // ATTENTION : Cette opération supprime TOUS les attachments
        // Si les colonnes de documents ont déjà été supprimées de activity_requests,
        // cette opération est irréversible et les données seront perdues.
        $count = ActivityRequestAttachment::count();

        if ($count > 0) {
            Log::warning("Suppression de {$count} attachments. Cette opération peut être irréversible.");
            ActivityRequestAttachment::truncate();
            Log::info("{$count} attachments supprimés.");
        } else {
            Log::info('Aucun attachment à supprimer.');
        }

        Log::info('=== FIN DE LA SUPPRESSION DES ATTACHMENTS ===');
    }
};
