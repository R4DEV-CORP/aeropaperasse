<?php

namespace App\Console\Commands;

use App\Mail\TrainingExpiryNotification;
use App\Models\CoworkerTraining;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyTrainingExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trainings:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie les formations qui vont bientôt expirer et envoie une notification au collaborateur';

    /**
     * Training assignments live in the per-tenant `coworker_trainings` table, so the
     * check runs once inside each tenant's context. The log path is resolved once in
     * the central context so every tenant appends to a single log file (storage_path
     * is suffixed per tenant once tenancy is initialized).
     * See docs/multi-tenant-migration.md (tenant-aware infrastructure).
     */
    public function handle(): int
    {
        $logPath = storage_path('logs/training-expiry-check.log');

        if (! is_dir(dirname($logPath))) {
            mkdir(dirname($logPath), 0775, true);
        }

        $this->log($logPath, 'Démarrage de la vérification des formations');

        Tenant::all()->each(function (Tenant $tenant) use ($logPath): void {
            $tenant->run(function () use ($logPath, $tenant): void {
                $this->checkExpiringTrainingsForCurrentTenant($logPath, (string) $tenant->getTenantKey());
            });
        });

        $this->log($logPath, 'Vérification des formations terminée.');

        return 0;
    }

    private function checkExpiringTrainingsForCurrentTenant(string $logPath, string $tenantId): void
    {
        $expiryIntervals = [90, 30, 15, 7];

        foreach ($expiryIntervals as $days) {
            $expiryDate = Carbon::now()->addDays($days)->toDateString();

            $coworkerTrainings = CoworkerTraining::with(['coworker', 'training'])
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', $expiryDate)
                ->get();

            $this->info("[{$tenantId}] Traitement de {$coworkerTrainings->count()} formations expirant dans {$days} jours");
            $this->log($logPath, "[{$tenantId}] Traitement de {$coworkerTrainings->count()} formations expirant dans {$days} jours");

            foreach ($coworkerTrainings as $coworkerTraining) {
                $recipientEmail = $coworkerTraining->coworker?->email;

                if ($recipientEmail) {
                    try {
                        Mail::to($recipientEmail)->send(new TrainingExpiryNotification($coworkerTraining, $days));

                        $this->info("[{$tenantId}] Notification envoyée pour la formation #{$coworkerTraining->id} à {$recipientEmail}");
                        $this->log($logPath, "[{$tenantId}] Notification envoyée pour la formation #{$coworkerTraining->id} à {$recipientEmail}");
                    } catch (\Exception $e) {
                        $this->error("[{$tenantId}] Erreur lors de l'envoi de la notification pour la formation #{$coworkerTraining->id}: ".$e->getMessage());
                        $this->log($logPath, "[{$tenantId}] Erreur lors de l'envoi de la notification pour la formation #{$coworkerTraining->id}: ".$e->getMessage());
                    }
                } else {
                    $this->warn("[{$tenantId}] Impossible d'envoyer une notification pour la formation #{$coworkerTraining->id}: email manquant");
                    $this->log($logPath, "[{$tenantId}] Impossible d'envoyer une notification pour la formation #{$coworkerTraining->id}: email manquant");
                }
            }
        }
    }

    private function log(string $logPath, string $message): void
    {
        file_put_contents($logPath, date('[Y-m-d H:i:s] ').$message."\n", FILE_APPEND | LOCK_EX);
        @chmod($logPath, 0664);
    }
}
