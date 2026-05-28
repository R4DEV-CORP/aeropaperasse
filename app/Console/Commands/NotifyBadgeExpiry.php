<?php

namespace App\Console\Commands;

use App\Mail\BadgeExpiryNotification;
use App\Models\Badge;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyBadgeExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie les badges qui vont bientôt expirer et envoie une notification au détenteur';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Badges live in the per-tenant databases, so the check runs once inside each
     * tenant's context. The log path is resolved once in the central context so all
     * tenants append to a single log file (storage_path is suffixed per tenant once
     * tenancy is initialized). See docs/multi-tenant-migration.md (tenant-aware infra).
     */
    public function handle(): int
    {
        $logPath = storage_path('logs/badge-expiry-check.log');

        if (! is_dir(dirname($logPath))) {
            mkdir(dirname($logPath), 0775, true);
        }

        $this->log($logPath, 'Démarrage de la vérification des badges');

        Tenant::all()->each(function (Tenant $tenant) use ($logPath): void {
            $tenant->run(function () use ($logPath, $tenant): void {
                $this->checkExpiringBadgesForCurrentTenant($logPath, (string) $tenant->getTenantKey());
            });
        });

        $this->info('Vérification terminée à '.Carbon::now());
        $this->log($logPath, 'Vérification des badges terminée.');

        return 0;
    }

    private function checkExpiringBadgesForCurrentTenant(string $logPath, string $tenantId): void
    {
        $expiryIntervals = [90, 30, 15, 7];

        foreach ($expiryIntervals as $days) {
            $expiryDate = Carbon::now()->addDays($days)->toDateString();

            $badges = Badge::with(['badgeRequest', 'client', 'coworker'])
                ->where('status', 'active')
                ->whereDate('expiry_date', $expiryDate)
                ->get();

            $this->info("[{$tenantId}] Traitement de {$badges->count()} badges expirant dans {$days} jours");
            $this->log($logPath, "[{$tenantId}] Traitement de {$badges->count()} badges expirant dans {$days} jours");

            foreach ($badges as $badge) {
                $recipientEmail = $badge->getRecipientEmail();

                if ($recipientEmail) {
                    try {
                        Mail::to($recipientEmail)->send(new BadgeExpiryNotification($badge, $days));

                        $this->info("[{$tenantId}] Notification envoyée pour le badge #{$badge->id} à {$recipientEmail}");
                        $this->log($logPath, "[{$tenantId}] Notification envoyée pour le badge #{$badge->id} à {$recipientEmail}");
                    } catch (\Exception $e) {
                        $this->error("[{$tenantId}] Erreur lors de l'envoi de la notification pour le badge #{$badge->id}: ".$e->getMessage());
                        $this->log($logPath, "[{$tenantId}] Erreur lors de l'envoi de la notification pour le badge #{$badge->id}: ".$e->getMessage());
                    }
                } else {
                    $this->warn("[{$tenantId}] Impossible d'envoyer une notification pour le badge #{$badge->id}: email manquant");
                    $this->log($logPath, "[{$tenantId}] Impossible d'envoyer une notification pour le badge #{$badge->id}: email manquant");
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
