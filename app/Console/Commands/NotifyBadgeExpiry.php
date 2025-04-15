<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Badge;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\BadgeExpiryNotification;

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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $logPath = storage_path('logs/badge-expiry-check.log');
        file_put_contents($logPath, date('[Y-m-d H:i:s] ') . "Démarrage de la vérification des badges\n", FILE_APPEND | LOCK_EX);
        chmod($logPath, 0664);
        // Récupérer les badges qui expirent dans 30, 15 et 7 jours
        $expiryIntervals = [90, 30, 15, 7];
        
        foreach ($expiryIntervals as $days) {
            $expiryDate = Carbon::now()->addDays($days)->toDateString();
            
            $badges = Badge::with(['badgeRequest'])
                ->where('status', 'active')
                ->whereDate('expiry_date', $expiryDate)
                ->get();
            
            $this->info("Traitement de {$badges->count()} badges expirant dans {$days} jours");
            file_put_contents($logPath, date('[Y-m-d H:i:s] ') . "Traitement de {$badges->count()} badges expirant dans {$days} jours\n", FILE_APPEND | LOCK_EX);

            foreach ($badges as $badge) {
                if ($badge->badgeRequest && $badge->badgeRequest->email) {
                    // Envoyer un email au détenteur du badge
                    try {
                        Mail::to($badge->badgeRequest->email)
                            ->send(new BadgeExpiryNotification($badge, $days));
                        
                        $this->info("Notification envoyée pour le badge #{$badge->badge_number} à {$badge->badgeRequest->email}");
                        file_put_contents($logPath, date('[Y-m-d H:i:s] ') . "Notification envoyée pour le badge #{$badge->badge_number} à {$badge->badgeRequest->email}\n", FILE_APPEND | LOCK_EX);
                    } catch (\Exception $e) {
                        $this->error("Erreur lors de l'envoi de la notification pour le badge #{$badge->badge_number}: " . $e->getMessage());
                        file_put_contents($logPath, date('[Y-m-d H:i:s] ') . "Erreur lors de l'envoi de la notification pour le badge #{$badge->badge_number}: " . $e->getMessage(), FILE_APPEND | LOCK_EX);
                    }
                } else {
                    $this->warn("Impossible d'envoyer une notification pour le badge #{$badge->badge_number}: email manquant");
                    file_put_contents($logPath, date('[Y-m-d H:i:s] ') . "Impossible d'envoyer une notification pour le badge #{$badge->badge_number}: email manquant\n", FILE_APPEND | LOCK_EX);
                }
            }
        }
        $this->info("Vérification terminée à " . Carbon::now());
        file_put_contents($logPath, date('[Y-m-d H:i:s] ') . "Vérification des badges terminée.\n", FILE_APPEND | LOCK_EX);
        return 0;
    }
}