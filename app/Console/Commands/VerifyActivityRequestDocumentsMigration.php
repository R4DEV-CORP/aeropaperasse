<?php

namespace App\Console\Commands;

use App\Models\ActivityRequest;
use App\Models\ActivityRequestAttachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class VerifyActivityRequestDocumentsMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-requests:verify-documents-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie la migration des documents de activity_requests vers activity_request_attachments';

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
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== VÉRIFICATION DE LA MIGRATION DES DOCUMENTS ===');
        $this->newLine();

        // Vérifier la structure des tables
        $this->info('1. Vérification de la structure des tables...');
        if (! Schema::hasTable('activity_requests')) {
            $this->error('❌ La table activity_requests n\'existe pas.');

            return Command::FAILURE;
        }
        $this->info('   ✓ Table activity_requests existe');

        if (! Schema::hasTable('activity_request_attachments')) {
            $this->error('❌ La table activity_request_attachments n\'existe pas.');

            return Command::FAILURE;
        }
        $this->info('   ✓ Table activity_request_attachments existe');
        $this->newLine();

        // Compter les documents dans activity_requests
        $this->info('2. Analyse des documents dans activity_requests...');
        $totalDocumentsInRequests = 0;
        $documentsByType = [];

        foreach (self::DOCUMENT_MAPPING as $columnName => $documentType) {
            if (! Schema::hasColumn('activity_requests', $columnName)) {
                $this->warn("   ⚠ Colonne {$columnName} n'existe pas dans activity_requests");

                continue;
            }

            $count = ActivityRequest::whereNotNull($columnName)
                ->where($columnName, '!=', '')
                ->count();

            $totalDocumentsInRequests += $count;
            $documentsByType[$documentType] = $count;

            $this->info("   • {$columnName} ({$documentType}): {$count} document(s)");
        }

        $this->info("   Total: {$totalDocumentsInRequests} document(s) dans activity_requests");
        $this->newLine();

        // Compter les attachments
        $this->info('3. Analyse des attachments dans activity_request_attachments...');
        $totalAttachments = ActivityRequestAttachment::count();
        $attachmentsByType = ActivityRequestAttachment::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        foreach (self::DOCUMENT_MAPPING as $columnName => $documentType) {
            $count = $attachmentsByType[$documentType] ?? 0;
            $this->info("   • {$documentType}: {$count} attachment(s)");
        }

        $this->info("   Total: {$totalAttachments} attachment(s) dans activity_request_attachments");
        $this->newLine();

        // Comparaison
        $this->info('4. Comparaison...');
        if ($totalDocumentsInRequests === $totalAttachments) {
            $this->info("   ✓ Le nombre de documents correspond ({$totalDocumentsInRequests})");
        } else {
            $diff = $totalDocumentsInRequests - $totalAttachments;
            $this->warn("   ⚠ Différence détectée: {$diff} document(s) non migré(s)");
            $this->warn("      Documents dans activity_requests: {$totalDocumentsInRequests}");
            $this->warn("      Attachments dans activity_request_attachments: {$totalAttachments}");
        }
        $this->newLine();

        // Vérifier les fichiers manquants
        $this->info('5. Vérification de l\'existence des fichiers...');
        $disk = Storage::disk('public');
        $missingFiles = 0;
        $existingFiles = 0;

        ActivityRequestAttachment::chunk(100, function ($attachments) use ($disk, &$missingFiles, &$existingFiles) {
            foreach ($attachments as $attachment) {
                if ($disk->exists($attachment->path)) {
                    $existingFiles++;
                } else {
                    $missingFiles++;
                    $this->warn("   ⚠ Fichier manquant: {$attachment->path} (activity_request_id: {$attachment->activity_request_id})");
                }
            }
        });

        $this->info("   Fichiers existants: {$existingFiles}");
        if ($missingFiles > 0) {
            $this->warn("   Fichiers manquants: {$missingFiles}");
        } else {
            $this->info('   ✓ Tous les fichiers existent');
        }
        $this->newLine();

        // Résumé final
        $this->info('=== RÉSUMÉ ===');
        $this->table(
            ['Type', 'Dans activity_requests', 'Dans activity_request_attachments', 'Statut'],
            collect(self::DOCUMENT_MAPPING)->map(function ($documentType, $columnName) use ($documentsByType, $attachmentsByType) {
                $inRequests = $documentsByType[$documentType] ?? 0;
                $inAttachments = $attachmentsByType[$documentType] ?? 0;
                $status = $inRequests === $inAttachments ? '✓ OK' : '⚠ Différence';

                return [
                    $documentType,
                    $inRequests,
                    $inAttachments,
                    $status,
                ];
            })->toArray()
        );

        if ($totalDocumentsInRequests === $totalAttachments && $missingFiles === 0) {
            $this->newLine();
            $this->info('✅ La migration semble complète et correcte !');

            return Command::SUCCESS;
        }

        $this->newLine();
        $this->warn('⚠ Des différences ont été détectées. Vérifiez les logs ci-dessus.');

        return Command::SUCCESS;
    }
}
