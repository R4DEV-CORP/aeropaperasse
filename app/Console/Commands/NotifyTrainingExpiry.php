<?php

namespace App\Console\Commands;

use App\Mail\TrainingExpiryNotification;
use App\Models\UserTraining;
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
    protected $description = 'Vérifie les formations qui vont bientôt expirer et envoie une notification à l\'utilisateur';

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
        $logPath = storage_path('logs/training-expiry-check.log');
        file_put_contents($logPath, date('[Y-m-d H:i:s] ')."Démarrage de la vérification des formations\n", FILE_APPEND | LOCK_EX);
        chmod($logPath, 0664);
        // Récupérer les formations qui expirent dans 90, 30, 15 et 7 jours
        $expiryIntervals = [90, 30, 15, 7];

        foreach ($expiryIntervals as $days) {
            $expiryDate = Carbon::now()->addDays($days)->toDateString();

            $userTrainings = UserTraining::with(['user'])
                ->whereDate('expires_at', $expiryDate)
                ->get();

            $this->info("Traitement de {$userTrainings->count()} formations expirant dans {$days} jours");
            file_put_contents($logPath, date('[Y-m-d H:i:s] ')."Traitement de {$userTrainings->count()} formations expirant dans {$days} jours\n", FILE_APPEND | LOCK_EX);

            foreach ($userTrainings as $userTraining) {
                if ($userTraining->user && $userTraining->user->email) {
                    // Envoyer un email à l'utilisateur
                    try {
                        Mail::to($userTraining->user->email)
                            ->send(new TrainingExpiryNotification($userTraining, $days));

                        $this->info("Notification envoyée pour la formation #{$userTraining->id} à {$userTraining->user->email}");
                        file_put_contents($logPath, date('[Y-m-d H:i:s] ')."Notification envoyée pour la formation #{$userTraining->id} à {$userTraining->user->email}\n", FILE_APPEND | LOCK_EX);
                    } catch (\Exception $e) {
                        $this->error("Erreur lors de l'envoi de la notification pour la formation #{$userTraining->id}: ".$e->getMessage());
                        file_put_contents($logPath, date('[Y-m-d H:i:s] ')."Erreur lors de l'envoi de la notification pour la formation #{$userTraining->id}: ".$e->getMessage(), FILE_APPEND | LOCK_EX);
                    }
                } else {
                    $this->warn("Impossible d'envoyer une notification pour la formation #{$userTraining->id}: email manquant");
                    file_put_contents($logPath, date('[Y-m-d H:i:s] ')."Impossible d'envoyer une notification pour la formation #{$userTraining->id}: email manquant\n", FILE_APPEND | LOCK_EX);
                }
            }
        }
        file_put_contents($logPath, date('[Y-m-d H:i:s] ')."Vérification des formations terminée.\n", FILE_APPEND | LOCK_EX);

        return 0;
    }
}
