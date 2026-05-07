<?php

namespace App\Console\Commands;

use App\Models\BadgeRequest;
use App\Services\BadgeRequestDocumentService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RenameBadgeRequestDocuments extends Command
{
    protected $signature = 'badge-requests:rename-documents {--dry-run : Affiche les opérations sans modifier les fichiers ni la BDD}';

    protected $description = 'Renomme les documents des demandes de badge au format court ({code}.{ext}) et met à jour la BDD';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');
        $codes = BadgeRequestDocumentService::DOCUMENT_TYPE_CODES;

        $this->info($dryRun ? '=== DRY-RUN — aucune modification ===' : '=== MIGRATION DES DOCUMENTS ===');
        $this->newLine();

        $stats = [
            'migrated' => 0,
            'already' => 0,
            'missing' => 0,
            'errors' => 0,
        ];

        $query = BadgeRequest::query();
        foreach (array_keys($codes) as $field) {
            $query->orWhereNotNull($field);
        }

        $query->chunkById(100, function ($badgeRequests) use ($disk, $codes, $dryRun, &$stats): void {
            foreach ($badgeRequests as $badgeRequest) {
                $this->processBadgeRequest($badgeRequest, $codes, $disk, $dryRun, $stats);
            }
        });

        $this->newLine();
        $this->info('=== RÉSUMÉ ===');
        $this->table(
            ['Migrés', 'Déjà au format court', 'Fichiers manquants', 'Erreurs'],
            [[$stats['migrated'], $stats['already'], $stats['missing'], $stats['errors']]]
        );

        return Command::SUCCESS;
    }

    /**
     * @param  array<string, string>  $codes
     * @param  array<string, int>  $stats
     */
    private function processBadgeRequest(
        BadgeRequest $badgeRequest,
        array $codes,
        Filesystem $disk,
        bool $dryRun,
        array &$stats
    ): void {
        $updates = [];

        foreach ($codes as $field => $code) {
            $currentPath = $badgeRequest->{$field};

            if (empty($currentPath)) {
                continue;
            }

            $directory = dirname($currentPath);
            $filename = basename($currentPath);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $expectedFilename = "{$code}.{$extension}";

            if ($filename === $expectedFilename) {
                $stats['already']++;

                continue;
            }

            if (! $disk->exists($currentPath)) {
                $this->warn("  ⚠ [BR#{$badgeRequest->id}] {$field} : fichier manquant sur disque ({$currentPath})");
                $stats['missing']++;

                continue;
            }

            $newPath = $directory.'/'.$expectedFilename;
            $updates[$field] = ['old' => $currentPath, 'new' => $newPath];

            $this->line("  • [BR#{$badgeRequest->id}] {$field} : {$filename} → {$expectedFilename}");
        }

        if (empty($updates)) {
            return;
        }

        if ($dryRun) {
            $stats['migrated'] += count($updates);

            return;
        }

        try {
            DB::transaction(function () use ($badgeRequest, $updates, $disk): void {
                $dbUpdates = [];

                foreach ($updates as $field => $paths) {
                    if ($disk->exists($paths['new'])) {
                        $disk->delete($paths['new']);
                    }
                    $disk->move($paths['old'], $paths['new']);
                    $dbUpdates[$field] = $paths['new'];
                }

                $badgeRequest->update($dbUpdates);
            });

            $stats['migrated'] += count($updates);
        } catch (\Throwable $e) {
            $this->error("  ❌ [BR#{$badgeRequest->id}] Erreur : {$e->getMessage()}");
            $stats['errors'] += count($updates);
        }
    }
}
